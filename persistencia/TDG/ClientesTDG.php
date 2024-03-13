<?php

class ClientesTDG {

    public static function idValido(int $id) {
        if ($id < 1 || $id > 5) {
            $rest = new Restful();
            $rest->printREST('Cliente inválido', Restful::STATUS_NAO_ENCONTRADO);
        }
    }

    public static function change(Clientes $cliente, string $tipo, string $descricao): Clientes {
        switch ($tipo) {
            case 'c':
                $acao = '+';
                break;
            case 'd':
                $acao = '-';
                break;
        }
        $tdg = TDG::getInstance();
		$tdg->beginTransaction();
		$valor = $cliente->getSaldo();
        $sql = "update clientes set saldo = saldo $acao {$valor} "
                . "where id = {$cliente->getId()} returning *;";
        $cliente = new Clientes($tdg->genericQuery($sql)[0]);
        if($cliente->getSaldo() < (-1*$cliente->getLimite())){
            $tdg->rollback();
            $rest = new Restful();
            $rest->printREST('O saldo estourou o seu limite', 422);
        }
        $movimentacao = new Movimentacao([]);
        $movimentacao->setValor($valor);
        $movimentacao->setTipo($tipo);
        $movimentacao->setFkClientes($cliente->getId());
        $movimentacao->setDescricao($descricao);
        $movDb = MovimentacaoTDG::inserir($movimentacao);
		if ($movDb == null) {
			$tdg->rollback();
			$tdg = null;
			throw new Exception('Não foi possível inserir a movimentação do extrato');
		}
        $tdg->commit();
		$tdg = null;
		return $cliente;
    }

}
