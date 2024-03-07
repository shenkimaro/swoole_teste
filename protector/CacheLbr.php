<?php

/**
 * @author ibanez
 */
class CacheLbr {

	private $pathFolder;
	private $hashAlgorithm;
	private $rootName = '_lbr_';

	/**
	 * Tempo em segundos para manter um arquivo, 5 minutos
	 */
	const _TIME_DEFAULT = 300;

	/**
	 * Tempo em segundos para manter um arquivo, 1 minuto
	 */
	const _TIME_SHORT = 60;

	/**
	 * Tempo em segundos para manter um arquivo, 10 minutos
	 */
	const _TIME_LONG = 600;

	/**
	 * 30 dias de cache, use com cuidado
	 */
	const _TIME_VERY_LONG = 2592000;

	/**
	 * 1 dia de cache, use com cuidado
	 */
	const _TIME_DAY = 86400;

	private $fileLifeTime;
    
    private $cacheEnabled = false;

	public function __construct() {
		$this->setPathFolder('');
		$this->setHashKey(Crypt::HASH_MD5);
		if (!defined('_LIBRARY_CACHE_FOLDER')) {
			$this->localConfiguration();
			return;
		}
		$this->cacheEnabled = true;
		if(defined('_LIBRARY_CACHE_ENABLED')){
			$this->cacheEnabled = _LIBRARY_CACHE_ENABLED;
		}
		if (defined('_LIBRARY_CACHE_HASH_KEY')) {
			$this->setHashKey(_LIBRARY_CACHE_HASH_KEY);
		}
		$this->setPathBase(str_replace('//', '/', _LIBRARY_CACHE_FOLDER));
	}

	/**
	 * @deprecated 
	 * @see \CacheLbr::enableCache()
	 */
	public function enableLog() {
		$this->cacheEnabled = true;
	}

	/**
	 * @deprecated 
	 * @see disableCache()
	 */
	public function disableLog() {
		$this->cacheEnabled = false;
	}

	public function enableCache() {
		$this->cacheEnabled = true;
	}

	public function disableCache() {
		$this->cacheEnabled = false;
	}

	private function localConfiguration() {
		if(Util::isLocalIp()){
			throw new Exception('Faltando configuração para constante _LIBRARY_CACHE_FOLDER');
		}
	}

	public function setPathBase(string $pathFolder) {
		$this->pathFolder = $pathFolder;
	}

	public function setHashKey(string $hashAlgorithm) {
		$this->hashAlgorithm = $hashAlgorithm;
	}

	public function setPathFolder(string $pathFolder) {
		$this->pathFolder .= $pathFolder;
	}

	public function add(string $key, $value, $folder = '') {
		if (!$this->cacheEnabled) {
			return;
		}
		if (!is_dir($this->pathFolder)) {
			mkdir($this->pathFolder);
		}
		if (!is_dir($this->pathFolder . '/' . $folder . '/')) {
			mkdir($this->pathFolder . '/' . $folder . '/');
		}
		$key = $this->generateHashKey($key);
		$fp = fopen($this->pathFolder . '/' . $folder . '/' . $key, 'a');
		if (!$fp) {
			return;
		}
		ftruncate($fp, 0);
		fwrite($fp, serialize($value));
		fclose($fp);
	}

	public function get($key, $lifeTime, $folder = '') {
        if (!$this->cacheEnabled) {
			return;
		}
		$this->fileLifeTime = $lifeTime;
		$this->removeOldFiles($folder);
		$key = $this->generateHashKey($key);
		$file = str_replace('//', '/', $this->pathFolder . '/' . $folder . '/' . $key);
		if (is_file($file)) {
			return unserialize(file_get_contents($file));
		}
		return null;
	}

	private function generateHashKey($key) {
		return $this->rootName . Crypt::hash($key, $this->hashAlgorithm);
	}

	public function removeFolder($folder) {
		if (!$this->cacheEnabled) {
			return;
		}
		if(!is_dir($this->pathFolder . $folder)){
			return;
		}
		$this->clearDir($this->pathFolder . $folder);
	}
	
	private function clearDir($folder) {
		if (!$this->validateFolder($folder)) {
			return;
		}
		$files = glob($folder . '/*'); // get all file names
		foreach ($files as $file) { // iterate files
			if (in_array($file, ['.', '..'])) {
				continue;
			}
			if (is_file($file)) {
				unlink($file); // delete file
			} else if (is_dir($file)) {
				$this->clearDir($file);
				rmdir($file);
			}
		}
	}

	/**
	 * É necessario ter pelo menos 3 niveis de pasta
	 * @param string $folder
	 * @return bool
	 */
	private function validateFolder(string $folder): bool {
		$folderArray = explode('/', $folder);
		$cont = 0;
		foreach ($folderArray as $value) {
			if(trim($value) == ''){
				continue;
			}
			++$cont;
		}
		return ($cont>2);
	}
	
	/**
	 * Usada para pagar todo conteudo dentro da constant 
	 * _LIBRARY_CACHE_FOLDER
	 */
	public function clearApplicationFolder() {
		$this->removeFolder('');
	}

	public function remove($key) {
        if (!$this->cacheEnabled) {
			return;
		}
		$key = $this->generateHashKey($key);
		if (is_file($this->pathFolder . '/' . $key)) {
			unlink($this->pathFolder . '/' . $key);
		}
	}

	private function removeOldFiles($subFolder = '') {
        if (!$this->cacheEnabled) {
			return;
		}
		$files = glob($this->pathFolder . "/$subFolder" . '/*'); // get all file names
		$lifeTimeFile = $this->fileLifeTime > (self::_TIME_LONG) ? $this->fileLifeTime : (self::_TIME_LONG);
		foreach ($files as $file) { // iterate files			
			if (is_file($file) && $this->getFileTime($file) > $lifeTimeFile) {
				unlink($file); // delete file
			}
		}
	}

	private function getFileTime(string $file): ?int {
        if (!$this->cacheEnabled) {
			return null;
		}
		if (is_file($file)) {
			$lastModification = filemtime($file);
			return date("YmdHis") - date("YmdHis", $lastModification);
		}
		return 0;
	}

}
