<?php
/**
 * Created by PhpStorm.
 * User: mengsuix
 * Date: 2019-10-10
 * Time: 09:03
 */

namespace im\server\listener;

class CloseListener
{
    public function handle($server, $fd, $redis)
    {
        $fdInfo = $redis->hget('im_fds', $fd);
        $fdInfo = json_decode($fdInfo, true);
        $redis->hdel('im_session', $fdInfo['uid']);
        $redis->hdel('im_fds', $fd);
    }
}