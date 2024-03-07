<?php
$appPath = _PATH_APP;
$loader['locais'][] = '';
$loader['locais'][] = $appPath.'.controle';
$loader['locais'][] = $appPath.'.service';
$loader['locais'][] = $appPath.'.persistencia';
$loader['locais'][] = $appPath.'.persistencia.DTO';
$loader['locais'][] = $appPath.'.persistencia.TDG';

include(__DIR__."/../protector/autoload.php");
