<?php
/**
 * Created by PhpStorm.
 * User: mengsuix
 * Date: 2019-10-15
 * Time: 09:54
 */

namespace im\server\framework\route;

class Route
{
    protected static $route = [];
    protected static $instance;

    public static function  getInstance()
    {
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function __callStatic($method, $params)
    {
        switch ($method) {
            case 'get':
                self::$route[$method][$params[0]] = $params[1];
                break;
        }
    }

    public function dispatch($request)
    {
        $path = $request->server['path_info'];
        if ($path == '/favicon.ico') {
            return '';
        }
        $requestMethod = $request->server['request_method'];
        switch ($requestMethod) {
            case 'GET':
                if (isset(self::$route['GET'][$path])) {
                    return $this->response(self::$route['GET'][$path]);
                }
                break;
            case 'POST':
                break;
        }
        return '';
    }

    public function response($params)
    {
        if ($params instanceof \Closure) {
            return call_user_func($params);
        } else {
            $paramArr = explode('@', $params);
            $namespace = "im\\app\\controller"; //命名空间
            $class = $namespace . "\\" . $paramArr[0];
            $obj = new $class;
            return call_user_func([$obj, $paramArr[1]]);
        }
    }
}