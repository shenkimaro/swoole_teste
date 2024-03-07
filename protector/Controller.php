<?php

use Swoole\Http\Request;
use Swoole\Http\Response;

/**
 * @package Framework
 *
 * @subpackage Controller
 *
 * @filesource
 */

/**
 * Esta classe eh responsavel por acoes padroes dos controladores
 *
 * @author Ibanez C. Almeida <ibanez.almeida@gmail.com>
 *
 * @version 2.0
 *
 */
class Controller {

	/**
	 * Objeto View
	 *
	 * @var View
	 */
	protected $view;

	/**
	 * Objeto ViewAjax
	 *
	 * @var ViewAjax
	 */
	protected $viewAjax;

	/**
	 * Objeto DAO Generico
	 *
	 * @var DAO
	 */
	private $dao;

	/**
	 * Objeto Container
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Nome do template padrao
	 *
	 * @var String
	 */
	private $templateIndex;

	/**
	 * Contem as variaveis vindas do formulario
	 *
	 * @var array
	 */
	private $formVars;

	/**
	 * Contem acoes mapeadas ou mascaradas
	 *
	 * @var array
	 */
	private $actions;

	/**
	 * Contem acoes mapeadas para chamada de modulos
	 *
	 * @var array
	 */
	private $actionsModules;

	/**
	 * Contem labels
	 * @see setLabel()
	 * @var array
	 */
	private $label;

	/**
	 * Ação padrao para ser executada quando nao for requisitada alguma
	 * @var string
	 */
	private static $defaultAction = 'index';
	protected $limitLines;
	protected $offsetAtual;
	protected Restful $rest;
	protected Request $request;
	protected Response $response;

	public function __construct(Request $request, Response $response) {
		$this->setFormVars($_REQUEST);
		if (isset($GLOBALS['labels'])) {
			$this->setLabel($GLOBALS['labels']);
		}
		if (isset($GLOBALS['modules']) && count($GLOBALS['modules']) > 0) {
			$this->setActionModules($GLOBALS['modules']);
		}
		if (isset($GLOBALS['template']['templateDefault'])) {
			$this->setIndex($GLOBALS['template']['templateDefault']);
		}

		$this->limitLines = 30;
		$this->request = $request;
		$this->response = $response;
		$this->rest = new Restful($request, $response);
	}

	//************************************************************************************************************************\\

	/**
	 * Obtem as acoes 'padrao', ou uma acao especifica
	 *
	 * @param string $op Operacao a ser realizada
	 *
	 * @return mixed Contem as acoes que o controlador conhece ou
	 * uma acao especifica
	 *
	 * @author Ibanez C. Almeida
	 *
	 */
	public function getActions($op = '') {
		$action = $this->actions;
		if (trim($op) != '')
			return $action[strtolower(trim($op))];
		return $action;
	}

	/**
	 * Atribui acoes
	 *
	 * @param array $var Deve conter as acoes como chaves e os metodos como
	 * valores. Ex.: $action['cadastrar']="insert";
	 *
	 * @author Ibanez C. Almeida
	 *
	 */
	public function setActions($var) {
		foreach ($var as $key => $value) {
			$this->actions[strtolower($key)] = $value;
		}
	}

	/**
	 * Obtem as acoes mapeadas para chamadas dos modulos
	 *
	 * @param string $op Operacao de requisicao
	 *
	 * @return mixed Contem as acoes e os modulos respectivos
	 * ou somente uma acao e modulo respectivo
	 *
	 * @author Ibanez C. Almeida
	 *
	 */
	public function getActionModules($op = '') {
		$action = $this->actionsModules;
		if (trim($op) != '' && isset($action[strtoupper(trim($op))])) {
			return $action[strtoupper(trim($op))];
		}
		return $action;
	}

	/**
	 * Atribui acoes
	 *
	 * @param array $var Deve conter as acoes como chaves e os metodos como
	 * valores. Ex.: $modules['cadastrar']="insert";
	 *
	 * @author Ibanez C. Almeida
	 *
	 */
	public function setActionModules($var) {
		foreach ($var as $key => $value) {
			$action[strtoupper($key)] = $value;
		}
		$this->actionsModules = $action;
	}

	/**
	 * Pega as variaveis vindas do formulario
	 *
	 * @return array Variaveis do formulario
	 *
	 */
	protected function getFormVars($key = '') {
		if (trim($key) != '')
			return isset($this->formVars[$this->getLabel($key)]) && $this->formVars[$this->getLabel($key)] != '' ? $this->formVars[$this->getLabel($key)] : "";
		return $this->formVars;
	}

