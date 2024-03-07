<?php
	include_once("common.php5");
	$xajax->printJavascript("../../xajax/");
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
	<title>Gerador de classes de PHP</title>
	<script type="text/javascript">
	
		function trim(str) {
			var re1 = /^\s+/; // espaço em branco inicial
			var re2 = /\s+$/; // espaço em branco final
			var trocaPor = "";
			if (str.replace) {
				str = str.replace(re1, trocaPor);
				str = str.replace(re2, trocaPor);
			}
			return str;
		}
		
		function iniMai(str) {
			var primeiraLetra = str.charAt(0);
			var resto = str.substring(1, str.length);
			var temp = '';
			var y=0;
			for(x=1;x<str.length;x++){
				if(str.charAt(x)=='_'){
					x++;
					temp+=str.charAt(x).toUpperCase();
				}
				else temp+=str.charAt(x);
			}
			return primeiraLetra.toUpperCase() + temp;
		}

	
		function gerarCodigoPHP() {
			var f = document.forms["classes"];
			var str = "";
			var classe, atributos,pk,table;
			var fks=null;
			
			classe = f.nome.value;
			esquema= f.esquema.value;
			table= f.tabela.value;
			pk=f.pk.value;
			fks = f.fks.value.split(",");
			atributos = f.campos.value.split(",");
			str += "&lt;?\n\n";
			str += "class " + classe + " extends VO{\n \n";
			
			//gerando atributo pk
			str += "\t//Pk da tabela\n";
			str += "\tprivate $" + trim(pk) + ";\n\n";
			
			// gerando atributos
			if(trim(atributos)!=''){
				str += "\t//Campos da tabela\n";
				for (var a=0; a<atributos.length; a++) {
					str += "\tprivate $" + trim(atributos[a]) + ";\n";
				}
				str += "\n";
			}					
			
			str += "\t//Fks da tabela\n";
			for(x=0;x<fks.length;x++){
				if(fks[x]!=''){
					fkVal=fks[x].split('>');
					str += "\tprivate $" + trim(fkVal[0])+";\n";
				}
			}
			
			// gerando construtor
			if (f.construtor.checked) {
			
				str += "\n";
				str += "\tpublic function __construct(";				
				/*for (var a=0; a<atributos.length; a++) {
					str += "$" + trim(atributos[a]) + " = \"\"";
					if (a < (atributos.length-1)) {
						str +=", ";
					}					
				}*/
				str += "$array=array()"
				str += ") {\n"
				str += "\t\tparent::__construct();\n";
				str += "\t\t$this->setTableName('" + trim(esquema) + "." + trim(table)+ "');\n";
				str += "\t\t$this->setPkName('" + trim(pk) + "');\n";
				for(x=0;x<fks.length;x++){
					if(fks[x]!=''){
						fkVal=fks[x].split('>');
						str += "\t\t$this->setFkName('" + trim(fkVal[0]) + "','"+trim(fkVal[1])+"');\n";
					}
				}
				str += "\t\t$this->setFields(get_class_vars(get_class($this)));\n";
//				for (var a=0; a<atributos.length; a++) {
//					str += "\t\t$this->set" + iniMai(trim(atributos[a])) + "($" + trim(atributos[a]) + ");\n";
//				}
				str += "\t\tif(count($array))";
				str += " $this->setVO($array);\n";
				str += "\t\telse ";
				str += " $this->setVO($_REQUEST);\n";
				str += "\t}\n";
			}
		
			str += "\n";
			str += "\tpublic function __get($name) {\n";
			str += "\t\treturn $this->$name;\n";				
			str += "\t}";
			
			// gerando destrutor
			if (f.destrutor.checked) {
				str += "\n";
				str += "\tpublic function __destruct() {\n";
				str += "\t\t// código do destrutor\n";				
				str += "\t}\n";
			}
			
			// gerando getters
			if (f.getters.checked) {
				
				//gerando get PK
				str += "\n \n\tpublic function get" + iniMai(trim(pk)) + "() {\n";
				str += "\t\treturn $this->" + trim(pk) + ";\n";
				str += "\t}";
				
				//gerando getters FKs
				for (var a = 0; a < fks.length; a++) {
					if(fks[a]!=''){
						fkVal=fks[a].split('>');
						str += "\n \n\tpublic function get" + iniMai(trim(fkVal[0])) + "() {\n";
						str += "\t\treturn $this->" + trim(fkVal[0]) + ";\n";
						str += "\t}";
					}
				}	
				
				//gerando getters
				for (var a=0; a<atributos.length; a++) {
					str += "\n \n\tpublic function get" + iniMai(trim(atributos[a])) + "() {\n";
					str += "\t\treturn $this->" + trim(atributos[a]) + ";\n";
					str += "\t}";
				}				
				
			}
			
			// gerando setters
			if (f.setters.checked) {
			
				//gerando set PK
				str += "\n \n\tpublic function set" + iniMai(trim(pk)) + "($" + trim(pk) + ") {\n";
				str += "\t\t$this->" + trim(pk) + " = $" + trim(pk) + ";\n";
				str += "\t}";
				
				//gerando setters FKs
				for (var a=0; a<fks.length; a++) {
					if(fks[a]!=''){
						fkVal=fks[a].split('>');
						str += "\n\n\tpublic function set" + iniMai(trim(fkVal[0])) + "($" + trim(trim(fkVal[0])) + ") {\n";
						str += "\t\t$this->" + trim(trim(fkVal[0])) + " = $" + trim(trim(fkVal[0])) + ";\n";
						str += "\t}";
					}
				}
				
				//gerando setters
				for (var a=0; a<atributos.length; a++) {
					str += "\n\n\tpublic function set" + iniMai(trim(atributos[a])) + "($" + trim(atributos[a]) + ") {\n";
					str += "\t\t$this->" + trim(atributos[a]) + " = $" + trim(atributos[a]) + ";\n";
					str += "\t}";
				}
			}
			
			//gerando methodsMap
			if (f.mapping.checked) {
				str += "\n\n\tpublic function methodsMap() {\n";
				str += "\t\t$this->addMethodsToBdMap ('get"+iniMai(trim(pk))+"','"+ trim(pk) + "');\n";
				for (var a=0; a<atributos.length; a++) {
					str += "\t\t$this->addMethodsToBdMap ('get"+iniMai(trim(atributos[a]))+"','"+ trim(atributos[a]) + "');\n";
				}
				for(x=0;x<fks.length;x++){
					if(fks[x]!=''){
						fkVal=fks[x].split('>');
						str += "\t\t$this->addMethodsToBdMap('get" + trim(fkVal[0]) + "','"+trim(fkVal[0])+"');\n";
					}
				}
				str += "\t}";
			}
			
			
			str += "\n}";
			str += "\n\n?&gt;";
			document.getElementById("codigo").innerHTML = str;	
		}
		
		function carregaCampos(){
			esquema = document.getElementById('esquema').value;
			tabela = document.getElementById('tabela').value;
			bd = document.getElementById('bd').value;
			if((esquema!='')&&(tabela!='')){
				document.getElementById('mensagem').innerHTML = 'Buscando dados...';
				xajax_carregaCampos(esquema,tabela,bd);
			}else{
				alert('Preencha o nome do esquema e da tabela ')
			}
		}
	</script>
	
	<style type="text/css">
		pre {
			white-space: pre;
			background-color: #eeeeee;
			display: block;
		}
	</style>
