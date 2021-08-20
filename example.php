<?php
require_once "src/AkmalFairuz/VNet/VNet.php";

use AkmalFairuz\VNet\Server;
use AkmalFairuz\VNet\network\Session;

$address = "0.0.0.0";
$port = 8080;

$server = new Server($address, $port);

$server->getEventHandler()
    ->onRecv(function (Session $session, string $packet) : void {
        $session->sendPacket(implode("\r\n", [
            "HTTP/1.1 200 OK",
            "Date: " . gmdate('D, d M Y H:i:s T'),
            "Connection: Keep-Alive",
            "Server: PHP",
            "Content-Type: text/html",
            "",
            "<html><head><title>Hello</title></head><body><h1>Hello World!</h1><br><br><b>You send:</b><br><br>" . str_replace("\n", "<br></body></html>", $packet)
        ]));
        $session->disconnect(STREAM_SHUT_RDWR);
    })
    ->onConnect(function (Session $session) : void {
        // TODO:
    })
    ->onClose(function (Session $session) : void {
        // TODO:
    });

$server->run();