<?php
/**
 * Created by PhpStorm.
 * Date: 2018/2/1
 * Time: 17:53
 */

namespace OAuth\Server\ResourceHandler;

use LogicException;
use OAuth\Request;
use OAuth\Server\Exception\InvalidTokenException;
use UnexpectedValueException;
use OAuth\Server\Exception\InvalidRequestException;
use OAuth\Server\Exception\InsufficientScopeException;


class ResourceHandler implements ResourceHandlerInterface
{
    protected $token;
    protected $tokenType;
    protected $request_uri;
    public $token_lifetime = 3600;
    protected $register = [
        'getToken' => null,
        'checkScope' => null,
        'checkWithProtect' => null,
    ];

    public function __construct(Request $request)
    {
        $authentication = $request->headers("authorization");
        @list($this->tokenType, $this->token) = sscanf($authentication, "%s %s");
        $this->request_uri = $request->server("PATH_INFO");
    }

    /**
     * @param array $protected
     * @return boolean
     */
    public function processResource(array $protected)
    {
        // 检测是否受保护
        if ($this->checkWithProtect($protected)) {
            // 获取token
            $accessToken = $this->getToken();
            if (!$accessToken) {
                throw new InvalidRequestException("token not exists.", 3001);
            }
            if (!isset($accessToken['access_token'])) {
                throw new LogicException("registered function (getToken) must include field[access_token].", 2406);
            } else if (!isset($accessToken['expires'])) {
                throw new LogicException("registered function (getToken) must include field[expires].", 2406);
            } else if (!isset($accessToken['scope'])) {
                throw new LogicException("registered function (getToken) must include field[scope].", 2406);
            }
            // 验证token有效期
            if (strtotime($accessToken['expires']) + $this->token_lifetime < time()) {
                throw new InvalidTokenException("access_token expired.", 3003);
            }
            // 验证accessTokenScope
            if (false === $this->checkScope($protected, $accessToken['scope'])) {
                throw new InsufficientScopeException("scope out of range.", 3002);
            }
        }

        return true;
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
     * @param array $protected
     * @return bool|mixed
     */
    protected function checkWithProtect(array $protected)
    {
        $callback = $this->register['checkWithProtect'];
        if (!is_null($callback) && is_callable($callback)) {
            return call_user_func($callback, $this->request_uri, $protected); // @return <Array>$tokenData | false
        } else {
            return $this->checkWithProtectDefault($this->request_uri, $protected);
        }
    }

    protected function checkWithProtectDefault($request_uri, array $protected)
    {
        // 先检测request_uri 在不在protected资源内
        foreach ($protected as $protected_uri => $scope) {
            if (strpos($protected_uri, '/')) {
                if ($protected_uri == $request_uri) {
                    return true;
                }
            } else {
                if ($protected_uri == substr($request_uri,strpos($request_uri, '/') + 1)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param array $protected
     * @param string $originScope
     * @return boolean
     */
    protected function checkScope(array $protected, $originScope)
    {
        $callback = $this->register['checkScope'];
        if (!is_null($callback) && is_callable($callback)) {
            return call_user_func($callback, $this->request_uri, $protected, $originScope); // @return <Array>$tokenData | false
        } else {
            return $this->checkScopeDefault($this->request_uri, $protected, $originScope);
        }
    }

    public function checkScopeDefault($request_uri, array $protected, $originScope)
    {

        if (is_callable($this->register['checkScope'])) {
            $restore = $this->register['checkScope'];
        } else {
            $restore = __CLASS__ . '::defaultScopeCall';
        }
        $originScopeArray = call_user_func($restore, $originScope);

        // 先检测request_uri 在不在protected资源内
        foreach ($protected as $protected_uri => $scope) {
            if (strpos($protected_uri, '/')) {
                if ($protected_uri == $request_uri) {
                    return in_array($protected[$request_uri], $originScopeArray);
                }
            } else {
                if ($protected_uri == substr($request_uri,strpos($request_uri, '/') + 1)) {
                    return in_array($protected[$protected_uri], $originScopeArray);
                }
            }
        }

        return true;
    }

    /**
     * @param $scope
     * @return array
     */
    protected static function defaultScopeCall($scope)
    {
        return array_filter(explode(',', $scope), 'trim');
    }

    protected function getToken()
    {
        $callback = $this->register['getToken'];
        if (!is_null($callback) && is_callable($callback)) {
            return call_user_func($callback, $this->token, $this->tokenType); // @return <Array>$tokenData | false
        } else {
            throw new LogicException("please define callable function (getToken) as preparation.", 2405);
        }
    }

    public function parseRequests(callable $callback)
    {
        call_user_func($callback, $this);
    }

    public function __set($name, $value)
    {
        if (isset($this->$name)) {

        }
        $reflectionProperty = new \ReflectionProperty($this, $name);
        if ($reflectionProperty) {
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($this, $value);
        }
    }


}