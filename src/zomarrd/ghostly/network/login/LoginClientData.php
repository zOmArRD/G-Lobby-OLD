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

class LoginClientData
{

    /**
     * @var ClientDataAnimationFrame[]
     * @required
     */
    public array $AnimatedImageData;

    /** @required */
    public string $ArmSize;

    /** @required */
    public string $CapeData;

    /** @required */
    public string $CapeId;

    /** @required */
    public int $CapeImageHeight;

    /** @required */
    public int $CapeImageWidth;

    /** @required */
    public bool $CapeOnClassicSkin;

    /** @required */
    public int $ClientRandomId;

    /** @required */
    public int $CurrentInputMode;

    /** @required */
    public int $DefaultInputMode;

    /** @required */
    public string $DeviceId;

    /** @required */
    public string $DeviceModel;

    /** @required */
    public int $DeviceOS;

    /** @required */
    public string $GameVersion;

    /** @required */
    public int $GuiScale;

    /** @required */
    public string $LanguageCode;

    /**
     * @var ClientDataPersonaSkinPiece[]
     * @required
     */
    public array $PersonaPieces;

    /** @required */
    public bool $PersonaSkin;

    /**
     * @var ClientDataPersonaPieceTintColor[]
     * @required
     */
    public array $PieceTintColors;

    /** @required */
    public string $PlatformOfflineId;

    /** @required */
    public string $PlatformOnlineId;

    public string $PlatformUserId = ""; //xbox-only, apparently

    /** @required */
    public string $PlayFabId;

    /** @required */
    public bool $PremiumSkin = false;

    /** @required */
    public string $SelfSignedId;

    /** @required */
    public string $ServerAddress;

    /** @required */
    public string $SkinAnimationData;

    /** @required */
    public string $SkinColor;

    /** @required */
    public string $SkinData;

    /** @required */
    public string $SkinGeometryData;

    /** @required */
    public string $SkinGeometryDataEngineVersion;

    /** @required */
    public string $SkinId;

    /** @required */
    public int $SkinImageHeight;

    /** @required */
    public int $SkinImageWidth;

    /** @required */
    public string $SkinResourcePatch;

    /** @required */
    public string $ThirdPartyName;

    /** @required */
    public bool $ThirdPartyNameOnly;

    /** @required */
    public int $UIProfile;

    /** @required */
    public string $Waterdog_XUID;

    /** @required */
    public string $Waterdog_IP;
}