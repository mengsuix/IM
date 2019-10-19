<?php
/**
 * Created by PhpStorm.
 * User: mengsuix
 * Date: 2019-10-14
 * Time: 11:01
 */

namespace im\route;

class round
{
    protected static $lastIndex = 0;

    public static function select(array $list)
    {
        $currentIndex = self::$lastIndex;
        $url = $list[$currentIndex];
        if ($currentIndex + 1 > count($list) - 1) {
            self::$lastIndex = 0;
        } else {
            self::$lastIndex++;
        }
        return $url;
    }
}