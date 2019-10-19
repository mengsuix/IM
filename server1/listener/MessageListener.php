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
    public function handle($server, $frame, $redis)
    {
        $requestData = json_decode($frame->data, true);
        switch ($requestData['method']) {
            case 'server_broadcast':
                $this->sendAll($server, $redis, $requestData['msg']);
                break;
            case 'route_broadcast':
                break;
        }
    }

    private function sendAll($server, $redis, $message)
    {
        $uids = $redis->hgetAll('im_session');
        foreach ($uids as $item) {
            $item = json_decode($item, true);
            $server->push($item['fd'], $message);
        }
    }
}