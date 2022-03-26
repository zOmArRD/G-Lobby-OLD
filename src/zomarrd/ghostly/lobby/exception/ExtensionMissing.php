<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 28/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace zomarrd\ghostly\lobby\exception;

use pocketmine\utils\TextFormat;
use RuntimeException;

final class ExtensionMissing extends RuntimeException
{
    public function __construct(string $extension)
    {
        $instructions = sprintf("Please install PHP according to the instructions from https://pmmp.readthedocs.io/en/rtfd/installation.html which provides the %s extension.", $extension);

        $ini = php_ini_loaded_file();
        if ($ini && is_file($ini)) {
            foreach (file($ini) as $i => $line) {
                if (str_contains($line, ";extension=") && stripos($line, $extension) !== false) {
                    $instructions = sprintf("%sPlease remove the leading semicolon on line %d of %s and restart the server %sso that the %s extension can be loaded.", TextFormat::GOLD, $i + 1, $ini, TextFormat::RED, $extension);
                }
            }
        }

        parent::__construct(sprintf("The %s extension is missing. %s", $extension, $instructions));
    }
}