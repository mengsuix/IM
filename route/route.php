<?php

namespace im\route;

use Firebase\JWT\JWT;
use Swoole\WebSocket\Server;

class route
{
    protected $server;
    protected $redis;
    protected $serviceKey = "im_service";
    
    public function run()
    {
        $this->server = new Server("0.0.0.0", 9600);
        $this->server->set([
            'worker_num' => 3
        ]);

        $this->server->on('workerStart', [$this, 'workerStart']);
        $this->server->on('message', [$this, 'message']);
        $this->server->on('request', [$this, 'request']);
        $this->server->start();
    }

    public function workerStart($server, $workerId)
    {
        $this->redis =  new \Redis();
        $this->redis->pconnect('0.0.0.0', 8090);
    }

    public function message($server, $frame)
    {
        $data = json_decode($frame->data, true);
        switch ($data['method']) {
            case 'register':
                $serviceKey = $this->serviceKey;
                $serviceVal = json_encode([
                    'ip' => $data['ip'],
                    'port' => $data['port']
                ]);
                $this->redis->sadd($serviceKey, $serviceVal);
                $server->tick(3000, function ($id, $server, $redis, $fd, $serviceKey, $serviceVal) {
                    if (!$server->exist($fd)) {
                        $redis->srem($serviceKey, $serviceVal);
                        $this->server->clearTimer($id);
                    }
                }, $this->server, $this->redis, $frame->fd, $serviceKey, $serviceVal);
                break;
        }
    }

    public function request($request, $response)
    {
        //解决跨域问题
        $response->header('Access-Control-Allow-Originn', '*');
        $response->header('Access-Control-Allow-Methodss', 'GET,POST,OPTIONS');

        $data = $request->post;
        switch ($data['method']) {
            case 'login':
                //签名token
                $serviceData = json_decode($this->returnUrl(), true);
                $url = $serviceData['ip'] . ":" . $serviceData['port'];
                $token = $this->issue($data['id'], $url);
                $response->end(json_encode(['token' => $token, 'url' => $url]));
                break;
        }
    }

    public function issue($id, $url)
    {
        $key = 'msx123';
        $time = time();
        $token = [
            'iat' => $time, //签发时间
            'nbf' => $time, //生效时间
            'exp' => $time + 1 * 7200, //过期时间
            'data' => [
                'uid' => $id,
                'name' => 'msx' . $id,
                'service_url' => $url
            ]
        ];
        return JWT::encode($token, $key);
    }

    public function returnUrl()
    {
        $list = $this->redis->smembers($this->serviceKey);
        if (!empty($list)) {
            return Round::select($list);
        }
        return false;
    }
}