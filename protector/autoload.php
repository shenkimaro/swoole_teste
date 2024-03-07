<?php

require_once __DIR__.'/AutoLoader.php5';
AutoLoader::init();

AutoLoader::registerForNamespace(getCallerDirectory(), getFullCallerDirectory());

function getCallerDirectory() {
	$line = getCallerFile();
	$fileParts = explode(DIRECTORY_SEPARATOR, $line['file']);
	$last = count($fileParts) - 1;
	return $fileParts[$last - 1];
}

function getFullCallerDirectory() {
	$last = getCallerFile();
	return dirname($last['file']);
}

function getCallerFile() {
	$backTrace = debug_backtrace();
	$line = '';
	foreach ($backTrace as $trace) {
		$args = $trace['args'] ?? [];
		foreach ($args as $value) {
			if (strpos($value, 'autoload')) {
				return $trace;
			}
		}
	}
	return $line;
}
