<?php

class MovimentacaoTDG {

    public static function inserir(Movimentacao $movimentacao): ?Movimentacao {
        $tdg = TDG::getInstance();
        return $tdg->insert($movimentacao);
    }
    
    public static function getExtrato(int $idCliente):array {
        $sql = "select *
				from public.clientes c
				left join public.movimentacao m on m.fk_clientes = c.id 
				where c.id = {$idCliente}
				order by m.data_hora desc
				limit 10";
            
         return TDG::getInstance()->genericQuery($sql);   
    }

}
