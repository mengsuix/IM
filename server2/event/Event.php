<?php
/**
 * Created by PhpStorm.
 * User: mengsuix
 * Date: 2019-10-10
 * Time: 09:03
 */

namespace im\server\event;

class Event
{
    protected static $events = [];

    public static function register($event, $callback)
    {
        $event = strtolower($event);
        self::$events[$event]['callback'] = $callback;
    }

    public static function trigger($event, $params = [])
    {
        $event = strtolower($event);
        if (isset(self::$events[$event])) {
            call_user_func(self::$events[$event]['callback'], ...$params);
        }
    }
}