<?php

namespace AkmalFairuz\VNet;

class Autoload {

    public function register() {
        require_once VNET_PATH . "vendor/autoload.php";
        spl_autoload_register(function(string $class) {
            require_once VNET_PATH . "src" . DIRECTORY_SEPARATOR . $class . ".php";
        });
    }
}