<?php

class RequestParamns {

	public static function decryptRequest($password) {
		$request = $_REQUEST;
		foreach ($request as $key => $value) {
			if (strpos($key, Crypt::$tag) !== false && strpos($value, Crypt::$tag) !== false) {
				unset($request[$key]);
				$key = str_replace(Crypt::$tag, '', $key);
				$value = str_replace(Crypt::$tag, '', $value);
				$request[Crypt::decryptSimple($password, $key)] = Crypt::decryptSimple($password, $value);				
				continue;
			}
			if (!is_array($value) && strpos($value, Crypt::$tag) !== false) {
				$value = str_replace(Crypt::$tag, '', $value);
				$request[$key] = Crypt::decryptSimple($password, $value);
			}
		}
		$_REQUEST = [];
		$_REQUEST = $request;
	}

	/**
	 * Retorna numerico ou null caso não exista
	 * @param $key
	 * @param $defaultValue Int Se o valor da chave for nulo é retornado o valor default
	 * @return int
	 */
	public static function getInt($key, $defaultValue = NULL) {
		$value = static::getKeyValue($key);
		if ($value != NULL) {
			return (int) $value;
		}
		return $defaultValue;
	}

	/**
	 * Retorna string(texto) ou null caso não exista.
	 * @param $key
	 * @param null $defaultValue
	 * @return string
	 */
	public static function getString($key, $defaultValue = NULL) {
		$value = static::getKeyValue($key);
		if ($value === null && $defaultValue !== null) {
			$value = $defaultValue;
		}
		return $value;
	}

	/**
	 * Retorna o parâmetro com a chave informada, desde que ele seja um array.
	 * Caso o valor da chave esteja vazio ou não seja um array, um array vazio será retornado.
	 * @param $key
	 * @return Array
	 */
	public static function getArray($key) {
		$value = static::getKeyValue($key);
		return ($value && is_array($value)) ? $value : array();
	}

	/**
	 * Verifica se a chave na request corresponde a um valor booleano
	 * e retorna String
	 * @param $key
	 * @param $defaultValue String - Se o valor da chave for nulo é retornado o valor default
	 * @return null|string 'true' ou 'false'
	 */
	public static function getBoolean($key, $defaultValue = NULL) {
		$value = static::getKeyValue($key);

		if ($value != null) {
			return ($value === true || $value === "true" || $value === 1 || $value === "1" || $value === "t" || $value === "on") ? 'true' : 'false';
		} else {
			return $defaultValue;
		}
	}

	/**
	 * Retorna true|false de acordo com a chave na request
	 * @param $key
	 * @param $defaultValue String - Se o valor da chave for nulo é retornado o valor default
	 * @return null|bool 'true' ou 'false'
	 */
	public static function getBool($key, $defaultValue = NULL) {
		$value = static::getKeyValue($key);

		if ($value != null) {
			return ($value === true || $value === "true" || $value === 1 || $value === "1" || $value === "t" || $value === "on") ? true : false;
		}
		return $defaultValue;
	}

	/**
	 * Verifica se a chave na request existe e / ou esta preenchida
	 * @param $key que deve ser passada com Request::getString($key)
	 * @param $message String - Uma mensagem qualquer, caso queira.	 
	 */
	public static function isRequired($key, $message = "") {

		if (($key == NULL) || ($key == "")) {
			throw new Exception($message);
		}
	}

	/**
	 * Remove uma key do REQUEST
	 * @param type $key
	 */
	public static function removeKey($key) {
		if (isset($_REQUEST[$key])) {
			unset($_REQUEST[$key]);
		}
	}

	protected static function getKeyValue($key) {
		return isset($_REQUEST[$key]) ? $_REQUEST[$key] : NULL;
	}

}
