<?php
/**
 * Created by PhpStorm.
 * User: mengsuix
 * Date: 2019-10-10
 * Time: 09:03
 */

namespace im\server\listener;

use Swoole\Coroutine\Http\Client;

class StartListener
{
    public function handle()
    {
        var_dump("startListenter连接到route");
        go(function () {
            $cli = new Client("0.0.0.0", 9600);
            $result = $cli->upgrade("/");
            if ($result) {
                $data=[
                    'method'=>'register', //方法
                    'serviceName'=>'IM2',
                    'ip'=>'0.0.0.0',
                    'port'=>9802
                ];
                $cli->push(json_encode($data));
                //心跳处理
                swoole_timer_tick(3000, function () use ($cli) {
                    if ($cli->errCode == 0) {
                        $cli->push('', WEBSOCKET_OPCODE_PING);
                    }
                });
            }
        });
    }
}