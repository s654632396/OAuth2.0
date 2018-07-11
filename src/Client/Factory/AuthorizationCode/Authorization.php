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
            /*
            $host = $_SERVER['HTTP_HOST'];

            $url = "http://{$host}/OAuth2.0/authorization";
            $parameters = [
                'response_type' => 'code',
                'client_id' => 'aaaaa',
                'redirect_uri' => "http://{$host}/test/fake/redirectCallback",
                'scope' => 'a,c',
                'state' => 'accbjnd',
            ];

            if (I('get.silent') == 1) {
                $mid = 336566;
                $mid = Encrypt::authcode("{$mid}-jiayouapp", "ENCODE");
                $mid = base64_encode($mid);
                $parameters['userId'] = $mid;
            }

            $url .= "?" . http_build_query($parameters);
            header("Location: {$url}");
             */
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