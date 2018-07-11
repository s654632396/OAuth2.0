<?php
/**
 * Created by PhpStorm.
 * Date: 2018/2/2
 * Time: 19:40
 */

namespace OAuth\Client\Factory;

use Exception;
use OAuth\Curl;
use OAuth\CurlResponse;

interface AuthorizationInterface
{
    public function processList();

    public function process(Curl $curl, callable $customProcess = null);

    public function errHandle(CurlResponse $curlResponse, Exception $exception);
}