<?php

declare(strict_types=1);

namespace AkmalFairuz\VNet\network;

use pocketmine\snooze\SleeperNotifier;
use Socket;
use Threaded;
use AkmalFairuz\VNet\thread\Thread;
use AkmalFairuz\VNet\utils\Logger;

class ServerThread extends Thread{

    public function __construct(private Logger $logger, private SleeperNotifier $notifier, private Threaded $external, private Threaded $internal, private Socket $ipcSocket, private string $address, private int $port, private int $maxClients, private bool $whitelistEnable, private array $whitelist) {
        $this->start();
        $this->waitUntilRun();
    }

    public function onRun(): void{
        new ServerManager($this, $this->logger, $this->notifier, $this->external, $this->internal, $this->ipcSocket, $this->address, $this->port, $this->maxClients, $this->whitelistEnable, (array) $this->whitelist);
    }
}