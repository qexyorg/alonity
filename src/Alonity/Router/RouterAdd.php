<?php
/**
 * Router add component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Alonity\Router;

class RouterAdd implements RouterAddInterface {
	private $pattern = null;

	private $name = null;

	private $methods = [];

	private $namespace = null;

	private $controller = null;

	private $action = 'index';

	private $params = [];

	private $aliases = [];

	private $view = null;

	/**
	 * Конструктор класса
	 * Принимает в качестве параметра - патерн
	 *
	 * @param $name string
	 * @param $pattern string
	*/
	public function __construct($name, $pattern='/'){
		$this->pattern = $pattern;
		$this->name = $name;
	}

	/**
	 * Методы приема маршрутом
	 *
	 * @param $methods array|string
	 *
	 * @return $this
	*/
	public function via($methods){
		if(is_array($methods)){
			$this->methods = array_replace_recursive($this->methods, $methods);
		}else{
			$this->methods[] = $methods;
		}

		return $this;
	}

	/**
	 * Пространство имен маршрута
	 *
	 * @param $name string
	 *
	 * @return $this
	 */
	public function _namespace($name){
		$this->namespace = $name;

		return $this;
	}

	/**
	 * Контроллер маршрута
	 *
	 * @param $name string
	 *
	 * @return $this
	 */
	public function controller($name){
		$this->controller = $name;

		return $this;
	}

	/**
	 * Исполняемый метод маршрута
	 *
	 * @param $name string
	 *
	 * @return $this
	 */
	public function action($name){
		$this->action = $name;

		return $this;
	}

	/**
	 * Выставляет представление маршрута
	 *
	 * @param $text string
	 *
	 * @return $this
	 */
	public function view($text){
		$this->view = $text;

		return $this;
	}

	/**
	 * Выставляет алиасы маршрута
	 *
	 * @param $pattern string
	 * @param $params array|null
	 *
	 * @return $this
	 */
	public function aliases($pattern, $params=null){
		if(is_null($params)){
			$this->aliases[] = $pattern;
		}else{
			$this->aliases[$pattern] = $params;
		}

		return $this;
	}

	/**
	 * Передаваемые маршрутом параметры
	 *
	 * @param $params array
	 *
	 * @return $this
	 */
	public function params($params){
		$this->params = array_replace_recursive($this->params, $params);

		return $this;
	}

	/**
	 * Метод, выполняющий сборку маршрута
	 *
	 * @return boolean
	 */
	public function execute(){
		if(empty($this->methods)){
			$this->methods = RouterHelper::getDefaultMethods();
		}

		if(is_null($this->pattern)){
			return false;
		}

		if(is_null($this->view) && is_null($this->controller)){
			return false;
		}

		if(is_null($this->namespace)){
			$this->namespace = RouterHelper::getDefaultControllerNamespace();
		}

		RouterHelper::setRoute($this->name, [
			'pattern' => $this->pattern,
			'methods' => $this->methods,
			'namespace' => $this->namespace,
			'controller' => $this->controller,
			'action' => $this->action,
			'params' => $this->params,
			'aliases' => $this->aliases,
			'view' => $this->view
		]);

		return true;
	}
}