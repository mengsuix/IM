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
            //服务广播，将需要广播的信息通过路由服务器向其他服务器通知进行广播，只有本机服务对本机上的fd先进行通知
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
                    'target_server' => $service,
                    'msg' => $requestData['msg']
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
            //来自于路由服务器的通知广播信息
            case 'route_broadcast':
                $this->sendAll($server, $frame, $table, $requestData['msg']);
                break;
            //用户单对单通信
            case 'notice' :
                $uid = $requestData['uid'];
                $session = $redis->hget($uid);
                if (empty($session)) {
                    $server->push($frame->fd, "该用户不在线");
                    break;
                }
                $session = json_decode($session);
                if ($session['service_url'] == $ip['ip'] . ":" . $ip['port']) {
                    $server->push($session['fd'], $requestData['msg']);
                } else {
                    $service = explode(':', $session['service_url']);
                    go(function () use ($frame, $service) {
                        $cli = new \Swoole\Coroutine\Http\Client($service[0], $service[1]);
                        $result = $cli->upgrade("/");
                        if ($result) {
                            $cli->push($frame->data);
                            $cli->close();
                        }
                    });
                }
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