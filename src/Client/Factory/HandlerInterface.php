<?php
/**
 * Created by PhpStorm.
 * Date: 2018/2/2
 * Time: 17:44
 */

namespace OAuth\Client\Factory;


interface HandlerInterface
{
    const EVENT_AUTHORIZATION = 1;
    const EVENT_TOKEN = 2;
    const EVENT_REFRESH_TOKEN = 3;
    const EVENT_ACCESS_RESOURCE = 4;

    const AUTHORIZATION_CODE = 'AuthorizationCode';
    const IMPLICIT = 'Implicit';
    const PASSWORD = 'PasswordCredentials';
    const CLIENT_CREDENTIALS = 'ClientCredentials';

    public function event($eventType);


    public function exec();
}