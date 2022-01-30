<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 1/1/2022
 *
 * Copyright © 2021 GhostlyMC Network - All Rights Reserved.
 */
declare(strict_types=1);

namespace build;

use Generator;
use JsonException;

const LANG_HEADER = <<<'HEADER'
<?php
/*
 * Copyright © 2022 (zOmArRD) GhostlyMC Network - All Rights Reserved.
 *
 * This is a self-generated file, don't try to modify it by hand lol
 *
 *      $$$$$$\  $$\                             $$\     $$\           $$\      $$\  $$$$$$\  
 *     $$  __$$\ $$ |                            $$ |    $$ |          $$$\    $$$ |$$  __$$\ 
 *     $$ /  \__|$$$$$$$\   $$$$$$\   $$$$$$$\ $$$$$$\   $$ |$$\   $$\ $$$$\  $$$$ |$$ /  \__|
 *     $$ |$$$$\ $$  __$$\ $$  __$$\ $$  _____|\_$$  _|  $$ |$$ |  $$ |$$\$\$ $$ |$$ |      
 *     $$ |\_$$ |$$ |  $$ |$$ /  $$ |\$$$$$\    $$ |    $$ |$$ |  $$ |$$ \$$  $$ |$$ |      
 *     $$ |  $$ |$$ |  $$ |$$ |  $$ | \____$$\   $$ |$$\ $$ |$$ |  $$ |$$ |\$  /$$ |$$ |  $$\ 
 *     \$$$$$  |$$ |  $$ |\$$$$$  |$$$$$$$  |  \$$$  |$$ |\$$$$$$ |$$ | \_/ $$ |\$$$$$  |
 *      \______/ \__|  \__| \______/ \_______/    \____/ \__| \____$$ |\__|     \__| \______/ 
 *                                                           $$\   $$ |                       
 *                                                           \$$$$$  |                       
 *                                                            \______/                                                             
 */
declare(strict_types=1);

namespace zomarrd\ghostly\player\language;


HEADER;

function stringifyKeys(array $array): Generator
{
	foreach ($array as $key => $value) {
		yield (string)$key => $value;
	}
}

function constantify(string $permissionName) : string{
	return strtoupper(str_replace([".", "-"], "_", $permissionName));
}

function generate_lang_keys(array $array): void
{
	ob_start();
	echo LANG_HEADER;
	echo <<<'HEADER'
/**
 * This class is generated automatically, do NOT modify it by hand.
 */
final class LangKey
{

HEADER;

	ksort($array, SORT_STRING);
	foreach (stringifyKeys($array) as $key => $_) {
		echo "\tpublic const ";
		echo constantify($key);
		echo " = \"" . $key . "\";\n";
	}

	echo "}";
	file_put_contents(dirname(__DIR__) . '/src/zomarrd/ghostly/player/language/LangKey.php', ob_get_clean());
	echo "Done generating LangKey.\n";
}

$langFile = file_get_contents(dirname(__DIR__) . '/resources/lang/en_US.json');
try {
	$json = json_decode($langFile, false, 512, JSON_THROW_ON_ERROR);
} catch (JsonException $e) {
	echo $e->getMessage();
	exit(1);
}
generate_lang_keys((array)$json->messages);