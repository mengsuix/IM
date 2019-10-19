<?php
/**
 * Created by PhpStorm.
 * User: mengsuix
 * Date: 2019-10-10
 * Time: 08:40
 */

namespace im\server\framework;

use im\server\event\Event;
use im\server\framework\route\Route;

class webSocket
{
    protected $redis;

    public function run()
    {
        $server = new \Swoole\WebSocket\Server('0.0.0.0', 9803);
        $server->set([
            'worker_num' => 2
        ]);
        $server->on('start', function () {
            Event::trigger('StartListener');
        });
        $server->on('workerStart', function () {
            $this->redis =  new \Redis();
            $this->redis->pconnect('0.0.0.0', 8090);
        });
        $server->on('open', function () {
        });
        $server->on('message', function () {
            $responseData =  Route::getInstance()->dispatch('');
        });
        $server->on('handshake', function ($request, $response) {
            Event::trigger('HandShakeListener', [$request, $response, $this->redis]);
        });
        $server->on('close', function ($server, $fd, $reactorId) {
            Event::trigger('CloseListener', [$server, $fd, $this->redis]);
        });
        $server->start();
    }
}