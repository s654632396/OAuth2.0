<?php
/**
 * Created by PhpStorm.
 * Date: 2018/2/2
 * Time: 17:40
 */

namespace OAuth\Client\Factory;


use OAuth\Curl;

class AuthorizationCodeFactory implements HandlerInterface
{
    protected $Curl = null;
    protected $event;

    public function event($event = self::EVENT_AUTHORIZATION, $class = null)
    {
        switch ($event) {
            case self::EVENT_AUTHORIZATION:
                $this->event = new (!is_null($class) ? $class : __NAMESPACE__ . "AuthorizationCode\\Authorization");
                break;
            case self::EVENT_TOKEN:
            case self::EVENT_REFRESH_TOKEN:
            case self::EVENT_ACCESS_RESOURCE:
                throw new \UnexpectedValueException("this is a feature event type.");
                break;
            default:
                throw new \UnexpectedValueException("undefined event type.");
                break;
        }
        $this->Curl = $this->Curl ? : new Curl();
    }

    public function bind($name, $param, callable $callable = null)
    {

    }

    public function exec()
    {

    }

}