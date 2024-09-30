<?php

namespace Boolval;

class Route
{
    /**
     *
     */
    public static $matches = [];

    /**
     *
     */
    public static $segments = [];

    /**
     *
     */
    public static $uri = null;

    /**
     *
     */
    public static $regex = [
        ':number' => '([0-9]+)',
        ':string' => '([A-Za-z]+)',
        ':slug'   => '([A-Za-z0-9-_]+)',
        ':any'    => '(.+)',
    ];

    /**
     * @return String '/' || '/shop/'
     */
    public static function base() : String
    {
        return str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    }

    /**
     * @return String 'en/user/edit/3'
     */
    public static function path() : String
    {
        return substr(explode('?', $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'])[0], strlen(self::base()));
    }

    /**
     * @return Array
     *
     * Array
     * (
     *     [0] => en
     *     [1] => user
     *     [2] => edit
     *     [3] => 3
     * )
     */
    public static function segments() : Array
    {
        return explode('/', self::path());
    }

    /**
     *
     */
    public static function method($method)
    {
        if ($_SERVER['REQUEST_METHOD'] == $method) {
            return new Self;
        }

        return self::escape();
    }

    /**
     *
     */
    public static function uri(String $string)
    {
        if (self::$uri === null) {
            self::$uri = self::path();
        }

        if (preg_match('~^' . strtr($string, self::$regex) . '$~ixs', self::$uri, self::$matches)) {
            array_shift(self::$matches);
            return new Self;
        }

        return self::escape();
    }

    /**
     *
     */
    public static function segment(String $string)
    {
        if (empty(self::$segments)) {
            self::$segments = self::segments();
        }

        if (preg_match('~^' . strtr($string, self::$regex) . '$~ixs', array_shift(self::$segments), self::$matches)) {
            array_shift(self::$matches);
            self::$uri = implode('/', self::$segments);
            return new Self;
        }

        self::$segments = self::segments();
        self::$uri = self::path();

        return self::escape();
    }

    /**
     *
     */
    public static function middleware($action)
    {
        if (call_user_func_array($action, self::$matches)) {
            return new Self;
        }

        return self::escape();
    }

    /**
     *
     */
    public static function group($action)
    {
        call_user_func_array($action, self::$matches);
    }

    /**
     *
     */
    public static function call($action)
    {
        call_user_func_array($action, self::$matches);

        die;
    }

    /**
     *
     */
    public static function include(String $string)
    {
        include dirname(dirname(dirname(__DIR__))) . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $string) . '.php';

        die;
    }

    /**
     *
     */
    public static function redirect(String $url = '', Int $status = 302)
    {
        header('Location: ' . self::base() . $url, true, $status);

        die;
    }

    /**
     *
     */
    public static function escape()
    {
        return new class
        {
            public function __call($name, $arguments)
            {
                return $this;
            }
        };
    }
}
