<?php

namespace Boolval\Routing;

class Route
{
    /**
     * ...
     * 
     * @var array
     */
    public static $matches = [];

    /**
     * ...
     * 
     * @var array
     */
    public static $segments = [];

    /**
     * ...
     * 
     * @var string
     */
    public static $uri = null;

    /**
     * ...
     * 
     * @var string
     */
    public static $viewPath = 'view';

    /**
     * ...
     * 
     * @var array
     */
    public static $regex = [
        ':number' => '([0-9]+)',
        ':string' => '([A-Za-z]+)',
        ':slug'   => '([A-Za-z0-9-_]+)',
        ':any'    => '(.+)',
    ];

    /**
     * ...
     * 
     * @return string '/' || '/shop/'
     */
    public static function base() : string
    {
        return str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    }

    /**
     * ...
     * 
     * @return string 'en/user/edit/3'
     */
    public static function path() : string
    {
        return substr(explode('?', $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'])[0], strlen(self::base()));
    }

    /**
     * ...
     *
     * @return array
     *
     * array
     * (
     *     [0] => en
     *     [1] => user
     *     [2] => edit
     *     [3] => 3
     * )
     */
    public static function segments() : array
    {
        return explode('/', self::path());
    }

    /**
     * ...
     *
     * @param  string $method
     * @return object
     */
    public static function method(string $method) : object
    {
        if ($_SERVER['REQUEST_METHOD'] == $method) {
            return new self;
        }

        return self::escape();
    }

    /**
     * ...
     *
     * @param  string $string
     * @return object
     */
    public static function uri(string $string) : object
    {
        if (self::$uri === null) {
            self::$uri = self::path();
        }

        if (preg_match('~^' . strtr($string, self::$regex) . '$~ixs', self::$uri, self::$matches)) {
            array_shift(self::$matches);
            return new self;
        }

        return self::escape();
    }

    /**
     * ...
     *
     * @param  string $string
     * @return object
     */
    public static function segment(string $string)
    {
        if (empty(self::$segments)) {
            self::$segments = self::segments();
        }

        if (preg_match('~^' . strtr($string, self::$regex) . '$~ixs', array_shift(self::$segments), self::$matches)) {
            array_shift(self::$matches);
            self::$uri = implode('/', self::$segments);
            return new self;
        }

        self::$segments = self::segments();
        self::$uri = self::path();

        return self::escape();
    }

    /**
     * ...
     *
     * @param  string $action
     * @return object
     */
    public static function middleware(object $action)
    {
        if (self::callUserFuncArray($action)) {
            return new self;
        }

        return self::escape();
    }

    /**
     * ...
     *
     * @param  string $action
     * @return object
     */
    public static function group($action)
    {
        self::callUserFuncArray($action);
    }

    /**
     * ...
     *
     * @param  string $action
     * @return object
     */
    public static function call($action)
    {
        self::callUserFuncArray($action);

        die;
    }

    /**
     * ...
     *
     * @param  string $action
     * @return object
     */
    public static function callUserFuncArray($action)
    {
        switch (gettype($action)) {
            case 'array':
                return call_user_func_array([new $action[0], $action[1]], self::$matches);
                break;
            case 'object':
                return call_user_func_array($action, self::$matches);
                break;
        }
    }

    /**
     * ...
     *
     * @param  string $viewPath
     * @return void
     */
    public static function setViewPath(string $viewPath)
    {
        self::$viewPath = $viewPath;
    }

    /**
     * ...
     *
     * @param  string $string
     * @return object
     */
    public static function view(string $string)
    {
        include dirname(dirname(dirname(__DIR__))) . 
            DIRECTORY_SEPARATOR . self::$viewPath . 
            DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $string) . '.php';

        die;
    }

    /**
     * ...
     *
     * @param  string $string
     * @return object
     */
    public static function redirect(string $url = '', int $status = 302)
    {
        header('Location: ' . self::base() . $url, true, $status);

        die;
    }

    /**
     * ...
     *
     * @param  string $string
     * @return object
     */
    public static function escape()
    {
        return new class
        {
            public function __call($name, $arguments)
            {
                return new self;
            }
        };
    }
}
