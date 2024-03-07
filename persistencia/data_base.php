<?php
?>
--DROP TABLE public.clientes;

CREATE TABLE public.clientes (
	id int2 not NULL,
	limite int4,
	saldo int4
);

INSERT INTO clientes (id,limite,saldo) VALUES
	 (1,100000,0),
	 (2,80000,0),
	 (3,1000000,0),
	 (4,10000000,0),
	 (5,500000,0);


-- public.movimentacao definition

-- Drop table

-- DROP TABLE public.movimentacao;

CREATE TABLE public.movimentacao (
	id serial4 NOT NULL,
	fk_clientes int2,
	valor int4,
	tipo varchar,
	descricao varchar,
	data_hora timestamp DEFAULT now(),
	CONSTRAINT movimentacao_pk PRIMARY KEY (id)
);

-- Permissions

ALTER TABLE public.movimentacao OWNER TO postgres;
GRANT ALL ON TABLE public.movimentacao TO postgres;


