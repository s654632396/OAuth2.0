<?php
/**
 * Created by PhpStorm.
 * Date: 2018/2/2
 * Time: 15:02
 */

namespace OAuth\Server\Exception;


class InvalidGrantException extends ResponseExceptionAbstract
{
    public function getAssertStatusCode()
    {
        return 401;
    }

    public function getErrorUri()
    {
        return '#page-45';
    }

    public function getErrorMessage()
    {
        return self::INVALID_GRANT_MESSAGE;
    }
}