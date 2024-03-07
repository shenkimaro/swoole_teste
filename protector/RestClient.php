<?php

/**
 * PHP REST Client
 * https://github.com/tcdent/php-restclient
 * (c) 2013 Travis Dent <tcdent@gmail.com>
 */
class RestClientException extends Exception {
	
}

class RestClient implements Iterator, ArrayAccess {

	public $options;
	public $handle; // cURL resource handle.
	// Populated after execution:
	public $response; // Response body.
	public $headers; // Parsed reponse header object.
	public $info; // Response info object.
	public $error; // Response error string.
	public $format;
	public $parameter;
	public $parameterXml;
	public $parameterJson;
	public $timeOut;
	// Populated as-needed.
	public $decoded_response; // Decoded response body.
	private $url;

	public function __construct($options = array()) {
		$default_options = array(
			'headers' => array(),
			'parameters' => array(),
			'curl_options' => array(),
			'user_agent' => "PHP RestClient/0.1.2",
			'base_url' => NULL,
			'format' => NULL,
			'format_regex' => "/(\w+)\/(\w+)(;[.+])?/",
			'decoders' => array(
				'json' => 'json_decode',
				'php' => 'unserialize'
			),
			'username' => NULL,
			'password' => NULL
		);

		$this->options = array_merge($default_options, $options);
		if (array_key_exists('decoders', $options))
			$this->options['decoders'] = array_merge(
					$default_options['decoders'], $options['decoders']);
	}

	public function set_option($key, $value) {
		$this->options[$key] = $value;
	}

	/**
	 * Time in miliseconds
	 * @param int $value
	 */
	public function setTimeOut(int $value) {
		$this->timeOut = $value;
	}

	public function register_decoder($format, $method) {
		// Decoder callbacks must adhere to the following pattern:
		//   array my_decoder(string $data)
		$this->options['decoders'][$format] = $method;
	}

	// Iterable methods:
	public function rewind(): void {
		$this->decode_response();
		reset($this->decoded_response);
	}

	public function current(): mixed {
		return current($this->decoded_response);
	}

	public function key(): mixed {
		return key($this->decoded_response);
	}

	public function next():void {
		next($this->decoded_response);
	}

	public function valid(): bool {
		return is_array($this->decoded_response)
				&& (key($this->decoded_response) !== NULL);
	}

	// ArrayAccess methods:
	public function offsetExists($key): bool {
		$this->decode_response();
		return is_array($this->decoded_response) ?
				isset($this->decoded_response[$key]) : isset($this->decoded_response->{$key});
	}

	public function offsetGet($key): mixed {
		$this->decode_response();
		if (!$this->offsetExists($key))
			return NULL;

		return is_array($this->decoded_response) ?
				$this->decoded_response[$key] : $this->decoded_response->{$key};
	}

	public function offsetSet($key, $value): void {
		throw new RestClientException("Decoded response data is immutable.");
	}

	public function offsetUnset($key): void {
		throw new RestClientException("Decoded response data is immutable.");
	}

	// Request methods:
	public function get($url, $parameters = array(), $headers = array()) {
		return $this->execute($url, 'GET', $parameters, $headers);
	}

	public function post($url, $parameters = array(), $headers = array()) {
		return $this->execute($url, 'POST', $parameters, $headers);
	}

	public function put($url, $parameters = array(), $headers = array()) {
		return $this->execute($url, 'PUT', $parameters, $headers);
	}

	public function delete($url, $parameters = array(), $headers = array()) {
		return $this->execute($url, 'DELETE', $parameters, $headers);
	}

	public function setParameterQuery($parameter) {
		$this->parameter = $parameter;
	}

	public function setFormat($format) {
		$this->format = $format;
	}

	public function setParameterXML($parameterXml) {
		$this->parameterXml = $parameterXml;
	}

	public function setParameterJSON($parameterJson) {
		$this->parameterJson = $parameterJson;
	}

