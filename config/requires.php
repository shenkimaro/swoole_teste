<?php

$pastaConf = __DIR__.'/';
define('_PATH_APP', str_replace('/config', '', __DIR__));
include_once("$pastaConf/load.php");
include_once("$pastaConf/config.php");
include_once("$pastaConf/config_pastas.php");
include_once("$pastaConf/config_debug.php");
include_once("$pastaConf/config_banco.php");
include_once("$pastaConf/config_log.php");





