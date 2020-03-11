<?php

/**
 * @file Предзагрузка
 * Может вызывать излишнее потребление памяти, т.к. грузит все что есть в composer classmap
 */

$files = require './vendor/composer/autoload_classmap.php';
$files = array_unique($files);

/** @noinspection ClassConstantCanBeUsedInspection */
unset(
    $files['Psr\\Log\\Test\\DummyTest'],
    $files['Psr\\Log\\Test\\LoggerInterfaceTest'],
    $files['Psr\\Log\\Test\\TestLogger'],
);

foreach ($files as $file) {
    opcache_compile_file($file);
}
