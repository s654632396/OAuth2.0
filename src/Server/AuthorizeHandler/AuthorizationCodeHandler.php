<?php
/**
 * AuthorizationCodeHandler
 * @author Chaofeng Dong
 */

namespace OAuth\Server\AuthorizeHandler;

use OAuth\Response;
use OAuth\Server\ResponseType\AccessTokenInterface;
use OAuth\Server\ResponseType\AuthorizationCodeInterface;
use OAuth\Server\Exception\InvalidRequestException;
use LogicException;

class AuthorizationCodeHandler extends AuthorizeHandlerAbstract
{

    protected $authorization_code;

    public function __construct()
    {
        $this->register = array_merge($this->register, [
            'getAuthCode' => null,
            'createAuthCode' => null,
            'createRefreshToken' => null,
        ]);
    }

    /**
     * @param array $requests
     * @inheritdoc
     */
    public function processAccessToken(array $requests)
    {
        // 验证客户端Server凭证
        $this->checkClientCredentials($requests);
        // 获取authorization_code详情
        $authCode = $this->getAuthCode($requests);
        if ($authCode == false) {
            throw new InvalidRequestException("Authorization Code verify failed.", 3107);
        }
        $data = [
            'request_uri' => $requests['request_uri'],
        ];
        $requests = array_merge($requests, $authCode);
        @list($data['access_token'], $data['token_type']) = $this->createAccessToken($requests);
        if ($data['access_token']) {
            $data['refresh_token'] = $this->createRefreshToken($requests);
        }

        $response = $this->makeTokenResponse($requests, $data);
        return $response;
    }

    /**
     * @param array $requests
     * @inheritdoc
     */
    public function processAuthorize(array $requests, array $clientDetails)
    {
        $this->checkScope($requests, $clientDetails['scope']);
        $this->authorization_code = $this->createAuthCode($requests);

        if ($this->authorization_code === false) {
            throw new LogicException('create authorization code failed.', 2006);
        }

        // check all passed
        // build response
        $response = $this->makeAuthResponse($requests, $clientDetails);
        return $response;
    }



    /**
     * @param array $requests
     * @return mixed
     * @throws LogicException
     */
    public function getAuthCode(array $requests)
    {
        if (!is_null($this->register['getAuthCode']) && is_callable($this->register['getAuthCode'])) {
            return call_user_func($this->register['getAuthCode'], $requests);
        } else {
            throw new LogicException("please define callable function <getAuthCode> as preparation.", 2106);
        }
    }

    public function createRefreshToken(array $requests)
    {
        if ($this->responseType instanceof AccessTokenInterface) {
            if ($this->responseType->createRefreshToken($this->register[__FUNCTION__], $requests)) {
                return $this->responseType->getRefreshToken();
            } else {
                return false;
            }
        } else {
            throw new LogicException("please add a ResponseType(AccessTokenInterface) as preparation.", 2102);
        }
    }

    /**
     * @param array $requests
     * @return mixed
     */
    public function createAuthCode(array $requests)
    {
        if ($this->responseType instanceof AuthorizationCodeInterface) {
            return
                $this->responseType->createAuthorizationCode($this->register[__FUNCTION__], $requests);
        } else {
            throw new LogicException("please add a ResponseType(AuthorizationCodeInterface) as preparation.", 2101);
        }
    }



    public function makeTokenResponse(array $requests, array $data)
    {
        $response = new Response();
        $redirect_uri = isset($requests['redirect_uri']) ? $requests['redirect_uri'] : '';
        if (empty($redirect_uri)) {
            $registered_redirect_uri = $data['redirect_uri'];
        }
        if (empty($data['refresh_token'])) {
            throw new LogicException("refresh_token create failed.", 2110);
        } else if (empty($data['access_token'])) {
            throw new LogicException("access_token create failed.", 2111);
        }


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

    public function makeAuthResponse(array $requests, array $details)
    {
        $response = new Response();

        $redirect_uri = isset($requests['redirect_uri']) ? $requests['redirect_uri'] : '';
        if (empty($redirect_uri)) {
            $registered_redirect_uri = $details['redirect_uri'];
        }
        if (empty($redirect_uri) && !empty($registered_redirect_uri)) {
            $redirect_uri = $registered_redirect_uri;
        }

        $params = [];
        $params['query']['code'] = $this->authorization_code;
        $params['query']['state'] = isset($requests['state']) ? $requests['state'] : '';
        $uri = $this->buildUri($redirect_uri, $params);
        $response->setRedirect(302, $uri);
        return $response;
    }

}
