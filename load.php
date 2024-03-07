<?php

function processaURL(Swoole\Http\Request $requisicao) {
    $request = $requisicao->server['request_uri'];
    if($request == null){
        $rest = new Restful();
        $rest->printREST('Url inválida', Restful::STATUS_BAD_REQUEST);
    }
	
    $parts = explode('/', substr($request, 1));
    if(count($parts) != 3){
        $rest = new Restful();
        $rest->printREST('Url inválida', Restful::STATUS_BAD_REQUEST);
    }
    $_REQUEST['modulo'] = $parts[0];
    $_REQUEST['id'] = $parts[1];
    $_REQUEST['acao'] = $parts[2];
}
