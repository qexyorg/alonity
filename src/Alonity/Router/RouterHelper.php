<?php
/**
 * Router helper component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.1.3
 */

namespace Framework\Alonity\Router;

use Framework\Alonity\DI\DI;
use Framework\Alonity\Keys\Key;

class RouterHelper implements RouterHelperInterface {
	private static $storage = [];

	private static $options = [];

	private static $current = null;

	private static $rootDir = null;

	private static $rootDirApp = null;

	private static $application = null;

	private static $defaultMethods = null;

	private static $fileRoutes = [];

	private static $appConfig = null;

	private static function filterPattern($string){

		$string = preg_quote($string, '/');

		$string = str_replace([
			'\:int', '\:integer', '\:string', '\:float', '\:boolean', '\:any', '\:urlparams', '\:notslash'
		], [
			'(\d+)', '(\d+)', '([\w\-]+)', '(\d+\.?\d+?)', '(true|false)', '(.*)', '([\w\=\-\|\@\#\&\?\%\|\.]+)', '([^\/]+)'
		], $string);

		return $string;
	}

	public static function getRouteByURL($url, $method='GET'){

		$url = urldecode($url);

		$routes = self::getRoutes();

		$current = false;

		if(empty($routes)){
			return $current;
		}

		foreach($routes as $key => $value){
			if(!isset($value['pattern'])){ continue; }

			$params = (isset($value['params']) && is_array($value['params'])) ? $value['params'] : [];

			if(isset($value['methods'])){
				if(is_string($value['methods']) && $method!=$value['methods']){
					continue;
				}elseif(is_array($value['methods']) && !in_array($method, $value['methods'])){
					continue;
				}
			}

			if(mb_substr($value['pattern'], -1, null, 'UTF-8')=='/'){
				$value['pattern'] = mb_substr($value['pattern'], 0, -1, 'UTF-8');
			}

			$pattern = self::filterPattern($value['pattern']);

			if(!preg_match("/^$pattern\/?$/iu", $url, $matches)){

				if(isset($value['aliases']) && !empty($value['aliases'])){

					if(!is_array($value['aliases'])){
						continue;
					}

					foreach($value['aliases'] as $k => $as){

						$alias = (is_int($k)) ? $as : $k;

						if(mb_substr($alias, -1, null, 'UTF-8')=='/'){
							$alias = mb_substr($alias, 0, -1, 'UTF-8');
						}

						$alias = self::filterPattern($alias);

						if(preg_match("/^$alias\/?$/iu", $url, $matches)){

							if(!is_int($k)){ $params = $as; }

							$value['pattern'] = (is_int($k)) ? $as : $k;

							break;
						}
					}
				}else{
					continue;
				}
			}

			if(empty($matches)){ continue; }

			if(isset($matches[0])){ unset($matches[0]); }

			$current = $value;

			$i = 1;
			foreach($params as $k => $v){
				$current['params'][$k] = (isset($matches[$v])) ? $matches[$v] : $v;

				$i++;
			}

			break;
		}

		return $current;
	}

	/**
	 * Возвращает настройки текущего маршрута
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function getCurrent(){
		if(!is_null(self::$current)){
			return self::$current;
		}

		$routes = self::getRoutes();

		self::$current = self::getDefaultRoute();

		if(empty($routes)){
			return self::$current;
		}

		$route = self::getRouteByURL($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);

		if($route!==false){
			self::$current = $route;
		}

		return self::$current;
	}

	/**
	 * Возвращает массив параметров
	 *
	 * @return array
	 */
	public static function getParams(){
		$current = self::getCurrent();

		return (isset($current['params'])) ? $current['params'] : [];
	}

	/**
	 * Возвращает значение по имени параметра
	 *
	 * @param $name string
	 *
	 * @return string|null
	 */
	public static function getParam($name){
		$current = self::getCurrent();

		if(!isset($current['params']) || empty($current['params'])){
			return null;
		}

		if(!isset($current['params'][$name])){
			return null;
		}

		return $current['params'][$name];
	}

	/**
	 * Возвращает корневую директорию
	 *
	 * @static
	 *
	 * @return string
	 */
	public static function getRoot(){
		if(!is_null(self::$rootDir)){ return self::$rootDir; }

		self::$rootDir = DI::get('ALONITY')->getRoot();

		return self::$rootDir;
	}

