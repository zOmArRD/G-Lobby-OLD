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

namespace zomarrd\ghostly\network\skin;

use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\types\skin\PersonaPieceTintColor;
use pocketmine\network\mcpe\protocol\types\skin\PersonaSkinPiece;
use pocketmine\network\mcpe\protocol\types\skin\SkinAnimation;
use pocketmine\network\mcpe\protocol\types\skin\SkinData;
use pocketmine\network\mcpe\protocol\types\skin\SkinImage;
use zomarrd\ghostly\network\login\ClientDataPersonaPieceTintColor;
use zomarrd\ghostly\network\login\ClientDataPersonaSkinPiece;
use zomarrd\ghostly\network\login\LoginClientData;

class ClientDataToSkinDataHelper
{

	/**
	 * @throws \InvalidArgumentException
	 */
	public static function fromClientData(LoginClientData $clientData): SkinData
	{
		/** @var SkinAnimation[] $animations */
		$animations = [];
		foreach ($clientData->AnimatedImageData as $k => $animation) {
			$animations[] = new SkinAnimation(
				new SkinImage(
					$animation->ImageHeight,
					$animation->ImageWidth,
					self::safeB64Decode($animation->Image, "AnimatedImageData.$k.Image")
				),
				$animation->Type,
				$animation->Frames,
				$animation->AnimationExpression
			);
		}
		return new SkinData(
			$clientData->SkinId,
			$clientData->PlayFabId,
			self::safeB64Decode($clientData->SkinResourcePatch, "SkinResourcePatch"),
			new SkinImage($clientData->SkinImageHeight, $clientData->SkinImageWidth, self::safeB64Decode($clientData->SkinData, "SkinData")),
			$animations,
			new SkinImage($clientData->CapeImageHeight, $clientData->CapeImageWidth, self::safeB64Decode($clientData->CapeData, "CapeData")),
			self::safeB64Decode($clientData->SkinGeometryData, "SkinGeometryData"),
			self::safeB64Decode($clientData->SkinGeometryDataEngineVersion, "SkinGeometryDataEngineVersion"), //yes, they actually base64'd the version!
			self::safeB64Decode($clientData->SkinAnimationData, "SkinAnimationData"),
			$clientData->CapeId,
			null,
			$clientData->ArmSize,
			$clientData->SkinColor,
			array_map(static function (ClientDataPersonaSkinPiece $piece): PersonaSkinPiece {
				return new PersonaSkinPiece($piece->PieceId, $piece->PieceType, $piece->PackId, $piece->IsDefault, $piece->ProductId);
			}, $clientData->PersonaPieces),
			array_map(static function (ClientDataPersonaPieceTintColor $tint): PersonaPieceTintColor {
				return new PersonaPieceTintColor($tint->PieceType, $tint->Colors);
			}, $clientData->PieceTintColors),
			true,
			$clientData->PremiumSkin,
			$clientData->PersonaSkin,
			$clientData->CapeOnClassicSkin,
			true, //assume this is true? there's no field for it ...
		);
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	private static function safeB64Decode(string $base64, string $context): string
	{
		$result = base64_decode($base64, true);
		if ($result === false) {
			throw new InvalidArgumentException("$context: Malformed base64, cannot be decoded");
		}
		return $result;
	}
}