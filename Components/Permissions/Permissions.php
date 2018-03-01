<?php
/**
 * Permissions component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 *
 */

namespace Alonity\Components\Permissions;

use Alonity\Components\Permissions\Update as Update;

class PermissionsException extends \Exception {}

class Permissions {

	private static $data = [];

	private static $local = [];

	private static $init = false;

	private static $permdir = __DIR__;

	private static function loading(){
		if(self::$init){ return true; }

		self::$permdir = dirname(dirname(__DIR__)).'/Uploads/permissions';

		if(!file_exists(self::$permdir)){
			@mkdir(self::$permdir, 0777, true);
		}

		if(file_exists(self::$permdir.'/default.php')){
			self::$data = (require_once(self::$permdir.'/default.php'));
		}else{
			self::$data = [];
		}

		self::$local = self::$data;

		self::$init = true;

		return true;
	}

	private static function hashed($name){
		return md5($name);
	}

	/**
	 * Возвращает массив со всей информацией о привилегии
	 *
	 * @param $name mixed
	 *
	 * @throws PermissionsException
	 *
	 * @return array
	*/
	public static function localGetInfo($name){
		self::loading();

		$key = self::hashed($name);

		if(!isset(self::$local[$key])){
			return null;
		}

		if(!isset(self::$local[$key])){
			throw new PermissionsException('index "value" is not set');
		}

		return self::$local[$key];
	}

	/**
	 * Возвращает значение привилегии
	 *
	 * @param $name mixed
	 *
	 * @throws PermissionsException
	 *
	 * @return mixed
	*/
	public static function localGetVal($name){
		self::loading();

		$key = self::hashed($name);

		if(!isset(self::$local[$key])){
			return null;
		}

		if(!isset(self::$local[$key]['value'])){
			throw new PermissionsException('index "value" is not set');
		}

		return self::$local[$key]['value'];
	}

	/**
	 * Устанавливает значение привилегии, которое будет доступно в пределах текущего запроса
	 *
	 * @param $name mixed
	 * @param $value mixed
	 *
	 * @return mixed
	 */
	public static function localSetVal($name, $value){
		self::loading();

		$key = self::hashed($name);

		self::$local[$key] = $value;

		return self::$local[$key];
	}

	/**
	 * Удаляет локальную привилегию
	 *
	 * @param $name mixed
	 *
	 * @return mixed
	 */
	public static function localDelete($name){
		self::loading();

		$key = self::hashed($name);

		if(!isset(self::$local[$key])){
			return false;
		}

		unset(self::$local[$key]);

		return true;
	}

	/**
	 * Проверяет наличие привилегии
	 *
	 * @param $name mixed
	 *
	 * @return boolean
	*/
	public static function exists($name){
		self::loading();

		$key = self::hashed($name);

		return isset(self::$local[$key]);
	}

	/**
	 * Сравнивает значения
	 *
	 * @param $name mixed
	 * @param $value mixed
	 *
	 * @return boolean
	*/
	public static function equal($name, $value){
		self::loading();

		$key = self::hashed($name);

		if(!isset(self::$local[$key])){
			return false;
		}

		if(!isset(self::$local[$key]['value'])){
			return false;
		}

		return (self::$local[$key]['value']===$value);
	}

	/**
	 * Возвращает экземпляр класса Update
	 *
	 * @return \Alonity\Components\Permissions\Update()
	*/
	public static function update(){
		self::loading();

		return new Update(self::$data);
	}
}

?>