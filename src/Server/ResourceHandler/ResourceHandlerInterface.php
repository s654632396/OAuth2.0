<?php
/**
 * Created by PhpStorm.
 * Date: 2018/2/1
 * Time: 17:49
 */

namespace OAuth\Server\ResourceHandler;

use OAuth\Request;

interface ResourceHandlerInterface
{

    public function __construct(Request $request);

    public function processResource(array $protected);

    public function registerCallback($name, callable $callable);

    public function checkScopeDefault($request_uri, array $protected, $originScope);
}