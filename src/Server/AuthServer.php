<?php
/**
 * AuthServer
 * @author Chaofeng Dong
 */
namespace OAuth\Server;

require_once dirname(dirname(__FILE__)) . '/autoload.php';

use OAuth\Response;
use OAuth\Request;

use OAuth\Server\Exception\InvalidGrantException;
use OAuth\Server\Exception\InvalidRequestException;
use OAuth\Server\ResourceHandler\ResourceHandler;
use OAuth\Server\ResourceHandler\ResourceHandlerInterface;

use OAuth\Server\ResponseType\AccessToken;
use OAuth\Server\ResponseType\AuthorizationCode;

use OAuth\Server\AuthorizeHandler\AuthorizationCodeHandler;
use OAuth\Server\AuthorizeHandler\RefreshTokenHandler;
use OAuth\Server\AuthorizeHandler\AuthorizeHandlerInterface;

use Exception;
use OAuth\Server\Exception\UnsupportedGrantTypeException;
use OAuth\Server\Exception\ResponseExceptionAbstract;

class AuthServer
{

    protected $requests;
    protected $origin_requests;

    protected $grant_type;
    protected $response_type;


    public function __construct()
    {
        $this->origin_requests = Request::createFromGlobals();
    }

    /**
     * @param $name
     * @param null $default
     * @return mixed
     */
    public function getRequestHeader($name, $default = null)
    {
        return $this->origin_requests->headers($name, $default);
    }

    /**
     * 获取accessToken请求handler
     * @param array $requests
     * @return AuthorizationCodeHandler|RefreshTokenHandler
     */
    public function accessToken(array $requests)
    {
        $this->requests = $requests;
        if (empty($requests['grant_type'])) {
            throw new InvalidGrantException('', 3000);
        }
        return $this->grantType($requests['grant_type']);
    }

    /**
     * array $requests
     * return AuthorizeHandler $handler
     * @param array $requests
     * @return AuthorizeHandlerInterface
     */
    public function authorize(array $requests)
    {
        $this->requests = $requests;
        if (empty($requests['response_type'])) {
            throw new InvalidRequestException('', 3000);
        }
        return $this->responseType($requests['response_type']);
    }

    /**
     * 获取资源请求handler
     * @return ResourceHandler
     */
    public function resource()
    {
        return new ResourceHandler($this->origin_requests);
    }

    /**
     * 处理 protected resource 请求
     * 如果
     * @param ResourceHandlerInterface $handler
     * @param array $protected
     * @param callable $callback
     * @return mixed callable's return
     * @throws Exception
     */
    public function handleResourceRequest(ResourceHandlerInterface $handler, array $protected, callable $callback = null)
    {
        try {

            $handler->processResource($protected);

            if (!is_null($callback) && is_callable($callback)) {
                // callback parameters: (Response $response, ResourceHandlerInterface $handler, Request $requests)
                $response = call_user_func($callback, (new Response), $handler, $this->origin_requests);
                return $response;
            }

            return true;

        } catch (Exception $e) {
            if ($e instanceof ResponseExceptionAbstract) {
                $response = new Response();
                $response->setError($e->getAssertStatusCode(), $e->getErrorMessage(), $e->getErrorDescription(), $e->getErrorUri());
                $response->send();
                exit();
            }
            throw $e;
        }
    }

    /**
     * 处理AccessToken请求
     * @param AuthorizeHandlerInterface $handler
     * @return Response
     * @throws Exception
     * @t
     */
    public function handleAccessTokenRequest(AuthorizeHandlerInterface $handler)
    {
        try {
            $response = $handler->processAccessToken($this->requests);
            return $response;
        } catch (Exception $e) {
            if ($e instanceof ResponseExceptionAbstract) {
                $response = new Response();
                $response->setError($e->getAssertStatusCode(), $e->getErrorMessage(), $e->getErrorDescription(), $e->getErrorUri());
                $response->send();
                exit();
            }
            throw $e;
        }
    }


    /**
     * 处理Authorize授权请求
     * @param AuthorizeHandlerInterface $handler
     * @param array $clientDetails
     * @return Response
     * @throws Exception
     */
    public function handleAuthorizeRequest(AuthorizeHandlerInterface $handler,array $clientDetails)
    {
        try {
            $response = $handler->processAuthorize($this->requests, $clientDetails);
            return $response;

        } catch (Exception $e) {
            if ($e instanceof ResponseExceptionAbstract) {
                $response = new Response();
                $response->setError($e->getAssertStatusCode(), $e->getErrorMessage(), $e->getErrorDescription(), $e->getErrorUri());
                $response->send();
                exit();
            }
            throw $e;
        }
    }

    public function setError($statusCode, $error, $errorDescription = null, $errorUri = null)
    {
        $response = new Response();
        $response->setError($statusCode, $error, $errorDescription, $errorUri);
        $response->send();
        exit;
    }

    /**
     * @param $grantType
     * @return AuthorizationCodeHandler|RefreshTokenHandler
     */
    protected function grantType($grantType)
    {
        $this->grant_type = $grantType;
        if ($grantType == GrantTypeInterface::GRANT_TYPE_AUTHORIZATION_CODE) {
            $handler = new AuthorizationCodeHandler();
            $handler->addResponseType(new AccessToken());
            return $handler;
        } else if ($grantType == GrantTypeInterface::GRANT_TYPE_REFRESH_TOKEN) {
            $handler = new RefreshTokenHandler();
            $handler->addResponseType(new AccessToken());
            return $handler;
        } else {
            throw new UnsupportedGrantTypeException("grant not supported for now.", 4001);
        }
    }

    /**
     * @param $responseType
     * @return AuthorizeHandler\AuthorizeHandlerInterface
     */
    protected function responseType($responseType)
    {
        $this->response_type = $responseType;
        if ($responseType == ResponseTypeInterface::RESPONSE_TYPE_AUTHORIZATION_CODE) {
           // authorization code Mode 
            $handler = new AuthorizationCodeHandler();
            $handler->addResponseType(new AuthorizationCode());
            return $handler;
            
        } else if ($responseType == ResponseTypeInterface::RESPONSE_TYPE_IMPLICIT) {
           // implicit Mode
            throw new UnsupportedGrantTypeException("response_type not supported for now.", 4001);
        } else {
            throw new UnsupportedGrantTypeException("response_type not supported.", 4002);
        }
    }

    public function responseException(Exception $e)
    {
        if ($e instanceof ResponseExceptionAbstract) {
            $response = new Response();
            $response->setError($e->getAssertStatusCode(), $e->getErrorMessage(), $e->getErrorDescription(), $e->getErrorUri());
            $response->send();
            exit();
        }
        throw $e;
        exit();
    }

}