	/**
	 * Atribui as variaveis do formulario
	 *
	 * @param array $var Variaveis vindas do formulario
	 *
	 * @author Ibanez C Almeida
	 */
	protected function setFormVars($var = '') {
		$this->formVars = $var;
	}

	/**
	 * Atribui o template padrao
	 *
	 * @param array $var Nome do template padrao
	 *
	 * @author Ibanez C Almeida
	 */
	protected function setIndex($var = '') {
		$this->templateIndex = $var;
	}

	/**
	 * Atribui apelido para op e module
	 *
	 * @param array $var Ex.:$var['op']='action'
	 *
	 * @author Ibanez C Almeida
	 */
	protected function setLabel($var = '') {
		foreach ($var as $key => $value) {
			$this->label[$key] = $value;
		}
	}

	/**
	 * Pega o apelido
	 *
	 * @param String $var Nome da variavel buscada
	 *
	 * @author Ibanez C Almeida
	 */
	protected function getLabel($var = '') {
		return isset($this->label[$var]) && trim($this->label[$var]) != '' ? $this->label[$var] : $var;
	}

	/**
	 * Ouve as acoes
	 *
	 * @author Ibanez C. Almeida
	 *
	 */
	public function listener() {
		$opform = $this->getFormVars('op');
		$op = strtolower($opform);
		$actions = $this->getActions();
		$obj = $this;
		if (isset($actions[$op])) {
			if (method_exists($obj, $actions[$op])) {
				$func = $actions[$op];
				$this->$func();
			} else {
				$this->indexTemplate();
			}
		} elseif (is_string($opform) && $opform != '') {
			$this->loadAction($opform);
		} else {
			//echo "Sem acao a executar";
			$this->loadAction(self::$defaultAction);
		}
	}

	private function loadAction($action) {
		if (method_exists($this, $action)) {
			$this->$action();
			throw new Exception('No call for rest printRest: ' . $action);
		} else {
			throw new Exception('Endpoint no found: ' . $action);
		}
	}

	private function getControlByRequest($modRequest) {
		$prefix = '';
		if (defined('_CONTROLLER_PREFIX')) {
			$prefix = _CONTROLLER_PREFIX;
		}
		$xplodeModulo = explode("_", $modRequest);
		$max = count($xplodeModulo);
		$className = $prefix;
		for ($index = 0; $index < $max; $index++) {
			$className .= ucfirst($xplodeModulo[$index]);
		}
		if (empty($className)) {
			throw new Exception('Modulo não informado');
		}
		if (class_exists($className)) {
			return new $className($this->request, $this->response);
		}
		return null;
	}

	/**
	 * Alias para loadModule
	 * @see loadModule
	 */
	public function start() {
		try {
			$this->loadModule();
		} catch (Throwable $t) {
			$this->rest->printREST(
				[
					"mensagem" => $t->getMessage(),
					"arquivo" => $t->getFile(),
					"linha" => $t->getLine(),
					"causado_por" => ($t->getPrevious()) ? $t->getPrevious()->getMessage() : '',
					"backtrace" => $t->getTraceAsString(),
				], 
				Restful::STATUS_ERRO_INTERNO_SERVIDOR
			);
		}
	}

	/**
	 * Carrega outro modulo(Controlador)
	 *
	 * @author Ibanez C. Almeida
	 *
	 */
	public function loadModule() {
		$mod = $this->getFormVars('module');
		$this->reloadRequest();
		$control = $this->getModuleByRequest($mod);

		if ($control != NULL && is_object($control)) {
			$control->listener();
		}
		$op = $this->getFormVars('op');
		if (!isset($GLOBALS['files'])) {
			throw new Exception('Template folder not found');
		}
		$templateFile = $GLOBALS['files']['templates'] . "/$op";
		if (file_exists($templateFile . $GLOBALS['template']['extension'])) {
			$this->indexTemplate($templateFile);
		}
		$this->indexTemplate();
	}

	private function getModuleByRequest($mod) {
		$actMod = $this->getActionModules($mod);
		if (!is_array($actMod) && ($actMod) && class_exists($actMod)) {
			return new $actMod($this->request, $this->response);
		}
		return $this->getControlByRequest($mod);
	}

	protected function reloadRequest() {
		if (defined('_SYSNAME') && isset($_SESSION[_SYSNAME]['request']) && count($_SESSION[_SYSNAME]['request']) > 0) {
			$_REQUEST = array_merge($_SESSION[_SYSNAME]['request'], $_REQUEST);
			$_SESSION[_SYSNAME]['request'] = array();
		}
	}

	public function __get($field) {
		if ($field == 'dao') {
			return DAO::getInstance();
		}
	}

}
