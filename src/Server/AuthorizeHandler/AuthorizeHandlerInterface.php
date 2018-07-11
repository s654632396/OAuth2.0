<?php
/**
 * AuthorizeHandlerInterface
 * @author Chaofeng Dong
 */
namespace OAuth\Server\AuthorizeHandler;

use OAuth\Server\ResponseTypeInterface;

interface AuthorizeHandlerInterface
{

    /**
     * 注册对应处理的回调方法
     * @param $name
     * @param callable $callable
     * @return $this
     */
    public function registerCallback($name, callable $callable);

    /**
     * 处理 AuthorizeRequest
     * @param array $requests
     * @param array $details
     * @return \OAuth\Response ResponseObject
     */
    public function processAuthorize(array $requests, array $details);

    /**
     * 处理 AccessTokenRequest
     * @param array $requests
     * @return \OAuth\Response ResponseObject
     */
    public function processAccessToken(array $requests);

    /**
     * @param ResponseTypeInterface $responseType
     * @return null
     */
    public function addResponseType(ResponseTypeInterface $responseType);
}
