<?php

declare(strict_types=1);

namespace AkmalFairuz\VNet\network\socket;

abstract class Socket{

    protected \Socket $socket;

    public function getSocket() : \Socket {
        return $this->socket;
    }

    public function setNonBlocking() {
        socket_set_nonblock($this->socket);
    }

    public function setOption(int $level, int $option, $value) {
        socket_set_option($this->socket, $level, $option, $value);
    }

    public function shutdown(int $mode) {
        socket_shutdown($this->socket, $mode);
    }

    public function close() {
        @socket_close($this->socket);
    }
}