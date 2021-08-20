<?php

declare(strict_types=1);

namespace AkmalFairuz\VNet\network;

use AkmalFairuz\VNet\Server;
use AkmalFairuz\VNet\utils\Binary;
use pocketmine\snooze\SleeperNotifier;
use Socket;
use Threaded;
use AkmalFairuz\VNet\network\socket\ClientSocket;
use AkmalFairuz\VNet\network\socket\ServerSocket;
use AkmalFairuz\VNet\network\socket\SocketException;
use AkmalFairuz\VNet\utils\Logger;

class ServerManager{

    private ServerSocket $serverSocket;

    public function __construct(private ServerThread $thread, private Logger $logger, private SleeperNotifier $notifier, private Threaded $external, private Threaded $internal, private Socket $ipcSocket, private string $address, private int $port, private int $maxClients, private bool $whitelistEnable, private array $whitelist) {
        $this->serverSocket = new ServerSocket($this, $this->logger, $this->address, $this->port, $this->maxClients, $this->whitelistEnable, $this->whitelist);
        $this->process();
    }

    public function process() {
        while(!$this->thread->isKilled) {
            $this->tick();
        }
    }

    private function tick() {
        $sockets = array_map(function(ClientSocket $client) : Socket{
            return $client->getSocket();
        }, $this->serverSocket->getClients());
        $sockets[0] = $this->serverSocket->getSocket();
        $sockets[-1] = $this->ipcSocket;
        $w = null;
        $e = null;
        if(socket_select($sockets, $w, $e, 1, 0) > 0) {
            foreach($sockets as $id => $socket) {
                if($id === 0) {
                    $this->serverSocket->accept();
                } elseif ($id === -1) {
                    socket_read($this->ipcSocket, 255);
                    $this->processMainPacket();
                } else {
                    $client = $this->serverSocket->getClient($id);
                    try{
                        $this->processClient($client);
                    } catch(SocketException $e) {
                        $this->logger->error($e);
                        $client->close();
                    }
                }
            }
        }
    }

    private function processMainPacket() {
        while(($buf = $this->readMainToThreadPacket()) !== null) {
            $pid = ord($buf[0]);
            $buffer = substr($buf, 1);
            switch($pid) {
                case Server::PACKET_SEND_PACKET:
                    $id = Binary::readInt(substr($buffer, 0, 4));
                    $client = $this->serverSocket->getClient($id);
                    if($client !== null){
                        try{
                            $client->write(substr($buffer, 4));
                        }catch(SocketException $exception){
                            $this->logger->error(sprintf("Failed send packet to %s/%d: %s", $client->address, $client->port, $exception->getMessage()));
                        }
                    }
                    break;
                case Server::PACKET_CLOSE_SESSION:
                    $id = Binary::readInt(substr($buffer, 0, 4));
                    $client = $this->serverSocket->getClient($id);
                    if($client !== null) {
                        $client->disconnect(Binary::readByte($buffer[4]));
                    }
                    break;
            }
        }
    }

    public function pushThreadToMainPacket(string $packet) {
        $this->internal[] = $packet;
        $this->notifier->wakeupSleeper();
    }

    public function readMainToThreadPacket() : ?string{
        return $this->external->shift();
    }

    /**
     * @throws SocketException
     */
    private function processClient(ClientSocket $client) {
        if($client->waitClose) {
            $client->close();
            return;
        }
        $this->pushThreadToMainPacket(chr(Server::PACKET_RECV_PACKET) . Binary::writeInt($client->getId()) . $client->read());
    }
}