	public function execute($url, $method = 'GET', $parameters = array(), $headers = array()) {
		$client = clone $this;
		$client->url = $url;
		$client->handle = curl_init();
		$curlopt = array(
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_USERAGENT => $client->options['user_agent']
		);

		if ($client->options['username'] && $client->options['password'])
			$curlopt[CURLOPT_USERPWD] = sprintf("%s:%s", $client->options['username'], $client->options['password']);

		if (count($client->options['headers']) || count($headers)) {
			$curlopt[CURLOPT_HTTPHEADER] = array();
			$headers = array_merge($client->options['headers'], $headers);
			foreach ($headers as $key => $value) {
				$curlopt[CURLOPT_HTTPHEADER][] = sprintf("%s:%s", $key, $value);
			}
		}

		if ($this->timeOut > 0) {
			$curlopt[CURLOPT_TIMEOUT_MS] = $this->timeOut;
		}

		$parameters = array_merge($client->options['parameters'], $parameters);
		if ($client->options['format'])
			$client->url .= '.' . $client->options['format'];

		if (in_array(strtoupper($method),['POST','PUT','DELETE'])) {
			$curlopt[CURLOPT_POST] = TRUE;
			$curlopt[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
			if ($this->format == 'xml') {
				$curlopt[CURLOPT_POSTFIELDS] = $client->parameterXml;
			} elseif ($this->format == 'json') {
				$curlopt[CURLOPT_POSTFIELDS] = $client->parameterJson;
			} else {
				if (is_array($this->parameter)) {
					$parameters = array_merge($parameters, $this->parameters);
				}
				$curlopt[CURLOPT_POSTFIELDS] = $client->format_query($parameters);
			}
		} elseif (count($parameters)) {
			$client->url .= strpos($client->url, '?') ? '&' : '?';
			$client->url .= $client->format_query($parameters);
		}

		if ($client->options['base_url']) {
			if ($client->url[0] != '/' || mb_substr($client->options['base_url'], -1) != '/')
				$client->url = '/' . $client->url;
			$client->url = $client->options['base_url'] . $client->url;
		}
		$curlopt[CURLOPT_URL] = $client->url;

		if ($client->options['curl_options']) {
			// array_merge would reset our numeric keys.
			foreach ($client->options['curl_options'] as $key => $value) {
				$curlopt[$key] = $value;
			}
		}
		curl_setopt_array($client->handle, $curlopt);
		$client->parse_response(curl_exec($client->handle));		
		$client->info = (object) curl_getinfo($client->handle);
		$client->error = curl_error($client->handle);

		curl_close($client->handle);				
		return $client;
	}

	public function format_query($parameters, $primary = '=', $secondary = '&') {
		$query = "";
		foreach ($parameters as $key => $value) {
			$pair = array(urlencode($key), urlencode($value));
			$query .= implode($primary, $pair) . $secondary;
		}
		return rtrim($query, $secondary);
	}

	public function parse_response($response) {
		$responseParts = $this->explodeResponseParts($response);
		$this->response = $this->getLastPart($responseParts);
		$last = count($responseParts) - 1;
		$headers = [];
		for ($index = 0; $index < $last; $index++) {
			$headerDetails = $responseParts[$index];
			$lines = explode("\n", $headerDetails);
			foreach ($lines as $line) {
				if (((trim($line) == '') || (strpos(strtolower($line), '100') !== false) && (strpos(strtolower($line), 'continue') !== false))) {
					continue;
				}
				if (strlen(trim($line)) == 0) {
					break;
				}
				if (count(explode(':', $line)) < 2) {
					continue;
				}
				list($key, $value) = explode(':', $line, 2);
				$key = trim(strtolower(str_replace('-', '_', $key)));
				$value = trim($value);
				if (empty($headers[$key])) {
					$headers[$key] = $value;
				} elseif (is_array($headers[$key])) {
					$headers[$key][] = $value;
				} else {
					$headers[$key] = array($headers[$key], $value);
				}
			}
		}
		$this->headers = (object) $headers;
	}

	private function explodeResponseParts($response): array {
			$responseParts = [];
		$responseLines = explode("\n", $response);
		$cont = 0;
		foreach ($responseLines as $line) {
			if (trim($line) == '' && count($responseParts) > 0) {
				++$cont;
			}
			if (isset($responseParts[$cont])) {
				$responseParts[$cont] .= "\n" . $line;
			} else {
				$responseParts[$cont] = $line;
			}
		}
		return $responseParts;
	}

	private function getLastPart(array &$responseParts) {
		$last = count($responseParts) - 1;
		if ($last < 0) {
			return '';
		}
		$responseGeral = isset($responseParts[$last]) ? $responseParts[$last] : '';
		if (trim($responseGeral) != '') {
			return $responseGeral;
		}
		array_pop($responseParts);
		return $this->getLastPart($responseParts);
	}

	public function get_response_format() {
		if (!$this->response)
			throw new RestClientException("A response must exist before it can be decoded.");

		// User-defined format. 
		if (!empty($this->options['format']))
			return $this->options['format'];

		// Extract format from response content-type header. 
		if (!empty($this->headers->content_type))
			if (preg_match($this->options['format_regex'], $this->headers->content_type, $matches))
				return $matches[2];

		throw new RestClientException(
						"Response format could not be determined.");
	}

	public function decode_response() {
		if (empty($this->decoded_response)) {
			$format = $this->get_response_format();
			if (!array_key_exists($format, $this->options['decoders']))
				throw new RestClientException("'$format' is not a supported " .
								"format, register a decoder to handle this response.");

			$this->decoded_response = call_user_func(
					$this->options['decoders'][$format], $this->response);
		}

		return $this->decoded_response;
	}

}