	/**
	 * Возвращает корневую директорию используемого приложения
	 *
	 * @static
	 *
	 * @return string
	 */
	public static function getRootApp(){

		if(!is_null(self::$rootDirApp)){ return self::$rootDirApp; }

		$app = self::getApp();

		self::$rootDirApp = self::getRoot().dirname($app['app_filename']);

		return self::$rootDirApp;
	}

	/**
	 * Возвращает конфигурацию приложения
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function getApp(){
		if(!is_null(self::$application)){ return self::$application; }

		self::$application = (require(self::getRoot().'/Application.php'));

		return self::$application;
	}

	/**
	 * Возвращает пространство имен по умолчанию
	 *
	 * @static
	 *
	 * @return string
	 */
	public static function getDefaultControllerNamespace(){
		$application = self::getApp();

		$appname = $application['app_name'];

		return "App\\$appname\\Controllers";
	}

	/**
	 * Возвращает массив настроек по умолчанию
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function getOptions(){
		return self::$options;
	}

	/**
	 * Выставляет настройки по умолчанию
	 *
	 * @static
	 *
	 * @param $params array
	 *
	 * @return void
	 */
	public static function setOptions($params){
		self::$options = array_replace_recursive(self::$options, $params);
	}

	/**
	 * Возвращает маршрут по умолчанию
	 *
	 * @return array
	 */
	public static function getDefaultRoute(){
		if(isset(self::$options['default'])){
			return self::$options['default'];
		}

		return [
			'pattern' => '/404',
			'methods' => self::getDefaultMethods(),
			'namespace' => '',
			'view' => '404',
		];
	}

	/**
	 * Возвращает массив маршрутов
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function getRoutes(){
		return self::$storage;
	}

	/**
	 * Возвращает маршрут по названию
	 *
	 * @static
	 *
	 * @param $name string
	 *
	 * @return array|null
	 */
	public static function getRouteByName($name){
		if(!isset(self::$storage[$name])){
			return null;
		}

		return self::$storage[$name];
	}

	/**
	 * Устанавливает значение маршрута
	 *
	 * @static
	 *
	 * @param $name string
	 * @param $value array
	 *
	 * @return void
	 */
	public static function setRoute($name, $value=[]){
		self::$storage[$name] = $value;
	}

	/**
	 * Удаляет маршрут из массива
	 *
	 * @static
	 *
	 * @param $name string
	 *
	 * @return void
	 */
	public static function delRoute($name){
		if(isset(self::$storage[$name])){
			unset(self::$storage[$name]);
		}
	}

	/**
	 * Возвращает методы по умолчанию
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function getDefaultMethods(){
		if(!is_null(self::$defaultMethods)){
			return self::$defaultMethods;
		}

		$application = self::getApp();

		self::setDefaultMethods($application['default_route_methods']);

		return self::$defaultMethods;
	}

	/**
	 * Выставляет методы по умолчанию
	 *
	 * @static
	 *
	 * @param $methods array
	 *
	 * @return void
	 */
	public static function setDefaultMethods($methods){
		self::$defaultMethods = $methods;
	}

	/**
	 * Получение массива маршрутов из файла
	 *
	 * @static
	 *
	 * @param $filename string
	 *
	 * @throws RouterException
	 *
	 * @return array
	 */
	public static function getRoutesFile($filename){
		$key = Key::make([__METHOD__, $filename]);

		if(isset(self::$fileRoutes[$key])){
			return self::$fileRoutes[$key];
		}

		if(!file_exists($filename)){
			throw new RouterException("<p>File <b>{$filename}</b> not found</p>");
		}

		self::$fileRoutes[$key] = (require($filename));

		return self::$fileRoutes[$key];
	}

	/**
	 * Получение массива параметров приложения
	 *
	 * @param $cache boolean
	 *
	 * @return array
	 */
	public static function getAppConfig($cache=true){

		if($cache && !is_null(self::$appConfig)){
			return self::$appConfig;
		}

		$app = self::getApp();

		if(!isset($app['app_config_filename'])){
			return [];
		}

		$filename = self::getRoot().$app['app_config_filename'];

		if(!file_exists($filename)){
			return [];
		}

		self::$appConfig = (require($filename));

		return self::$appConfig;
	}
}