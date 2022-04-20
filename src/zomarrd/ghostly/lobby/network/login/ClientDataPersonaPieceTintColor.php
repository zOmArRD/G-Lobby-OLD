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

namespace zomarrd\ghostly\lobby\network\login;

/**
 * Model class for LoginPacket JSON data for JsonMapper
 */
final class ClientDataPersonaPieceTintColor
{
    /** @required */
    public string $PieceType;

    /** @required */
    public array $Colors;
}
