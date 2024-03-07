<?php

use Swoole\Http\Request;
use Swoole\Http\Response;

class Restful {

	const STATUS_OK = 200;
	const STATUS_CRIADO = 201;
	const STATUS_NO_CONTENT = 204;
	const STATUS_BAD_REQUEST = 400;
	const STATUS_SEM_AUTORIZACAO = 401;
	const STATUS_NAO_PERMITIDO = 403;
	const STATUS_NAO_ENCONTRADO = 404;
	const STATUS_METODO_NAO_PERMITIDO = 405;
	const STATUS_CONFLIT = 409;
	const STATUS_ERRO_INTERNO_SERVIDOR = 500;
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_DELETE = 'DELETE';
	const OUTPUTMETHOD_XML = 'xml';
	const OUTPUTMETHOD_JSON = 'json';
	const REQUEST_OPTIONS = 'OPTIONS';

	private $tipo_saida;
	private Request $request;
	private Response $response;

	function __construct(Request $request, Response $response) {
		$this->escolheTipoSaida();
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * Retorna os valores requisitados do cliente
	 * @return array[key] = value
	 */
	public function getREQUEST() {
		foreach ($_REQUEST as $key => $value) {
			$array[$key] = $value;
		}
		foreach ($_SERVER as $key => $value) {
			if (strpos($key, 'HTTP_') === FALSE) {
				continue;
			}
			$key = str_replace('HTTP_', '', $key);
			$array[strtolower($key)] = $value;
		}
		return $array;
	}

	/**
	 * 
	 * @return array
	 */
	public function getRequestHeaders() {
		return apache_request_headers();
	}

	/**
	 *  Retorna o metodo requisitado pelo cliente
	 * @return string
	 */
	public function getMethod() {
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Usada para converter ISO para UTF
	 * @param string $item
	 */
	public static function formatUTF8(&$item) {
		if (!is_numeric($item)) {
			$item = utf8_encode($item);
		}
	}

	private function addCorsHeaders() {
		$this->response->header('Access-Control-Allow-Origin', '*');
		$this->response->header('Access-Control-Allow-Credentials', 'true');
		$this->response->header('Access-Control-Allow-Headers', 'authorization');
	}

	private function returnHeadersWhenOptionsMethod() {
		if ($this->getRequestMethod() == self::REQUEST_OPTIONS) {
			$this->response->header("HTTP/1.1 " . Restful::STATUS_OK . " " . $this->requestStatus(Restful::STATUS_OK), '');
			$this->response->end();
		}
	}

	private function allowedOrigins() {
		return true;
	}

	/**
	 *
	 * @param array $data
	 * @param integer $status
	 */
	public function printREST($data, $status = Restful::STATUS_OK) {
		$this->response->header('Access-Control-Allow-Methods', '*');
		if ($this->allowedOrigins()) {
			$this->addCorsHeaders();
			$this->returnHeadersWhenOptionsMethod();
		}
		$this->response->header('Access-Control-Allow-Methods', '*');
		$this->response->header("HTTP/1.1 " . $status . " " . $this->requestStatus($status), '');

		if ($this->tipo_saida == 'json') {
			$this->response->header('Content-Type', "application/{$this->tipo_saida}");
			$json = json_encode($data);
			$this->response->end($json);
		}

		if ($this->tipo_saida == 'xml') {
			$this->response->header('Content-Type', "text/{$this->tipo_saida};charset=utf-8");
			$xml = $this->xml_encode($data);
			$this->response->end($xml);
		}
	}

	public function setTipoSaida($tipoSaida) {
		$this->tipo_saida = $tipoSaida;
	}

	private function xml_encode($data) {
		$xml = '<?xml version="1.0" encoding="UTF-8"?>';
		if (isset($data[0])) {
			$xml .= '<registros>';
		}
		foreach ($data as $key => $value) {
			if (is_string($value)) {
				$xml .= "<$key>$value</$key>";
			} else if (is_array($value)) {
				$xml .= "<registro>";
				foreach ($value as $key1 => $value1) {
					if (is_string($value1)) {
						$xml .= "<$key1>$value1</$key1>";
					} elseif (is_array($value1)) {
						$xml .= "<$key1>";
						foreach ($value1 as $key2 => $value2) {
							$xml .= "<$key2>$value2</$key2>";
						}
						$xml .= "</$key1>";
					}
				}
				$xml .= "</registro>";
			}
		}
		if (isset($data[0])) {
			$xml .= '</registros>';
		}
		return $xml;
	}

	private function requestStatus($code) {
		$status = array(
			self::STATUS_OK => 'OK',
			self::STATUS_CRIADO => 'Criado',
			self::STATUS_NO_CONTENT => 'No Content',
			self::STATUS_BAD_REQUEST => 'Bad Request',
			self::STATUS_SEM_AUTORIZACAO => 'Sem Autorizacao',
			self::STATUS_NAO_PERMITIDO => 'Nao Permitido',
			self::STATUS_NAO_ENCONTRADO => 'Nao Encontrado',
			self::STATUS_METODO_NAO_PERMITIDO => 'Metodo nao permitido',
			self::STATUS_ERRO_INTERNO_SERVIDOR => 'Erro Interno do Servidor',
		);
		return (isset($status[$code])) ? $status[$code] : $status[500];
	}

	private function getRequestMethod() {
		return $this->request->server['request_method'] ?? 'GET';
	}

	public function escolheTipoSaida() {
		if (!isset($_SERVER['CONTENT_TYPE']) || $_SERVER['CONTENT_TYPE'] == '') {
			$this->setTipoSaida('json');
			return;
		}
		$content = $_SERVER['CONTENT_TYPE'];
		if (!(strpos($content, self::OUTPUTMETHOD_XML) === FALSE)) {
			$this->setTipoSaida('xml');
		} else if (strpos($content, self::OUTPUTMETHOD_JSON)) {
			$this->setTipoSaida('json');
		} else {
			$this->setTipoSaida('json');
		}
	}

}
