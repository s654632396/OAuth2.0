<?php

namespace OAuth\Server\ResponseType;

use OAuth\Server\ResponseTypeInterface;
use OAuth\Server\GrantTypeInterface;

abstract class AuthorizationCodeInterface implements ResponseTypeInterface,GrantTypeInterface
{

    abstract public function createAuthorizationCode(callable $callback, array $request);

    abstract public function getAuthorizationCode();
}
