<?php

declare(strict_types=1);

namespace AkmalFairuz\VNet\thread;

class ThreadManager extends \Volatile{

    /** @var ThreadManager|null */
    private static $instance = null;

    public static function init() : void{
        self::$instance = new ThreadManager();
    }

    public static function getInstance() : ThreadManager{
        if(self::$instance === null){
            self::$instance = new ThreadManager();
        }
        return self::$instance;
    }

    public function add(Thread $thread) : void{
        $this[spl_object_id($thread)] = $thread;
    }

    public function remove(Thread $thread) : void{
        unset($this[spl_object_id($thread)]);
    }

    public function getAll() : array{
        $array = [];
        foreach($this as $key => $thread){
            $array[$key] = $thread;
        }

        return $array;
    }

    public function stopAll() : int{

        $erroredThreads = 0;

        foreach($this->getAll() as $thread){
            try{
                $thread->quit();
            }catch(ThreadException $e){
                ++$erroredThreads;
            }
        }

        return $erroredThreads;
    }
}