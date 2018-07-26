<?php

/**
 * @file
 * A simple launcher file.
 */

require_once './vendor/autoload.php';

// Load config.
if (file_exists('./config.inc.php')) {
  require_once './config.inc.php';
}
else {
  require_once './config.inc.php.dist';
}

$warmer = new \drunomics\SitemapCacheWarmer\Cachewarmer($config);
$warmer->run();
