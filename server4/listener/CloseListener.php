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
    public function handle($server, $fd, $redis, $table)
    {
        $uid = $table->get($fd, 'uid');
        $redis->hdel('im_session', $uid);
        $table->del($fd);
    }
}