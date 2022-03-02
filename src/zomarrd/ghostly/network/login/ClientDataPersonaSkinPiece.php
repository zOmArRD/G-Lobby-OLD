<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 30/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */

declare(strict_types=1);

namespace zomarrd\ghostly\network\login;

/**
 * Model class for LoginPacket JSON data for JsonMapper
 */
final class ClientDataPersonaSkinPiece
{
    /** @required */
    public string $PieceId;

    /** @required */
    public string $PieceType;

    /** @required */
    public string $PackId;

    /** @required */
    public bool $IsDefault;

    /** @required */
    public string $ProductId;
}
