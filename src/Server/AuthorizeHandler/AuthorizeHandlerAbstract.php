<?php
/**
 * AuthorizeHandlerAbstractClass
 * @author Chaofeng Dong
 */

namespace OAuth\Server\AuthorizeHandler;

use OAuth\Server\Exception\InsufficientScopeException;
use OAuth\Server\ResponseType\AccessTokenInterface;
use OAuth\Server\ResponseTypeInterface;
use LogicException;
use UnexpectedValueException;
use OAuth\Server\Exception\InvalidClientException;

abstract class AuthorizeHandlerAbstract implements AuthorizeHandlerInterface
{

    protected $responseType;

    protected $register = [
        'checkClientCredentials' => null,
        'createAccessToken' => null,
        'checkScope' => null,
    ];

    /**
     * @param array $requests
     * @inheritdoc
     */
    public function addResponseType(ResponseTypeInterface $responseType)
    {
        $this->responseType = $responseType;
    }

    /**
     * @param array $requests
     * @inheritdoc
     */
    public function registerCallback($name, callable $callable)
    {
        if (in_array($name, array_keys($this->register))) {
            $this->register[$name] = $callable;
            return $this;
        } else {
            throw new UnexpectedValueException("unknown handle register name($name).", 2000);
        }
    }

    /**
     * @param array $requests
     * @return bool
     */
    public function checkClientCredentials(array $requests)
    {
        if (!is_null($this->register['checkClientCredentials']) && is_callable($this->register['checkClientCredentials'])) {
            if (call_user_func($this->register['checkClientCredentials'], $requests)) {
                return true;
            }
            throw new InvalidClientException("client_id checked failure.", 3005);
        } else {
            throw new LogicException("please define callable function (checkClientCredentials) as preparation.", 2105);
        }
    }

    /**
     * @param array $requests
     * @return array|bool
     */
    public function createAccessToken(array $requests)
    {
        if ($this->responseType instanceof AccessTokenInterface) {
            if ($this->responseType->createAccessToken($this->register[__FUNCTION__], $requests)) {
                return [
                    $this->responseType->getAccessToken(),
                    $this->responseType->getTokenType(),
                ];
            } else {
                return false;
            }
        } else {
            throw new LogicException("please add a ResponseType(AccessTokenInterface) as preparation.", 2101);
        }
    }

    /**
     * Build the absolute URI based on supplied URI and parameters.
     *
     * @param string $uri An absolute URI.
     * @param array $params Parameters to be append as GET.
     *
     * @return string
     * An absolute URI with supplied parameters.
     *
     * @ingroup oauth2_section_4
     */
    protected function buildUri($uri, $params)
    {
        $parse_url = parse_url($uri);

        // Add our params to the parsed uri
        foreach ($params as $k => $v) {
            if (isset($parse_url[$k])) {
                $parse_url[$k] .= "&" . http_build_query($v, '', '&');
            } else {
                $parse_url[$k] = http_build_query($v, '', '&');
            }
        }

        // Put the uri back together
        return
            ((isset($parse_url["scheme"])) ? $parse_url["scheme"] . "://" : "")
            . ((isset($parse_url["user"])) ? $parse_url["user"]
                . ((isset($parse_url["pass"])) ? ":" . $parse_url["pass"] : "") . "@" : "")
            . ((isset($parse_url["host"])) ? $parse_url["host"] : "")
            . ((isset($parse_url["port"])) ? ":" . $parse_url["port"] : "")
            . ((isset($parse_url["path"])) ? $parse_url["path"] : "")
            . ((isset($parse_url["query"]) && !empty($parse_url['query'])) ? "?" . $parse_url["query"] : "")
            . ((isset($parse_url["fragment"])) ? "#" . $parse_url["fragment"] : "");
    }

    /**
     * check scope if out range
     * @access public
     * @param array $requests
     * @param $scopeCheck
     * @return bool
     */
    public function checkScope(array $requests, $scopeCheck)
    {
        $scope = $this->scopeIn($requests['scope']);
        $check_scope = $this->scopeIn($scopeCheck);

        if (array_diff($scope, $check_scope)) {
            throw new InsufficientScopeException("scope out of range!", 3002);
        }

        return true;
    }

    /**
     * @param $scope
     * @return mixed
     */
    protected function scopeIn($scope)
    {
        if (is_callable($this->register['checkScope'])) {
            return call_user_func($this->register['checkScope'], $scope);
        } else {
            // invoke <defaultScopeCall> while do not register <scopeIn> function
            return call_user_func(__CLASS__ . '::defaultScopeCall', $scope);
        }
    }

    /**
     * @param $scope
     * @return array
     */
    protected static function defaultScopeCall($scope)
    {
        return array_filter(explode(',', $scope), 'trim');
    }

}
