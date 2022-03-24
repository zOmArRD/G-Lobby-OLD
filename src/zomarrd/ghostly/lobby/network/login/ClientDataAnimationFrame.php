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
final class ClientDataAnimationFrame
{

    /** @required */
    public int $ImageHeight;

    /** @required */
    public int $ImageWidth;

    /** @required */
    public float $Frames;

    /** @required */
    public int $Type;

    /** @required */
    public string $Image;

    /** @required */
    public int $AnimationExpression;
}
