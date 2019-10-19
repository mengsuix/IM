<?php
/**
 * Created by PhpStorm.
 * User: mengsuix
 * Date: 2019-10-10
 * Time: 08:40
 */

namespace im\server\framework;

use im\server\event\Event;

class webSocket
{
    public function run()
    {
        $server = new \Swoole\WebSocket\Server('0.0.0.0', 9802);
        $server->set([
            'worker_num' => 2
        ]);
        $server->on('start', function () {
            Event::trigger('StartListener');
        });
        $server->on('message', function () {
        });
        $server->start();
    }
}