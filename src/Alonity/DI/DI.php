<?php
/**
 * DI component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Alonity\DI;

use Framework\Alonity\Keys\Key;

class DI implements DIInterface {
	private static $storage = [];

	/**
	 * Проверяет наличие объекта в инъекции
	 *
	 * @param $name mixed
	 *
	 * @return boolean
	 */
	public static function has($name){
		return (isset(self::$storage[Key::make($name)]));
	}


	/**
	 * Возвращет объект из инъекции
	 *
	 * @param $name mixed
	 *
	 * @return mixed
	 */
	public static function get($name){
		$key = Key::make($name);

		if(!self::has($name)){
			return null;
		}

		return self::$storage[$key];
	}

	/**
	 * Устанавливает значение инъекции и возвраает его
	 *
	 * @param $name mixed
	 * @param $value mixed
	 *
	 * @return mixed
	 */
	public static function set($name, $value){
		$key = Key::make($name);

		self::$storage[$key] = $value;

		return $value;
	}
}