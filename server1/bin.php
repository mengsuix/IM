<?php
define('ROOT_PATH', __DIR__);
define('FRAME_PATH',ROOT_PATH.'/framework');
define('LISTENER_PATH',ROOT_PATH.'/listener');
define('EVENT_PATH',ROOT_PATH.'/event');
define('ROUTE_PATH',ROOT_PATH.'/route');
include ROOT_PATH . "/vendor/autoload.php";
include FRAME_PATH . "/Http.php";
include FRAME_PATH . "/webSocket.php";
include FRAME_PATH . "/route/Route.php";
include ROUTE_PATH . "/index.php";
//收集事件
include EVENT_PATH . "/Event.php";
$files = glob(LISTENER_PATH . "/*.php");

foreach ($files as $filename) {
    include $filename;
    $className = pathinfo($filename)['filename'];
    $class = "im\\server\\listener\\" . $className;
    $obj = new $class;
    \im\server\event\Event::register($className, [$obj, 'handle']);
}

try{
    switch ($argv[1]){
        case 'start':
            $http_server = new \im\server\framework\Http();
            $http_server->run();
            break;
        case 'ws:start':
            $ws = new \im\server\framework\webSocket();
            $ws->run();
            break;
    }
}catch (\Exception $e){
    echo '异常'.$e->getMessage().PHP_EOL;
}catch (\Throwable $t){
    echo '错误'.$t->getMessage().PHP_EOL;
}
