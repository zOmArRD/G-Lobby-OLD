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

namespace zomarrd\ghostly\player\permission;


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

function generate_permission_keys(array $array): void
{
	ob_start();
	echo LANG_HEADER;
	echo <<<'HEADER'
/**
 * This class is generated automatically, do NOT modify it by hand.
 */
final class PermissionKey
{

HEADER;

	ksort($array, SORT_STRING);
	foreach (stringifyKeys($array) as $key => $_) {
		echo "\tpublic const ";
		echo constantify($key);
		echo " = \"" . $key . "\";\n";
	}

	echo "}";
	file_put_contents(dirname(__DIR__) . '/src/zomarrd/ghostly/player/permission/PermissionKey.php', ob_get_clean());
	echo "Done generating PermissionKey.\n";
}
$files = scandir(dirname(__DIR__));
foreach ($files as $file) {
	if (str_contains($file, 'plugin.yml')) {
		$yml = yaml_parse_file($file);
	}
}
if ($yml === false ){
	fwrite(STDERR, "Missing Permission files!\n");
	exit(1);
}
generate_permission_keys($yml["permissions"]);