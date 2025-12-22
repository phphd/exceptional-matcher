<?php

use Composer\Autoload\ClassLoader;

/** @var ClassLoader $loader */
$loader = require dirname(__DIR__, 2).'/vendor/autoload.php';

/**
 * The Loader was registered with $prepend=true, but we'd not like it to be prepended,
 * since current composer.json's dependencies should take precedence to main composer.json's dependencies.
 */
$loader->unregister();
$loader->register(false);
