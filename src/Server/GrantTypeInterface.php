<?php

namespace OAuth\Server;

interface GrantTypeInterface
{

    const GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';
    const GRANT_TYPE_PASSWORD = 'password';
    const GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
    const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

}
