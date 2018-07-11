<?php

namespace OAuth;

use Exception;

defined('DS') or define('DS', DIRECTORY_SEPARATOR);
class ClassAutoloader
{

    public static function loadClasses($className)
    {
        $path_arr = array_map(function ($var) {
            return ucfirst($var);
        }, explode('\\', $className));
        
        array_shift($path_arr);

        require_once __DIR__ . DS . join(DS, $path_arr) . '.php';
    }

    public static function autoloader()
    {
        spl_autoload_register(function ($class) {
            if (strpos($class, __NAMESPACE__ . "\\") !== false) {
                if (!class_exists($class)) {
                    self::loadClasses($class);
                } else {
                    throw new Exception($class . ' Not Found.');
                }
            }
        });

    }
}

ClassAutoloader::autoloader();


