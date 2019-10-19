<?php
include __DIR__ . "/vendor/autoload.php";
include __DIR__ . "/route.php";
include __DIR__ . "/round.php";
$route = new \im\route\route();
$route->run();