<?php
/**
 * Created by PhpStorm.
 * Date: 2018/2/2
 * Time: 14:49
 */

namespace OAuth\Server\Exception;

interface OAuthExceptionInterface
{
    const INVALID_GRANT_MESSAGE = 'invalid_grant';
    const UNSUPPORTED_GRANT_TYPE_MESSAGE = 'unsupported_grant_type';
    const INVALID_CLIENT_MESSAGE = 'invalid_client';
    const INVALID_REQUEST_MESSAGE = 'invalid_request';
    const INVALID_TOKEN_MESSAGE = 'invalid_token';
    const INSUFFICIENT_SCOPE_MESSAGE = 'insufficient_scope';

    public function getErrorMessage();

    public function getErrorDescription();

    public function getAssertStatusCode();

    public function getErrorUri();

}