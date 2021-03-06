<?php

namespace OAuth\Server\ResponseType;

use OAuth\Server\ResponseType\AuthorizationCodeInterface;

/**
 * AuthorizationCode ResponseType
 */
class AuthorizationCode extends AuthorizationCodeInterface
{
    protected $authorization_code;

    public function createAuthorizationCode(callable $callback, array $requests)
    {
        $this->authorization_code = $this->generateAuthorizationCode();
        return call_user_func($callback, $requests, $this->authorization_code);
    }

    public function getAuthorizationCode()
    {
        return $this->authorization_code;
    }

    /**
     * Generates an unique auth code.
     *
     * Implementing classes may want to override this function to implement
     * other auth code generation schemes.
     *
     * @return
     * An unique auth code.
     *
     * @ingroup oauth2_section_4
     */
    protected function generateAuthorizationCode()
    {
        $tokenLen = 40;
        if (function_exists('mcrypt_create_iv')) {
            $randomData = mcrypt_create_iv(100, MCRYPT_DEV_URANDOM);
        } elseif (function_exists('openssl_random_pseudo_bytes')) {
            $randomData = openssl_random_pseudo_bytes(100);
        } elseif (@file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 100) . uniqid(mt_rand(), true);
        } else {
            $randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);
        }
        return substr(hash('sha512', $randomData), 0, $tokenLen);
    }
}


