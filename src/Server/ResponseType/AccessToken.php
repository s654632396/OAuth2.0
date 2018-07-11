<?php

namespace OAuth\Server\ResponseType;

use OAuth\Server\ResponseType\AccessTokenInterface;

/**
 * AccessToken ResponseType
 */
class AccessToken extends AccessTokenInterface
{
    const TOKEN_TYPE = 'bearer';

    protected $access_token;
    protected $refresh_token;

    public function createRefreshToken(callable $callback, array $requests)
    {
        if (!empty($this->refresh_token)) {
            return call_user_func(
                $callback,
                $requests,              // param 1 : array $request
                $this->refresh_token    // param 2 : accessToken
            );
        }
    }

    public function createAccessToken(callable $callback, array $requests)
    {
        $this->access_token = $this->generateAccessToken();

        switch (isset($requests['authorization_code'])) {
            case self::GRANT_TYPE_AUTHORIZATION_CODE :

                // generate refresh_token because full authorize flow
                $this->refresh_token = $this->generateAccessToken();

            case self::GRANT_TYPE_REFRESH_TOKEN:

                return call_user_func(
                    $callback,
                    $requests,              // param 1 : array $request
                    $this->access_token,    // param 2 : accessToken
                    self::TOKEN_TYPE        // param 3 : tokenType
                );                          // @return Boolean
        }

    }

    public function refreshAccessToken(callable $callback, array $requests, array $refresh_token)
    {
        $this->access_token = $this->generateAccessToken();
        return call_user_func(
            $callback,
            $requests,              // param 1 : array $request
            $this->access_token,    // param 2 : accessToken
            $refresh_token,         // param 3 : the refresh_token data <array>
            self::TOKEN_TYPE        // param 3 : tokenType
        );                          // @return Boolean
    }

    public function getTokenType()
    {
        return self::TOKEN_TYPE;
    }

    public function getAccessToken()
    {
        return $this->access_token;
    }

    public function getRefreshToken()
    {
        return $this->refresh_token;
    }

    public static function checkRefreshToken($origin, $compared)
    {
        return strcmp($origin, $compared) === 0 ? true : false;
    }

    /**
     * Generates an unique access token.
     *
     * Implementing classes may want to override this function to implement
     * other access token generation schemes.
     *
     * @return
     * An unique access token.
     *
     * @ingroup oauth2_section_4
     */
    protected function generateAccessToken()
    {
        if (function_exists('mcrypt_create_iv')) {
            $randomData = mcrypt_create_iv(20, MCRYPT_DEV_URANDOM);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        if (function_exists('openssl_random_pseudo_bytes')) {
            $randomData = openssl_random_pseudo_bytes(20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        if (@file_exists('/dev/urandom')) { // Get 100 bytes of random data
            $randomData = file_get_contents('/dev/urandom', false, null, 0, 20);
            if ($randomData !== false && strlen($randomData) === 20) {
                return bin2hex($randomData);
            }
        }
        // Last resort which you probably should just get rid of:
        $randomData = mt_rand() . mt_rand() . mt_rand() . mt_rand() . microtime(true) . uniqid(mt_rand(), true);
        return substr(hash('sha512', $randomData), 0, 40);
    }

}


