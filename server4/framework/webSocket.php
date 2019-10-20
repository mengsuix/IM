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
use Swoole\Table;

class webSocket
{
    protected $redis;
    protected $table;
    protected $ip = [
        'ip' => '192.168.10.23',
        'port' => 9804
    ];

    public function run()
    {
        $this->table = new Table(1024);
        $this->table->column('fd', Table::TYPE_INT);
        $this->table->column('uid', Table::TYPE_INT);
        $this->table->create();
        $server = new \Swoole\WebSocket\Server('0.0.0.0', 9804);
        $server->set([
            'worker_num' => 2
        ]);
        $server->on('start', function () {
            Event::trigger('StartListener', [$this->ip]);
        });
        $server->on('workerStart', function () {
            $this->redis =  new \Redis();
            $this->redis->pconnect('0.0.0.0', 8090);
        });
        $server->on('open', function () {
        });
        $server->on('message', function ($server, $frame) {
//            $responseData =  Route::getInstance()->dispatch('');
            Event::trigger("MessageListener", [$server, $frame, $this->redis, $this->table, $this->ip]);
        });
        $server->on('handshake', function ($request, $response) {
            Event::trigger('HandShakeListener', [$request, $response, $this->redis, $this->table]);
        });
        $server->on('close', function ($server, $fd, $reactorId) {
            Event::trigger('CloseListener', [$server, $fd, $this->redis, $this->table]);
        });
        $server->start();
    }
}