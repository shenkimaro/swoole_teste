<?php

class ControlClientes extends Controller {

    const MODULO = 'inicio';

    private $rest;

    public function __construct() {
        parent::__construct();
        ClientesTDG::idValido(Request::getInt('id',0));
        $this->rest = new Restful();
    }

    public function index() {
        $this->rest->printREST(['mensagem'=>'endpoint não encontrado'], Restful::STATUS_BAD_REQUEST);
    }

    public function transacoes() {
        try {
			$json = file_get_contents('php://input');
			$request = json_decode($json);
            $valor = (int) $request->valor;
			$tipo = strtolower($request->tipo);
            if(!in_array($tipo, ['c','d'])){
                throw new Exception('Tipo é inválido');
            }
            $descricao = strtolower($request->descricao);
            if (strlen($descricao) <= 0 || strlen($descricao) > 10) {
				throw new Exception('Descrição é inválido');
            }
			$idCliente = Request::getInt('id',0);
            $cliente = ClientesTDG::change(new Clientes(['saldo'=>$valor, 'id'=>$idCliente]), $tipo,$descricao);
            $this->rest->printREST([
                "limite" => $cliente->getLimite(),
                "saldo" => $cliente->getSaldo(),
            ]);
        } catch (Exception $e) {
            $data['mensagem'] = $e->getMessage();
            $this->rest->printREST($data, 422);
        }
    }
	
	public function extrato() {
        try {
			$movimentacao = MovimentacaoTDG::getExtrato(Request::getInt('id', 0));
			$data = [];
			foreach ($movimentacao as $registro) {
				if(empty($registro['tipo'])){
					continue;
				}
				$data[] = [
					'valor'=>$registro['valor'],
					'tipo'=>$registro['tipo'],
					'descricao'=>$registro['descricao'],
					'realizada_em'=>$registro['data_hora'],
				];
				
			}
            $this->rest->printREST([
                "saldo" => [
					'total' => $movimentacao[0]['saldo'],
					'data_extrato' => date("Y-m-d H:i:s"),
					'limite' => $movimentacao[0]['limite'],
				],
				"ultimas_transacoes"=> $data
					
            ]);
        } catch (Exception $e) {
            $data['mensagem'] = $e->getMessage();
            $this->rest->printREST($data, Restful::STATUS_BAD_REQUEST);
        }
    }
}
