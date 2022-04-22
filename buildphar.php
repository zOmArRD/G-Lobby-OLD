<?php
/*
 * Created by PhpStorm.
 *
 * User: zOmArRD
 * Date: 20/4/2022
 *
 * Copyright © 2022 GhostlyMC Network (omar@ghostlymc.live) - All Rights Reserved.
 */
error_reporting(E_ALL | E_STRICT); // Show all errors

if (ini_get('phar.readonly')) {
    echo "Phar.readonly is set to 1. Please set it to 0 in your php.ini file.\n";
    exit(1);
}

$description = file_get_contents(__DIR__ . '/plugin.yml');
$buildDir = __DIR__ . '/build';


if ($description === false) {
    echo "Could not read plugin.yml\n";
    exit(1);
}

if (!is_dir($buildDir) && !mkdir($buildDir) && !is_dir($buildDir)) {
    echo "Could not create build directory\n";
    exit(1);
}

$pharFile = sprintf('%s/GhostlyMC.phar', $buildDir);

if (file_exists($pharFile)) {
    unlink($pharFile);
}

$phar = new Phar($pharFile);
$phar->setSignatureAlgorithm(Phar::SHA1);
$phar->startBuffering();

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__));

$phar->buildFromIterator(new class($iterator) extends FilterIterator {
    final public function accept(): bool
    {
        $current = $this->getInnerIterator()->current();

        if (is_dir($current)) {
            return false;
        }

        $current = substr($current, strlen(__DIR__) + 1);

        if (DIRECTORY_SEPARATOR !== '/') { // Windows uses '\\' instead of '/'
            $current = str_replace(DIRECTORY_SEPARATOR, '/', $current);
        }

        return $current === 'plugin.yml'
            || str_starts_with($current, 'src/')
            || str_starts_with($current, 'vendor/')
            || str_starts_with($current, 'resources/');
    }
}, __DIR__);

$phar->stopBuffering();
$phar->compressFiles(Phar::GZ);

exit('PHAR file created successfully!' . PHP_EOL);