<?php

/**
 * @schemaName public
 * @tableName clientes
 * @pkName 
 */
class Clientes extends DTO {

	// Chave primária
	private $id;

	// Campos da tabela
	private $limite;
	private $saldo;

	/**
	 * Acessor - chave primaria
	 * @tableField id
	 * @return string
	 */
	public function getId() {
            return $this->id;
	}

	/**
	 * Mutador - chave primaria
	 * @tableField id
	 * @param string $id
	 */
	public function setId($id) {
            $this->id = $id;
	}

	/**
	 * Acessor
	 * @tableField limite
	 * @return string
	 */
	public function getLimite() {
		return $this->limite;
	}

	/**
	 * Mutador
	 * @tableField limite
	 * @param string $limite
	 */
	public function setLimite($limite) {
		$this->limite = $limite;
	}

	/**
	 * Acessor
	 * @tableField saldo
	 * @return string
	 */
	public function getSaldo() {
		return $this->saldo;
	}

	/**
	 * Mutador
	 * @tableField saldo
	 * @param string $saldo
	 */
	public function setSaldo($saldo) {
		$this->saldo = $saldo;
	}

}