</head>

<body>

	<h1>Gerador de classes VO PHP</h1>
	<form name="classes">
		<p>Informe o nome da classe e os campos. Separe os campos com ",".</p>				
		<table>
			<tr>
				<td width="70%" valign="top">
				Banco:<br>
				<input type="text" id="bd" name="bd" size="50"><br>
				
				Nome do Esquema:<br>
				<input type="text" id="esquema" name="esquema" size="30"><br>
				
				Nome da tabela:<br>
				<input type="text" id="tabela" name="tabela" size="40">				
				<input type="button" value="Carregar Campos" onclick="carregaCampos()">				
				<br>
				<span id="mensagem"></span>
				</td>
				<td>
				Campos:<br>
				<textarea name="campos" id="campos" cols="30" rows="10"></textarea><br>
				</td>
			</tr>
		</table>		
		<hr>
		
		Nome da classe:<br>
		<input type="text" name="nome" size="100"><br>
		
		Nome da Pk:<br>
		<input type="text" name="pk" size="100"><br>
		
		Objetos FKs:<br>
		Ex.: ClasseVO>fk_tabela, OutroClasseVO>outra_fk_tabela<br>
		<input type="text" name="fks" size="100"><br>				
		
		Construtor:
		<input type="Checkbox" name="construtor" checked="checked" disabled="disabled">				
		
		<label for="mapping">
		Mapeamento:
		</label>
		<input type="Checkbox" name="mapping" id="mapping" checked="checked" disabled="disabled">
		
		<label for="getters">
		Getters:
		</label>
		<input type="Checkbox" name="getters" id="getters" checked="checked" disabled="disabled">
		<label for="setters">
		Setters:
		</label>
		<input type="Checkbox" name="setters" id="setters" checked="checked" disabled="disabled">
		
		<label for="destrutor">
		Destrutor:
		</label>
		<input type="Checkbox" name="destrutor" id="destrutor">
		<br>
		<button type="button" onClick="gerarCodigoPHP()">Gerar</button>
		
		<fieldset>
			<legend>Código</legend>
			<pre id="codigo"></pre>
		</fieldset>
	</form>
	

</body>
</html>
<?
	$sql = "SELECT a.attname as fields 
			FROM pg_attribute a 
			WHERE a.atttypid > 0 and a.attnum > 0 AND
			(a.attrelid = 
			(
			SELECT pc.oid
			FROM pg_class pc 
			inner join pg_namespace pn on pc.relnamespace = pn.oid
			WHERE 
			relname='pessoas'
			and 
			nspname='ueg'
			))
			ORDER BY a.attnum";
	
?>
