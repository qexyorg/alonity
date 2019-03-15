<?php
/**
 * Keys component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Alonity\Keys;

class Key {

	/**
	 * Преобразует данные в строку
	 *
	 * @param $params mixed
	 *
	 * @return string
	*/
	private static function dump($params){
		ob_start();

		var_dump($params);

		return ob_get_clean();
	}

	/**
	 * Возвращает хэш-сумму входящих данных
	 *
	 * @param $params mixed
	 *
	 * @return string
	*/
	public static function make($params){
		return md5(self::dump($params));
	}
}