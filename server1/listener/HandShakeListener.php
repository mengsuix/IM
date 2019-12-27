<?php
/**
 * Created by PhpStorm.
 * User: mengsuix
 * Date: 2019-10-10
 * Time: 09:03
 */

namespace im\server\listener;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;

class HandShakeListener
{
    public function handle($request, $response, $redis, $table)
    {
        if (!isset($request->header['sec-websocket-protocol'])) {
            $response->end();
            return false;
        }
        //jwt校验
        $flag = 0;
        try {
            $tokenData = JWT::decode($request->header['sec-websocket-protocol'], 'msx123', ['HS256']);
            $redis->hset('im_session', $tokenData->data->uid, json_encode(['service_url' => $tokenData->data->service_url,'fd' => $request->fd, 'name' => $tokenData->data->name]));
            $table->set($request->fd, ['fd' => $request->fd, 'uid' => $tokenData->data->uid]);
        } catch (SignatureInvalidException $e) { //签名错误
            var_dump($e->getMessage());
        } catch (ExpiredException $e) { //token过期
            var_dump($e->getMessage());
        } catch (\Exception $e) { //其他错误
            var_dump($e->getMessage());
        }
        if ($flag > 1)  {
            $response->end();
            return false;
        }

        // websocket握手连接算法验证
        $secWebSocketKey = $request->header['sec-websocket-key'];
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->end();
            return false;
        }
        $key = base64_encode(sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        ));
        $headers = [
            'Upgradee' => 'websocket',
            'Connectionn' => 'Upgrade',
            'Sec-WebSocket-Acceptt' => $key,
            'Sec-WebSocket-Versionn' => '13',
        ];
        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocoll'] = $request->header['sec-websocket-protocol'];
        }
        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }
        $response->status(101);
        $response->end();
    }
}