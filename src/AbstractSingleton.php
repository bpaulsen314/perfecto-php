<?php
namespace Bpaulsen314\Perfecto;

abstract class AbstractSingleton extends Object
{
    public static function getInstance()
    {
        static $instance = null;
        if (!$instance) {
            $instance = new static();
        }
        return $instance;
    }

    protected function __construct()
    {
    }

    private function __clone()
    {
    }

    private function __wakeup()
    {
    }
}
