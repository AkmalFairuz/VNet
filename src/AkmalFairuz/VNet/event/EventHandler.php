<?php

declare(strict_types=1);

namespace AkmalFairuz\VNet\event;

use AkmalFairuz\VNet\network\Session;
use AkmalFairuz\VNet\Server;

class EventHandler{

    /** @var callable */
    private $recvEvent = null;
    /** @var callable */
    private $connectEvent = null;
    /** @var callable */
    private $closeEvent = null;

    public function __construct(private Server $server) {
    }

    public function callRecv(Session $session, string $packet) {
        if($this->recvEvent !== null) {
            try{
                ($this->recvEvent)($session, $packet);
            } catch(\Exception $exception) {
                $this->server->getLogger()->error("Error handling recvEvent: " . $exception->getMessage());
            }
        }
    }

    public function callConnect(Session $session) {
        if($this->connectEvent !== null) {
            try{
                ($this->connectEvent)($session);
            } catch(\Exception $exception) {
                $this->server->getLogger()->error("Error handling connectEvent: " . $exception->getMessage());
            }
        }
    }

    public function callClose(Session $session) {
        if($this->closeEvent !== null) {
            try{
                ($this->closeEvent)($session);
            } catch(\Exception $exception) {
                $this->server->getLogger()->error("Error handling closeEvent: " . $exception->getMessage());
            }
        }
    }

    public function onRecv(callable $c): static{
        $this->recvEvent = $c;
        return $this;
    }

    public function onConnect(callable $c): static{
        $this->connectEvent = $c;
        return $this;
    }

    public function onClose(callable $c): static{
        $this->closeEvent = $c;
        return $this;
    }
}