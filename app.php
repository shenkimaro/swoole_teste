
<?php

$http = new Swoole\Http\Server('0.0.0.0', 9999);
include './config/requires.php';
include './load.php';

$http->on('start', function ($server) {
	Util::shellDebug([
		'IP' => $server->host, 
		'Port' => $server->port
		],false);
    echo "Servidor Iniciado \n";
});

$http->on('request', function ($request, $response) {
   processaURL($request, $response);
});

$http->start();