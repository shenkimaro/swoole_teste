<?php
namespace library\ueg;

class Header {

	private const Authorization = 'Authorization';

	public static function getAuthorizationBearer() {
		$return = explode(' ', \Conversor::insideTrim(self::get(self::Authorization))??'');
		if(count($return)<2){
			return null;
		}
		return $return[1];
	}
	
	public static function getAuthorization() {
		return self::get(self::Authorization);
	}

	private static function get($key) {
		header("Access-Control-Allow-Origin: *");
		header('Access-Control-Allow-Credentials: true');
		header("Access-Control-Allow-Methods: *");
		header('Access-Control-Allow-Headers: authorization');
		if(!function_exists('apache_request_headers')){
			return null;
		}
		$headers = apache_request_headers();
		return isset($headers[$key]) ? $headers[$key] : NULL;
	}

}
