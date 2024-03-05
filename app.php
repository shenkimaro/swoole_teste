#!/usr/bin/env php
<?php

$http = new Swoole\Http\Server('0.0.0.0', 9501);

$http->on('request', function ($request, $response) {
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('<h1>Hello Swoole Teste. #' . rand(1000, 9999) . '</h1>');
});

$http->on($event_name, $callback);

$http->start();
