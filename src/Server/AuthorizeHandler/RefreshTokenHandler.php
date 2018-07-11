<?php

namespace OAuth\Server\AuthorizeHandler;

use OAuth\Response;
use OAuth\Server\Exception\InvalidRequestException;
use OAuth\Server\Exception\InvalidTokenException;
use OAuth\Server\ResponseType\AccessTokenInterface;
use LogicException;

class RefreshTokenHandler extends AuthorizeHandlerAbstract
{

    public function __construct()
    {
        $this->register = array_merge($this->register, [
            'refreshToken' => null,
            'getRefreshTokenDetails' => null,
        ]);
    }


    public function processAccessToken(array $requests)
    {
        // 验证客户端Server凭证
        $this->checkClientCredentials($requests);

        $refresh_token = $this->getRefreshTokenDetails($requests);

        if (empty($refresh_token)) {
            throw new InvalidRequestException("invalid requests.", 3001);
        } else if (!isset($refresh_token['scope'])) {
            throw new LogicException("registered function (getRefreshTokenDetails) must include field[scope]", 2014);
        }

        $this->checkScope($requests, $refresh_token['scope']);

        @list($accessToken, $tokenType) = $this->refreshToken($requests, $refresh_token);

        if ($accessToken === false) {
            throw new InvalidTokenException("refresh token failed.", 3015);
        }
        $data = [
            'access_token' => $accessToken,
            'refresh_token' => $refresh_token['refresh_token'],
            'token_type' => $tokenType,
            'scope' => isset($requests['scope']) ? $requests['scope'] : $refresh_token['scope'],
        ];

        $response = $this->makeTokenResponse($requests, $data);
        return $response;
    }

    public function getRefreshTokenDetails(array $requests)
    {
        if (!is_null($this->register[__FUNCTION__]) && is_callable($this->register[__FUNCTION__])) {
            return call_user_func($this->register[__FUNCTION__], $requests);
        }
        throw new LogicException("please add a ResponseType(AccessTokenInterface) as preparation.", 2102);
    }

    public function refreshToken(array $requests, array $refresh_token)
    {
        if ($this->responseType instanceof AccessTokenInterface) {
            if ($this->responseType->refreshAccessToken($this->register[__FUNCTION__], $requests, $refresh_token)) {
                return [
                    $this->responseType->getAccessToken(),
                    $this->responseType->getTokenType(),
                ];
            } else {
                return false;
            }
        } else {
            throw new LogicException("please add a ResponseType(AccessTokenInterface) as preparation.", 2104);
        }
    }

    /**
     * nothing need to write, only to implements this method
     * @param array $requests
     * @param array $details
     * @return Response|void
     */
    public function processAuthorize(array $requests, array $details)
    {
    }

    private function makeTokenResponse(array $requests, $data)
    {
        $response = new Response();

        $response->addHttpHeaders([
            'Pragma' => 'no-cache',
            'Cache-Control' => 'no-store',
        ]);
        $params = [
            'access_token' => $data['access_token'],
            'token_type' => $data['token_type'],
            'refresh_token' => $data['refresh_token'],
        ];
        $response->addParameters($params);


        return $response;
    }


}
