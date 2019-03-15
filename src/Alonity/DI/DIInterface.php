<?php
/**
 * DI Interface component of Alonity Framework
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

interface DIInterface {
	/**
	 * Возвращет объект из инъекции
	 *
	 * @param $name mixed
	 *
	 * @return mixed
	 */
	public static function get($name);

	/**
	 * Устанавливает значение инъекции и возвраает его
	 *
	 * @param $name mixed
	 * @param $value mixed
	 *
	 * @return mixed
	 */
	public static function set($name, $value);

	/**
	 * Проверяет наличие объекта в инъекции
	 *
	 * @param $name mixed
	 *
	 * @return boolean
	 */
	public static function has($name);
}