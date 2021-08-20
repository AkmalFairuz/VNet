<?php

use AkmalFairuz\VNet\Autoload;
use AkmalFairuz\VNet\Server;
use AkmalFairuz\VNet\thread\ThreadManager;

$autoloadPath = __DIR__ . DIRECTORY_SEPARATOR . "Autoload.php";

define("VNET_AUTOLOAD_PATH", $autoloadPath);
define("VNET_PATH", getcwd() . DIRECTORY_SEPARATOR);

require_once $autoloadPath;
$autoload = new Autoload();
$autoload->register();

ThreadManager::init();
