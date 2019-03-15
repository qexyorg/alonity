<?php
/**
 * Controller component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.0.0
 */

namespace Framework\Alonity\Controller;

use Framework\Alonity\Keys\Key;
use Framework\Alonity\Router\Router;
use Framework\Alonity\Router\RouterHelper;

class Controller extends Router {
	private $currentModel = null;

	private $currentView = null;

	private $views = [];

	private $models = [];

	private $params = null;

	/**
	 * Возвращает экземпляр текущей модели
	 *
	 * @return object
	 */
	public function getCurrentModel(){

		if(!is_null($this->currentModel)){
			return $this->currentModel;
		}

		$application = RouterHelper::getApp();

		$appname = $application['app_name'];

		$router = $this->getCurrent();

		$classname = "App\\$appname\\Models\\".$router['controller'];

		if(!class_exists($classname)){
			return null;
		}

		$this->currentModel = new $classname();

		return $this->currentModel;
	}

	/**
	 * Возвращает экземпляр текущего представления
	 *
	 * @return object
	 */
	public function getCurrentView(){

		if(!is_null($this->currentView)){
			return $this->currentView;
		}

		$application = RouterHelper::getApp();

		$appname = $application['app_name'];

		$router = $this->getCurrent();

		$classname = "App\\$appname\\Views\\".$router['controller'];

		if(!class_exists($classname)){
			return null;
		}

		$this->currentView = new $classname();

		return $this->currentView;
	}

	public function getView($classname=null){
		if(is_null($classname)){
			return $this->getCurrentView();
		}

		$key = md5(var_export([__METHOD__, $classname], true));

		if(isset($this->views[$key])){
			return $this->views[$key];
		}

		if(!class_exists($classname)){
			return null;
		}

		$this->views[$key] = new $classname();

		return $this->views[$key];
	}

	public function getModel($classname=null){
		if(is_null($classname)){
			return $this->getCurrentModel();
		}

		$key = Key::make([__METHOD__, $classname]);

		if(isset($this->models[$key])){
			return $this->models[$key];
		}

		if(!class_exists($classname)){
			return null;
		}

		$this->models[$key] = new $classname();

		return $this->models[$key];
	}

	public function getParams(){
		if(!is_null($this->params)){
			return $this->params;
		}

		$router = $this->getCurrent();

		$this->params = (isset($router['params'])) ? $router['params'] : [];

		return $this->params;
	}

	public function getParam($name){
		$params = $this->getParams();

		return (!isset($params[$name])) ? null : $params[$name];
	}
}

?>