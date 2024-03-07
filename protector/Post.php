<?php

class Post extends Request{

	public static function removeKey($key) {
		if (isset($_POST[$key])) {
			unset($_POST[$key]);
		}
	}

	protected static function getKeyValue($key) {
		return isset($_POST[$key]) ? $_POST[$key] : NULL;
	}

}
