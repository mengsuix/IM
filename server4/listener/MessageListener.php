<?php
/**
 * Created by PhpStorm.
 * User: mengsuix
 * Date: 2019-10-10
 * Time: 09:03
 */

namespace im\server\listener;

class MessageListener
{
    public function handle($server, $frame, $redis, $table, $ip)
    {
        $requestData = json_decode($frame->data, true);
        switch ($requestData['method']) {
            case 'server_broadcast':
                $service = $redis->smembers('im_service');
                foreach ($service as $key => $value) {
                    $value = json_decode($value, true);
                    if ($value['ip'] . ":" . $value['port'] == $ip['ip'] . ":" . $ip['port']) {
                        unset($service[$key]);
                    }
                }
                $routeData = [
                    'method' => 'route_broadcast',
                    'target_server' => $service
                ];
                go(function () use ($routeData) {
                    $cli = new \Swoole\Coroutine\Http\Client("0.0.0.0", 9600);
                    $result = $cli->upgrade("/");
                    if ($result) {
                        $cli->push(json_encode($routeData));
                        $cli->close();
                    }
                });
                $this->sendAll($server, $frame, $table, $requestData['msg']);
                break;
            case 'route_broadcast':
                $this->sendAll($server, $frame, $table, $requestData['msg']);
                break;
        }
    }

    private function sendAll($server, $frame, $table, $message)
    {
        foreach ($table as $key => $value) {
            if ($value['fd'] == $frame->fd) {
                continue;
            }
            $server->push($value['fd'], $message);
        }
    }
}