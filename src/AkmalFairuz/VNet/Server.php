<?php

declare(strict_types=1);

namespace AkmalFairuz\VNet;

use AkmalFairuz\VNet\event\EventHandler;
use AkmalFairuz\VNet\network\Session;
use AkmalFairuz\VNet\utils\Binary;
use pocketmine\snooze\SleeperHandler;
use pocketmine\snooze\SleeperNotifier;
use Socket;
use Threaded;
use AkmalFairuz\VNet\network\ServerThread;
use AkmalFairuz\VNet\thread\Thread;
use AkmalFairuz\VNet\utils\Logger;

class Server{

    const PACKET_OPEN_SESSION = 0;
    const PACKET_CLOSE_SESSION = 1;
    const PACKET_SEND_PACKET = 2;
    const PACKET_RECV_PACKET = 2;

    private Threaded $external;

    private Threaded $internal;

    private Thread $thread;

    public Socket $ipcMainSocket;

    private Socket $ipcThreadSocket;

    private Logger $logger;

    private SleeperHandler $sleeperHandler;

    private SleeperNotifier $sleeperNotifier;

    /** @var Session[] */
    public array $sessions;

    private EventHandler $eventHandler;

    public function __construct(string $address, int $port, int $maxClients = 100, bool $enableIpWhitelist = false, array $ipsWhitelist = []) {
        $this->external = new Threaded;
        $this->internal = new Threaded;
        $this->logger = new Logger();

        $this->logger->info("Loading...");

        socket_create_pair(AF_INET, SOCK_STREAM, 0, $ipc);
        [$this->ipcMainSocket, $this->ipcThreadSocket] = $ipc;
        socket_set_nonblock($this->ipcMainSocket);
        socket_set_nonblock($this->ipcThreadSocket);

        $this->sleeperHandler = new SleeperHandler();
        $this->sleeperNotifier = new SleeperNotifier();

        $this->sleeperHandler->addNotifier($this->sleeperNotifier, function() : void {
            $this->processPacket();
        });
        $this->eventHandler = new EventHandler($this);
        $this->thread = new ServerThread($this->logger, $this->sleeperNotifier, $this->external, $this->internal, $this->ipcThreadSocket, $address, $port, $maxClients, $enableIpWhitelist, $ipsWhitelist);
    }

    public function getEventHandler(): EventHandler{
        return $this->eventHandler;
    }

    public function run() {
        $this->process();
    }

    public function readThreadToMainPacket() :?string {
        return $this->internal->shift();
    }

    public function pushMainToThreadPacket(string $packet) {
        $this->external[] = $packet;
        socket_write($this->ipcMainSocket, "\xff");
    }

    private function process() {
        while(true) {
            $start = microtime(true);
            $this->tick();
            $this->sleeperHandler->sleepUntil($start + 0.001);
        }
    }

    private function tick() {
    }

    public function getLogger(): Logger{
        return $this->logger;
    }

    private function processPacket() {
        @socket_read($this->ipcMainSocket, 255);
        while(($buf = $this->readThreadToMainPacket()) !== null) {
            $pid = ord($buf[0]);
            $buffer = substr($buf, 1);

            switch($pid) {
                case self::PACKET_OPEN_SESSION:
                    $id = Binary::readInt(substr($buffer, 0, 4));
                    $len = Binary::readInt(substr($buffer, 4, 4));
                    $address = substr($buffer, 8, $len);
                    $port = Binary::readInt(substr($buffer, 8 + $len, 4));
                    $this->sessions[$id] = new Session($this, $id, $address, $port);
                    break;
                case self::PACKET_RECV_PACKET:
                    $id = Binary::readInt(substr($buffer, 0, 4));
                    $packet = substr($buffer, 4);
                    $this->sessions[$id]->handlePacket($packet);
                    break;
                case self::PACKET_CLOSE_SESSION:
                    $id = Binary::readInt(substr($buffer, 0, 4));
                    $this->sessions[$id]->close();
                    break;
            }
        }
    }

}