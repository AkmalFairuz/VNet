<?php

declare(strict_types=1);

namespace AkmalFairuz\VNet\network;

use AkmalFairuz\VNet\Server;
use AkmalFairuz\VNet\utils\Binary;

class Session{

    public function __construct(private Server $server, private int $id, private string $address, private int $port) {
        $this->server->getLogger()->info(sprintf("New session from %s/%d", $address, $port));
        $this->server->getEventHandler()->callConnect($this);
    }

    public function getId(): int{
        return $this->id;
    }

    public function getAddress(): string{
        return $this->address;
    }

    public function getPort(): int{
        return $this->port;
    }

    /**
     * This function should called from Thread, if you want to close use $session->disconnect();
     */
    public function close() {
        $this->server->getEventHandler()->callClose($this);
        unset($this->server->sessions[$this->id]);
    }

    public function disconnect() {
        $this->server->pushMainToThreadPacket(chr(Server::PACKET_CLOSE_SESSION) . Binary::writeInt($this->id));
    }

    public function handlePacket(string $packet) {
        $this->server->getEventHandler()->callRecv($this, $packet);
    }

    public function sendPacket(string $packet) {
        $this->server->pushMainToThreadPacket(chr(Server::PACKET_SEND_PACKET) . Binary::writeInt($this->id) . $packet);
    }
}