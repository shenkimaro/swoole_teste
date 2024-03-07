<?

	include_once($_SERVER['DOCUMENT_ROOT']."/library/xajax/xajax.inc.php");
//	$xajax = new xajax($_SERVER['HTTP_HOST']."/library/ext/geradorVO/Server.php5");	
	$xajax = new xajax("Server.php5");	
	$xajax->registerFunction("carregaCampos");
//	$xajax->debugOn();	
?>