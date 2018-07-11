<?php
/**
 * Created by PhpStorm.
 * Date: 2018/2/2
 * Time: 19:36
 */

namespace OAuth\Client\Factory\AuthorizationCode;


use OAuth\Client\Factory\AuthorizationInterface;
use OAuth\Curl;
use OAuth\CurlResponse;

class Authorization implements AuthorizationInterface
{
    public $redirectUrl;

    private $params;

    public function processList()
    {
        return [
            'setRedirectUrl' => [__CLASS__, 'setRedirectUrl'],
        ];
    }

    public function bindParams($event, array $param)
    {
        $this->params[$event] = $param;
    }

    public function process(Curl $Curl, callable $customProcess = null)
    {
        try {
            foreach ($this->processList() as $event => $callable)
            {
                call_user_func_array($callable, $this->params[$event]);
            }

        } catch (\Exception $e) {
            $curlResponse = new CurlResponse($e->getMessage());
            $this->errHandle($curlResponse, $e);
        }
    }

    public function errHandle(CurlResponse $curlResponse, \Exception $exception)
    {
        // TODO: Implement errHandle() method.
    }

    public function setRedirectUrl($url)
    {
        $this->redirectUrl = $url;
    }
}