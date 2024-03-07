<?php
die();
	include("../../Db.php5");
	include("../../Email.php5");
	include_once("common.php5");

	function carregaCampos($esquema,$tabela,$bd) {		
		$db = new Db('10.20.3.1',$bd,"postgres","Hitokiri_Battousai");
		$sql = "SELECT a.attname as fields 
			FROM pg_attribute a 
			WHERE a.atttypid > 0 and a.attnum > 0 AND
			(a.attrelid = 
			(
			SELECT pc.oid
			FROM pg_class pc 
			inner join pg_namespace pn on pc.relnamespace = pn.oid
			WHERE 
			relname='$tabela'
			and 
			nspname='$esquema'
			))
			ORDER BY a.attnum";
		$ex = $db->query($sql);
		$var = '';
		for ($x=0;$x<$db->num_rows($ex);$x++){
			if ($var)
				$var .=",\n".$db->result($ex,$x,0);
			else $var .=$db->result($ex,$x,0);
		}
		$objAjax=new xajaxResponse();
		$objAjax->addAssign("campos","value",$var);
		$objAjax->addAssign("mensagem","innerHTML",'');
		return $objAjax;
	}
	
	$xajax->processRequests();
?>