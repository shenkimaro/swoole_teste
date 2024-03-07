<?php

/**
 * @package Framework
 *
 * @subpackage Debug
 * 
 * @filesource
 */

/**
 * Esta classe realiza servicos de debug
 * 
 * @author Ibanez C. Almeida <ibanez.almeida@gmail.com>
 *
 * @version 2.0
 *
 */
class Debug {

	const DEBUG_FILE = 1;
	const DEBUG_APACHE = 2;
	const DEBUG_REDIS = 3;

	/**
	 * Diz se a classe funciona ou nao
	 * 
	 * @var boolean
	 */
	private $status;

	/**
	 * Contem o caminho da pasta do arquivo de debug
	 * 
	 * @var string
	 */
	private $fileDebug;

	//************************************************************************************************************************\\

	/**
	 * Metodo construtor da classe Debug
	 *
	 * @author Ibanez C Almeida
	 * 
	 */
	function __construct() {
		$status = isset($GLOBALS['configDebug']['status']) ? $GLOBALS['configDebug']['status'] : false;
		$this->setStatusDebug($status);
		$this->fileDebug = "/var/www/logs/debugSys.log";
	}

	//************************************************************************************************************************\\

	/**
	 * Seta o status da classe, ligado ou desligado
	 *
	 * @param boolean $file 0 para desligar a classe debug, 1 para 
	 * liga-la
	 *
	 * @return boolean verdadeiro se o debug funcionou, false em caso 
	 * contrario
	 *
	 * @author Ibanez C Almeida
	 * 
	 */
	public function setStatusDebug($status = 1) {
		$this->status = $status;
	}

	/**
	 * Retorna o tempo inicial da execução
	 * @return float
	 */
	public static function getStartExecutionTime() {
		return time();
	}

	/**
	 * Retorna o tempo decorrido da execução do codigo
	 * @param float $startTime
	 * @return float
	 */
	public static function getElapsedExecutionTime($startTime) {
		$script_end = time();
		$elapsedTime = round($script_end - $startTime, 5);
		return $elapsedTime;
	}

	//************************************************************************************************************************\\

	/**
	 * Seta o caminho para o arquivo de debug
	 *
	 * @param string $file Deve conter o caminho para o arquivo de debug, 
	 * somente a pasta onde deverar ficar
	 *
	 * @author Ibanez C Almeida
	 * 
	 * @version 1.0
	 */
	function setFileDebug($file) {
		$this->fileDebug = $file;
	}

	//************************************************************************************************************************\\

	/**
	 * Escreve a mensagem passada, para fins de acompanhamento da 
	 * codificacao
	 *
	 * @param string $var Deve conter a mensagem que aparecera no 
	 * log
	 *
	 * @param numerico output Não obrigatorio, 0 para tela do browser, 
	 * 1 para escrita em arquivo(previamente setado), 2 para email
	 *
	 * @return boolean verdadeiro se o debug funcionou, false em caso
	 * contrario
	 *
	 * @author Ibanez C Almeida
	 * 
	 */
	public function write($var, $output = 1, $info = '') {
		$message = "/=================================CHAMADA :=================================\ \n";
		$message .= print_r($var, TRUE);
		$message .= "\n \n" . $info;
		$message .= "\n/==================================FIM: ==================================\ \n\n";

		if ($output == 1) {
			$data = date('Y_m_d');
			if (strtoupper(mb_substr(PHP_OS, 0, 3)) === 'WIN') {
				$file = $_SERVER['DOCUMENT_ROOT'];
			}
			$file = $this->fileDebug;
			static::createDir($file);
			$fp = fopen($file, 'a');
			if (!$fp) {
				return;
			}

			fwrite($fp, $message);
			fclose($fp);
		} else if ($output == 0) {
			echo "<pre>";
			print_r($var);
			echo "</pre>";
		} else if ($output == 2) {
//			$this->enviarEmail($message);
		}

		return true;
	}

	private static function createDir(string $file) {
		$directories = explode('/', $file);
		$dir = '';
		for ($index = 0; $index < count($directories) - 1; $index++) {
			if ($directories[$index] == '') {
				continue;
			}
			$dir .= '/' . $directories[$index];
		}
		if (!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
	}

	public static function tail($variavel, $outPutFile = '', $outPut = Debug::DEBUG_FILE) {
		$debug = new Debug();
		if ($outPutFile != '') {
			$debug->setFileDebug($outPutFile);
		}
		$stack = debug_backtrace();
		$call_info = @array_shift($stack);
		$arquivo = $call_info['file'];
		$linha = $call_info['line'];
		$date = date('d-m-Y H:i:s:' . microtime());
		$message = "\n Horario: $date";
		$message .= "\n Arquivo: $arquivo -> linha: ($linha)";
		switch ($outPut) {
			case Debug::DEBUG_FILE:
				$debug->write($variavel, 1, $message);
				break;
			case Debug::DEBUG_APACHE:
				$variavel .= "\n" . $message;
				$debug->writeLogApache($variavel);
				break;
			case Debug::DEBUG_REDIS:
				$variavel .= "\n" . $message;
				$debug->writeLogNoSql($variavel);
				break;

			default:
				break;
		}		
	}

	public function trace(): array {
		return debug_backtrace();
	}

	private function writeLogApache($var) {
		$stderr = fopen('php://stderr', 'w');
		fwrite($stderr, "\n-----\n{$var}\n-----\n");
		fclose($stderr);
	}
	
	private function writeLogNoSql($var) {
		$redis = new Redis();
		$redis->connect('127.0.0.1', 6380);
		$redis->lpush("log_debug_library", $var);
	}

	public function stop() {
		exit();
	}

}
