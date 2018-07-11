<?php

namespace OAuth\Server;

interface ResponseTypeInterface
{
    // auth response_type
    const RESPONSE_TYPE_AUTHORIZATION_CODE = 'code';
    const RESPONSE_TYPE_IMPLICIT = 'token';
    const RESPONSE_TYPE_PASSWORD_CREDENTIALS = 'password';
    const RESPONSE_TYPE_CLIENT_CREDENTIALS = 'client_credentials';



}
