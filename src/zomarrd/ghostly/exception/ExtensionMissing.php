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

namespace zomarrd\ghostly\exception;

use pocketmine\utils\TextFormat;
use RuntimeException;

final class ExtensionMissing extends RuntimeException
{
    public function __construct(string $extension)
    {
        $instructions = "Please install PHP according to the instructions from https://pmmp.readthedocs.io/en/rtfd/installation.html which provides the $extension extension.";

        $ini = php_ini_loaded_file();
        if ($ini && is_file($ini)) {
            foreach (file($ini) as $i => $line) {
                if (str_contains($line, ";extension=") && stripos($line, $extension) !== false) {
                    $instructions = TextFormat::GOLD . "Please remove the leading semicolon on line " . ($i + 1) . " of $ini and restart the server " . TextFormat::RED . "so that the $extension extension can be loaded.";
                }
            }
        }

        parent::__construct("The $extension extension is missing. $instructions");
    }
}