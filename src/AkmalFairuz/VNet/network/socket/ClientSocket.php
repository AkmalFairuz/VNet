<?php

declare(strict_types=1);

namespace AkmalFairuz\VNet\network\socket;

class ClientSocket extends Socket{

    public bool $waitClose = false;

    public function __construct(public ServerSocket $server, private int $id, \Socket $socket, public string $address, public int $port) {
        $this->socket = $socket;
    }

    public function getId(): int{
        return $this->id;
    }

    /**
     * @throws SocketException
     */
    public function write(string $buffer, int $length = null) {
        $ret = @socket_write($this->socket, $buffer, $length);
        if($ret === false) {
            throw new SocketException("Unable to write socket " . socket_strerror(socket_last_error($this->socket)));
        }
    }

    /**
     * @throws SocketException
     */
    public function read(int $length = 65535): string{
        $ret = @socket_read($this->socket, $length);
        if($ret === false) {
            throw new SocketException("Unable to read socket ".socket_strerror(socket_last_error($this->socket)));
        }
        return $ret;
    }

    public function disconnect(int $mode) {
        $this->setOption(SOL_SOCKET, SO_LINGER, ["l_onoff" => 1, "l_linger" => 1]);
        $this->shutdown($mode);
        $this->waitClose = true;
        $this->server->logger->debug(sprintf("Disconnecting %s/%d", $this->address, $this->port));
    }

    public function close() {
        parent::close();
        $this->server->logger->debug(sprintf("Closed %s/%d", $this->address, $this->port));
        $this->server->closeClient($this->id);
    }
}