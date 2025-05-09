<?php /** @noinspection ALL */
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 18/4/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */

declare(strict_types=1);

namespace zomarrd\ghostly\lobby\network\login;

use Closure;
use JsonMapper;
use JsonMapper_Exception;
use pocketmine\lang\KnownTranslationKeys;
use pocketmine\network\mcpe\auth\VerifyLoginException;
use pocketmine\network\mcpe\JwtException;
use pocketmine\network\mcpe\JwtUtils;
use pocketmine\network\mcpe\protocol\types\login\JwtChainLinkBody;
use pocketmine\network\mcpe\protocol\types\login\JwtHeader;
use pocketmine\scheduler\AsyncTask;
use function base64_decode;
use function igbinary_serialize;
use function igbinary_unserialize;
use function openssl_error_string;
use function time;

/**
 * ProcessLoginTask.php from PocketMine-MP
 */
final class ProcessLoginTask extends AsyncTask
{
    private const TLS_KEY_ON_COMPLETION = "completion";

    public const MOJANG_ROOT_PUBLIC_KEY = "MHYwEAYHKoZIzj0CAQYFK4EEACIDYgAE8ELkixyLcwlZryUQcu1TvPOmI2B7vX83ndnWRUaXm74wFfa5f/lwQNTfrLVHa2PmenpGI6JhIMUJaWZrjmMj90NoKNFSNBuKdm8rYiXsfaz3K36x/1U26HpG0ZxK/V1V";

    private const CLOCK_DRIFT_MAX = 60;

    private ?string $chain;
    private string $clientDataJwt;

    /**
     * @var string|null
     * Whether the keychain signatures were validated correctly. This will be set to an error message if any link in the
     * keychain is invalid for whatever reason (bad signature, not in nbf-exp window, etc.). If this is non-null, the
     * keychain might have been tampered with. The player will always be disconnected if this is non-null.
     */
    private ?string $error = "Unknown";
    /**
     * @var bool
     * Whether the player is logged into Xbox Live. This is true if any link in the keychain is signed with the Mojang
     * root public key.
     */
    private bool $authenticated = false;
    /** @var bool */
    private bool $authRequired;

    /** @var string|null */
    private ?string $clientPublicKey = null;

    public function __construct(array $chainJwts, string $clientDataJwt, bool $authRequired, Closure $onCompletion)
    {
        $this->storeLocal(self::TLS_KEY_ON_COMPLETION, $onCompletion);
        $this->chain = igbinary_serialize($chainJwts);
        $this->clientDataJwt = $clientDataJwt;
        $this->authRequired = $authRequired;
    }

    public function onRun(): void
    {
        try {
            $this->clientPublicKey = $this->validateChain();
            $this->error = null;
        } catch (VerifyLoginException $e) {
            $this->error = $e->getMessage();
        }
    }

    private function validateChain(): string
    {
        /** @var string[] $chain */
        $chain = igbinary_unserialize($this->chain);

        $currentKey = null;
        $first = true;

        foreach ($chain as $jwt) {
            $this->validateToken($jwt, $currentKey, $first);
            if ($first) {
                $first = false;
            }
        }

        /** @var string $clientKey */
        $clientKey = $currentKey;

        $this->validateToken($this->clientDataJwt, $currentKey);

        return $clientKey;
    }

    /**
     * @throws VerifyLoginException if errors are encountered
     */
    private function validateToken(string $jwt, ?string &$currentPublicKey, bool $first = false): void
    {
        try {
            [$headersArray, $claimsArray,] = JwtUtils::parse($jwt);
        } catch (JwtException $e) {
            throw new VerifyLoginException("Failed to parse JWT: " . $e->getMessage(), 0, $e);
        }

        $mapper = new JsonMapper();
        $mapper->bExceptionOnMissingData = true;
        $mapper->bExceptionOnUndefinedProperty = true;
        $mapper->bEnforceMapType = false;

        try {
            $headers = $mapper->map($headersArray, new JwtHeader());
            assert($headers instanceof JwtHeader);
        } catch (JsonMapper_Exception $e) {
            throw new VerifyLoginException("Invalid JWT header: " . $e->getMessage(), 0, $e);
        }

        $headerDerKey = base64_decode($headers->x5u, true);
        if ($headerDerKey === false) {
            throw new VerifyLoginException("Invalid JWT public key: base64 decoding error decoding x5u");
        }

        if ($currentPublicKey === null) {
            if (!$first) {
                throw new VerifyLoginException(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_MISSINGKEY);
            }
        } else if ($headerDerKey !== $currentPublicKey) {
            //Fast path: if the header key doesn't match what we expected, the signature isn't going to validate anyway
            throw new VerifyLoginException(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_BADSIGNATURE);
        }

        try {
            $signingKeyOpenSSL = JwtUtils::parseDerPublicKey($headerDerKey);
        } catch (JwtException $e) {
            throw new VerifyLoginException("Invalid JWT public key: " . openssl_error_string());
        }
        try {
            if (!JwtUtils::verify($jwt, $signingKeyOpenSSL)) {
                throw new VerifyLoginException(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_BADSIGNATURE);
            }
        } catch (JwtException $e) {
            throw new VerifyLoginException($e->getMessage(), 0, $e);
        }

        if ($headers->x5u === self::MOJANG_ROOT_PUBLIC_KEY) {
            $this->authenticated = true; //we're signed into xbox live
        }

        $mapper = new JsonMapper();
        $mapper->bExceptionOnUndefinedProperty = false; //we only care about the properties we're using in this case
        $mapper->bExceptionOnMissingData = true;
        $mapper->bEnforceMapType = false;
        $mapper->bRemoveUndefinedAttributes = true;
        try {
            $claims = $mapper->map($claimsArray, new JwtChainLinkBody());
            assert($claims instanceof JwtChainLinkBody);
        } catch (JsonMapper_Exception $e) {
            throw new VerifyLoginException("Invalid chain link body: " . $e->getMessage(), 0, $e);
        }

        $time = time();
        if (isset($claims->nbf) && $claims->nbf > $time + self::CLOCK_DRIFT_MAX) {
            throw new VerifyLoginException(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_TOOEARLY);
        }

        if (isset($claims->exp) && $claims->exp < $time - self::CLOCK_DRIFT_MAX) {
            throw new VerifyLoginException(KnownTranslationKeys::POCKETMINE_DISCONNECT_INVALIDSESSION_TOOLATE);
        }

        if (isset($claims->identityPublicKey)) {
            $identityPublicKey = base64_decode($claims->identityPublicKey, true);
            if ($identityPublicKey === false) {
                throw new VerifyLoginException("Invalid identityPublicKey: base64 error decoding");
            }
            $currentPublicKey = $identityPublicKey; //if there are further links, the next link should be signed with this
        }
    }

    public function onCompletion(): void
    {
        $callback = $this->fetchLocal(self::TLS_KEY_ON_COMPLETION);
        $callback($this->authenticated, $this->authRequired, $this->error, $this->clientPublicKey);
    }
}