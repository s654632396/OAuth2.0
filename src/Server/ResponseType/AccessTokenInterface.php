<?php

namespace OAuth\Server\ResponseType;

use OAuth\Server\ResponseTypeInterface;
use OAuth\Server\GrantTypeInterface;

abstract class AccessTokenInterface implements ResponseTypeInterface,GrantTypeInterface
{

    abstract public function createAccessToken(callable $callback, array $request);

    abstract public function createRefreshToken(callable $callback, array $requests);

    abstract public function refreshAccessToken(callable $callback, array $request, array $refresh_token);

    abstract public function getAccessToken();

    abstract public function getRefreshToken();

    abstract public function getTokenType();

}
