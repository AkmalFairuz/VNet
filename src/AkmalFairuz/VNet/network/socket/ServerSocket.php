<?php

declare(strict_types=1);

namespace AkmalFairuz\VNet\network\socket;

use AkmalFairuz\VNet\network\ServerManager;
use AkmalFairuz\VNet\Server;
use AkmalFairuz\VNet\utils\Binary;
use AkmalFairuz\VNet\utils\Logger;

class ServerSocket extends Socket{

    /** @var ClientSocket[] */
    private array $clients = [];

    private int $nextClientId = 1;

    public function __construct(private ServerManager $manager, public Logger $logger, string $address, int $port, private int $maxClients, private bool $whitelistEnable, private array $whitelist) {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $this->setNonBlocking();
        $this->setOption(SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($this->socket, $address, $port);
        socket_listen($this->socket, 3);
        $this->logger->info("Server running $address/$port");
    }

    public function getClients() : array{
        return $this->clients;
    }

    public function getClient(int $id) : ?ClientSocket{
        return $this->clients[$id] ?? null;
    }

    public function accept() {
        $client = socket_accept($this->socket);
        if($client !== false){
            socket_set_nonblock($client);
            socket_getpeername($client, $ip, $port);
            if($this->whitelistEnable) {
                if(!in_array($ip, $this->whitelist)) {
                    socket_close($client);
                    return;
                }
            }
            if(count($this->clients) >= $this->maxClients) {
                socket_close($client);
                return;
            }
            $id = $this->nextClientId++;
            $this->clients[$id] = new ClientSocket($this, $id, $client, $ip, $port);
            $this->manager->pushThreadToMainPacket(chr(Server::PACKET_OPEN_SESSION) . Binary::writeInt($id) . Binary::writeInt(strlen($ip)) . $ip . Binary::writeInt($port));
        }
    }

    public function closeClient(int $id) {
        $this->manager->pushThreadToMainPacket(chr(Server::PACKET_CLOSE_SESSION) . Binary::writeInt($id));
        unset($this->clients[$id]);
    }
}