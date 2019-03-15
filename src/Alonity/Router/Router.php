<?php
/**
 * Router component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.0.0
 */

namespace Framework\Alonity\Router;

class Router implements RouterInterface {

	/**
	 * Получает текущий маршрут
	 *
	 * @return array
	*/
	public function getCurrent(){
		return RouterHelper::getCurrent();
	}

	/**
	 * Возвращает маршрут по ключу(паттерну)
	 *
	 * @param $name string
	 *
	 * @return array|null
	*/
	public function get($name){
		return RouterHelper::getRouteByName($name);
	}

	/**
	 * Возвращает список всех выставленных маршрутов
	 *
	 * @return array
	*/
	public function getAll(){
		return RouterHelper::getRoutes();
	}

	/**
	 * Удаляет маршрут по ключу
	 *
	 * @param $name string
	 *
	 * @return void
	*/
	public function del($name){
		RouterHelper::delRoute($name);
	}

	/**
	 * Добавляет маршрут по ключу
	 *
	 * @param $name string
	 * @param $pattern string
	 *
	 * @return RouterAdd()
	*/
	public function add($name, $pattern){
		return new RouterAdd($name, $pattern);
	}

	/**
	 * Добавляет массив маршрутов
	 *
	 * @param $routes array
	 *
	 * @return void
	*/
	public function addMultiple($routes){
		if(empty($routes)){
			return;
		}

		foreach($routes as $k => $v){

			if(!isset($v['pattern']) || empty($v['pattern'])){ continue; }

			if(!isset($v['controller']) && !isset($v['view'])){ continue; }

			if(empty($v['controller']) && empty($v['view'])){ continue; }

			RouterHelper::setRoute($k, [
				'pattern' => $v['pattern'],
				'methods' => (isset($v['methods'])) ? $v['methods'] : RouterHelper::getDefaultMethods(),
				'namespace' => (isset($v['namespace'])) ? $v['namespace'] : RouterHelper::getDefaultControllerNamespace(),
				'controller' => (isset($v['controller'])) ? $v['controller'] : null,
				'action' => (isset($v['action'])) ? $v['action'] : 'index',
				'params' => (isset($v['params'])) ? $v['params'] : [],
				'view' => (isset($v['view'])) ? $v['view'] : null,
				'aliases' => (isset($v['aliases'])) ? $v['aliases'] : []
			]);
		}
	}
}

?>