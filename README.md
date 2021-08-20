# VNet
A TCP Networking library written in PHP with pthreads

## Requirements
- PHP8
- [pmmp/pthreads](https://github.com/pmmp/pthreads)
- [pmmp/Snooze](https://github.com/pmmp/Snooze)
- ext-socket

## Example
```php
<?php
require_once "src/AkmalFairuz/VNet/VNet.php";

use AkmalFairuz\VNet\Server;
use AkmalFairuz\VNet\network\Session;

$address = "0.0.0.0";
$port = 8080;

$server = new Server($address, $port);

$server->getEventHandler()
    ->onRecv(function (Session $session, string $packet) : void {
        var_dump($packet);
    })
    ->onConnect(function (Session $session) : void {
        echo "New Connection {$session->getAddress()}/{$session->getPort()}\n";
    })
    ->onClose(function (Session $session) : void {
        echo "Closed Connection {$session->getAddress()}/{$session->getPort()}\n";
    });

$server->run();
```