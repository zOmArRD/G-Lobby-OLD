<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 25/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\network\login;

use Closure;
use InvalidArgumentException;
use JsonMapper;
use JsonMapper_Exception;
use pocketmine\entity\InvalidSkinException;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\network\mcpe\auth\ProcessLoginTask;
use pocketmine\network\mcpe\handler\PacketHandler;
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\LoginPacket;
use pocketmine\network\mcpe\protocol\PlayStatusPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\login\AuthenticationData;
use pocketmine\network\mcpe\protocol\types\login\JwtChain;
use pocketmine\network\PacketHandlingException;
use pocketmine\player\Player;
use pocketmine\player\XboxLivePlayerInfo;
use pocketmine\Server;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use zomarrd\ghostly\network\skin\ClientDataToSkinDataHelper;
use zomarrd\ghostly\network\skin\SkinAdapterSingleton;

final class LoginPacketHandler extends PacketHandler
{
	private Server $server;
	private NetworkSession $session;
	private Closure $playerInfoConsumer;
	private Closure $authCallback;

	public function __construct(Server $server, NetworkSession $session, Closure $playerInfoConsumer, Closure $authCallback)
	{
		$this->session = $session;
		$this->server = $server;
		$this->playerInfoConsumer = $playerInfoConsumer;
		$this->authCallback = $authCallback;
	}

	public function handleLogin(LoginPacket $packet): bool
	{
		if (!$this->isCompatibleProtocol($packet->protocol)) {
			$this->session->sendDataPacket(PlayStatusPacket::create($packet->protocol < ProtocolInfo::CURRENT_PROTOCOL ? PlayStatusPacket::LOGIN_FAILED_CLIENT : PlayStatusPacket::LOGIN_FAILED_SERVER), true);
			$this->session->disconnect(PREFIX . "Apparently we don't support this version of Minecraft\n§aContact support in: discord.ghostlymc.live!", false);
			return true;
		}

		$extraData = $this->fetchAuthData($packet->chainDataJwt);

		if (!Player::isValidUserName($extraData->displayName)) {
			$this->session->disconnect(PREFIX . "§cYou can not enter with this name, try to change it!");
			return true;
		}

		$clientData = $this->parseClientData($packet->clientDataJwt);

		try {
			$skin = SkinAdapterSingleton::get()->fromSkinData(ClientDataToSkinDataHelper::fromClientData($clientData));
		} catch (InvalidArgumentException|InvalidSkinException $e) {
			$this->session->getLogger()->debug("Invalid skin: " . $e->getMessage());
			$this->session->disconnect(PREFIX . "§cTry to change your skin to be able to enter the network!");
			return true;
		}

		if (!Uuid::isValid($extraData->identity)) {
			throw new PacketHandlingException("Invalid login UUID");
		}

		$uuid = Uuid::fromString($extraData->identity);

		$class = new ReflectionClass($this->session);
		$property = $class->getProperty("ip");
		$property->setAccessible(true);

		if (isset($clientData->Waterdog_IP, $clientData->Waterdog_XUID)) {
			$property->setValue($this->session, $clientData->Waterdog_IP);
			$xuid = $clientData->Waterdog_XUID;
		} else {
			$this->session->disconnect(PREFIX . "Login without proxy is not allowed!");
			return true;
		}

		$playerInfo = new XboxLivePlayerInfo(
			$extraData->XUID,
			$extraData->displayName,
			$uuid,
			$skin,
			$clientData->LanguageCode,
			(array)$clientData
		);

		($this->playerInfoConsumer)($playerInfo);

		$ev = new PlayerPreLoginEvent(
			$playerInfo,
			$this->session->getIp(),
			$this->session->getPort(),
			$this->server->requiresAuthentication()
		);

		/** @todo do this in the onJoin? */
		if ($this->server->getNetwork()->getConnectionCount() > $this->server->getMaxPlayers()) {
			$ev->setKickReason(PlayerPreLoginEvent::KICK_REASON_SERVER_FULL, PREFIX . "This server has reached its maximum capacity, please try again later!");
		}

		if (!$this->server->isWhitelisted($playerInfo->getUsername())) {
			$ev->setKickReason(PlayerPreLoginEvent::KICK_REASON_SERVER_WHITELISTED, PREFIX . "We are in maintenance!");
		}

		$ev->call();

		if (!$ev->isAllowed()) {
			$this->session->disconnect($ev->getFinalKickMessage());
			return true;
		}

		$this->processLogin($packet, $ev->isAuthRequired());

		return true;
	}

	protected function isCompatibleProtocol(int $protocolVersion): bool
	{
		return $protocolVersion === ProtocolInfo::CURRENT_PROTOCOL;
	}

	/**
	 * @throws PacketHandlingException
	 */
	protected function fetchAuthData(JwtChain $chain): AuthenticationData
	{
		/** @var AuthenticationData|null $extraData */
		$extraData = null;
		foreach ($chain->chain as $k => $jwt) {
			//validate every chain element
			try {
				[, $claims,] = JwtUtils::parse($jwt);
			} catch (JwtException $e) {
				throw PacketHandlingException::wrap($e);
			}
			if (isset($claims["extraData"])) {
				if ($extraData !== null) {
					throw new PacketHandlingException("Found 'extraData' more than once in chainData");
				}

				if (!is_array($claims["extraData"])) {
					throw new PacketHandlingException("'extraData' key should be an array");
				}
				$mapper = new JsonMapper;
				$mapper->bEnforceMapType = false; //TODO: we don't really need this as an array, but right now we don't have enough models
				$mapper->bExceptionOnMissingData = true;
				$mapper->bExceptionOnUndefinedProperty = true;
				try {
					/** @var AuthenticationData $extraData */
					$extraData = $mapper->map($claims["extraData"], new AuthenticationData);
				} catch (JsonMapper_Exception $e) {
					throw PacketHandlingException::wrap($e);
				}
			}
		}
		if ($extraData === null) {
			throw new PacketHandlingException("'extraData' not found in chain data");
		}
		return $extraData;
	}

	/**
	 * @throws PacketHandlingException
	 */
	protected function parseClientData(string $clientDataJwt): LoginClientData
	{
		try {
			[, $clientDataClaims,] = JwtUtils::parse($clientDataJwt);
		} catch (JwtException $e) {
			throw PacketHandlingException::wrap($e);
		}

		$mapper = new JsonMapper;
		$mapper->bEnforceMapType = false; //TODO: we don't really need this as an array, but right now we don't have enough models
		$mapper->bExceptionOnMissingData = true;
		$mapper->bExceptionOnUndefinedProperty = true;
		try {
			$clientData = $mapper->map($clientDataClaims, new LoginClientData());
		} catch (JsonMapper_Exception $e) {
			throw PacketHandlingException::wrap($e);
		}
		return $clientData;
	}

	/**
	 * TODO: This is separated for the purposes of allowing plugins (like Specter) to hack it and bypass authentication.
	 * In the future this won't be necessary.
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function processLogin(LoginPacket $packet, bool $authRequired): void
	{
		$this->server->getAsyncPool()->submitTask(new ProcessLoginTask($packet->chainDataJwt->chain, $packet->clientDataJwt, $authRequired, $this->authCallback));
		$this->session->setHandler(null); //drop packets received during login verification
	}
}