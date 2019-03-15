<?php
/**
 * Path component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Components;

class Path {

	private static $rootDir = null;

	private static $appName = null;

	private static $app = null;

	/**
	 * Выставляет имя текущего приложения
	 *
	 * @param $name string
	 *
	 * @return void
	*/
	public static function setApp($name){
		self::$appName = $name;
	}

	/**
	 * Возвращает имя текущего приложения
	 *
	 * @return string
	*/
	public static function getApp(){
		if(!is_null(self::$appName)){
			return self::$appName;
		}

		$app = self::getApplication();

		self::setApp($app['app_name']);

		return self::$appName;
	}

	private static function getApplication(){
		if(!is_null(self::$app)){
			return self::$app;
		}

		self::$app = (include(self::to('/Application.php')));

		return self::$app;
	}

	/**
	 * Возвращает рутовую диреторию
	 *
	 * @return string
	*/
	public static function root(){
		if(!is_null(self::$rootDir)){
			return self::$rootDir;
		}

		self::$rootDir = dirname(__DIR__);

		return self::$rootDir;
	}

	/**
	 * Возвращает директорию от корневого пути движка
	 *
	 * @param $to string
	 *
	 * @return string
	*/
	public static function to($to){
		return self::root().$to;
	}

	/**
	 * Возвращает директорию от директории приложения
	 *
	 * @param $to string
	 *
	 * @return string
	 */
	public static function app($to=''){
		return self::to(DIRECTORY_SEPARATOR.'Applications'.DIRECTORY_SEPARATOR.self::getApp().$to);
	}
}

?>