<?php
/**
 * Created by PhpStorm.
 * Date: 2018/2/2
 * Time: 14:56
 */

namespace OAuth\Server\Exception;

use LogicException;

abstract class ResponseExceptionAbstract extends LogicException implements OAuthExceptionInterface
{
    protected $errDescription;

    public function __construct($message = "", $code = 0, \Throwable $previous = null)
    {
        $this->errDescription = $message;
    }

    public function getErrorDescription()
    {
        return $this->errDescription;
    }

    public function getResponseBody()
    {
        return [
            'error' => $this->getMessage(),
            'error_description' => $this->getErrorDescription(),
            'error_code' => $this->getCode(),
        ];
    }
}