<?php

/**
 * @schemaName public
 * @tableName movimentacao
 * @pkName id
 */
class Movimentacao extends DTO {

	// Chave primária
	private $id;

	// Campos da tabela
	private $fk_clientes;
	private $valor;
	private $tipo;
	private $descricao;
	private $data_hora;

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
	 * @tableField fk_clientes
	 * @return string
	 */
	public function getFkClientes() {
		return $this->fk_clientes;
	}

	/**
	 * Mutador
	 * @tableField fk_clientes
	 * @param string $fk_clientes
	 */
	public function setFkClientes($fk_clientes) {
		$this->fk_clientes = $fk_clientes;
	}

	/**
	 * Acessor
	 * @tableField valor
	 * @return string
	 */
	public function getValor() {
		return $this->valor;
	}

	/**
	 * Mutador
	 * @tableField valor
	 * @param string $valor
	 */
	public function setValor($valor) {
		$this->valor = $valor;
	}

	/**
	 * Acessor
	 * @tableField tipo
	 * @return string
	 */
	public function getTipo() {
		return $this->tipo;
	}

	/**
	 * Mutador
	 * @tableField tipo
	 * @param string $tipo
	 */
	public function setTipo($tipo) {
		$this->tipo = $tipo;
	}

	/**
	 * Acessor
	 * @tableField descricao
	 * @return string
	 */
	public function getDescricao() {
		return $this->descricao;
	}

	/**
	 * Mutador
	 * @tableField descricao
	 * @param string $descricao
	 */
	public function setDescricao($descricao) {
		$this->descricao = $descricao;
	}

	/**
	 * Acessor
	 * @tableField data_hora
	 * @return string
	 */
	public function getDataHora() {
		return $this->data_hora;
	}

	/**
	 * Mutador
	 * @tableField data_hora
	 * @param string $data_hora
	 */
	public function setDataHora($data_hora) {
		$this->data_hora = $data_hora;
	}

}
