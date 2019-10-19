<?php

namespace im\server\framework;

use im\server\event\Event;
use Swoole\Http\Server;

class Http
{
    public function run()
    {
        $server = new Server('0.0.0.0', 9801);
        $server->set([
            'worker_num' => 2
        ]);
        $server->on('start', function () {
            Event::trigger('StartListener');
        });
        $server->on('request', function () {
            Event::trigger('RequestListener');
        });
        $server->start();
    }
}