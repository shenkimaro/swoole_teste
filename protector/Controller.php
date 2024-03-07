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
		if (!isset($this->view)) {
			$this->view = View::getInstance();
		}

		$this->limitLines = 30;
		$this->request = $request;
		$this->response = $response;
		$this->rest = new Restful($request, $response);

		$page = RequestParamns::getInt('page', 1);
		$this->offsetAtual = $page * $this->limitLines;
		$this->view->setPage($page, "block");
		if (defined('_TEMPLATE_PAGINATION_MAX_ROWS')) {
			$this->view->setRowsLimit(_TEMPLATE_PAGINATION_MAX_ROWS);
		} else {
			$this->view->setRowsLimit(50);
		}
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
	 * Chama o template padrao para ser exibido
	 *
	 * @param array $var Nome do template padrao
	 *
	 * @author Ibanez C Almeida
	 */
	protected function indexTemplate($var = '') {
        if (trim($this->templateIndex) == '') {
			$this->templateIndex = defined('_templateIndex') ? _templateIndex : '';
		}
        $this->view->mergeTemplateIndex(trim($var) != '' ? $var : $this->templateIndex);
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
				$this->view->mergeTemplate($op);
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
		$templateFile = strtolower($action);
		if (method_exists($this, $action)) {
			$this->$action();
			if (!(defined('_TEMPLATE_MANAGER') && _TEMPLATE_MANAGER == View::ENGINE_JSONVIEW) && !$this->view->templateExists($templateFile)) {
				throw new Exception('No Template found to this Method: ' . $templateFile);
			}
			$this->view->mergeTemplate($templateFile);
		} elseif ($this->view->templateExists($templateFile)) {
			$this->view->mergeTemplate($templateFile);
		} else {
			$this->indexTemplate();
		}
	}

	public static function getControlByRequest($modRequest) {
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
			return new $className();
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
			$this->call($control);
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
		return self::getControlByRequest($mod);
	}

	protected function reloadRequest() {
		if (defined('_SYSNAME') && isset($_SESSION[_SYSNAME]['request']) && count($_SESSION[_SYSNAME]['request']) > 0) {
			$_REQUEST = array_merge($_SESSION[_SYSNAME]['request'], $_REQUEST);
			$_SESSION[_SYSNAME]['request'] = array();
		}
	}

	public function call($obj) {
		$r = new ReflectionObject($obj);
		$methods = $r->getMethods();
		$script = "";
		foreach ($methods as $key => $value) {
			$methodName = $value->getName();
			try {
				$p = $r->getMethod($methodName);
				$dc = $p->getDocComment();
				if (preg_match("#@RegisterAjax\s#", $dc, $a)) {
					$moduleName = $this->getLabel("module");
					$moduleValue = $this->getFormVars($moduleName);
					$opName = $this->getLabel("op");
					if ($script == "")
						$script = '<script type="text/javascript">';
					$ajaxPrefix = 'ajax';
					if (defined('_AJAX_FUNCTION_PREFIX')) {
						$ajaxPrefix = _AJAX_FUNCTION_PREFIX;
					}
					if ($ajaxPrefix != '') {
						$functionName = $ajaxPrefix . '_' . $methodName;
					} else {
						$functionName = $methodName;
					}
					$script .= "\nfunction $functionName(){\n";
					$script .= "\tvar arguments = $functionName.arguments;
							var paramsString = '';
							var data = {
							    $moduleName:'{$moduleValue}',
							    $opName:'{$methodName}'
							    };\n;
                            for (var i = 0; i < arguments.length; i++){
                                var campo = 'param'+i;
                                data[campo] = arguments[i];
							}\n";
					$script .= "\t if(!$('#stormAjax') || $('#stormAjax').length == 0) { return;} \n";
					$script .= "document.getElementById('ajax_loading').style.display='inline';\n ";
					$script .= "$('#stormAjax').load('?$moduleName={$moduleValue}&$opName=$methodName', data, function(response, status, xhr) {
								if (response.includes('<body')) {
								    document.write(response);\n
								    return;
								}
								document.getElementById('ajax_loading').style.display='none';\n
							});\n";
					$script .= "}\n";
				}
			} catch (Exception $e) {
				Debug::tail($e);
			}
		}
		if ($script != "")
			$script .= '</script>';
		$this->view->addDefault("scriptAjax", $script);
	}

	public function __get($field) {
		if ($field == 'dao') {
			return DAO::getInstance();
		}
	}

}
