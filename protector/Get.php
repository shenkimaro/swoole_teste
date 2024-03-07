<?php

class Get extends Request{

	public static function removeKey($key) {
		if (isset($_GET[$key])) {
			unset($_GET[$key]);
		}
	}

	protected static function getKeyValue($key) {
		return isset($_GET[$key]) ? $_GET[$key] : NULL;
	}

}
