<?php
/**
 * Created by PhpStorm.
 * Date: 2018/2/2
 * Time: 17:24
 */

namespace OAuth\Client;

require_once dirname(dirname(__FILE__)) . '/autoload.php';

use OAuth\Client\Factory\HandlerInterface;


class AuthClient
{
    public $handler;

    public function __construct($type = 'authorization_code')
    {
        $type = strtoupper($type);
        $factory = __NAMESPACE__ . "\\Factory\\" . constant(__NAMESPACE__ . "\\Factory\\HandlerInterface::$type") . "Factory";

        if (class_exists($factory) && in_array(__NAMESPACE__ . "\\Factory\\HandlerInterface", class_implements($factory))) {
            $this->handler = new $factory;
            return $this;
        } else {
            throw new \UnexpectedValueException("called auth client type undefined.");
        }
    }

    public function selectEvent($eventType)
    {
        if ($this->handler instanceof HandlerInterface) {
            $this->handler->event($eventType);
        }
    }

    public function setEvent()
    {
        if ($this->handler instanceof HandlerInterface) {

        }
    }


}