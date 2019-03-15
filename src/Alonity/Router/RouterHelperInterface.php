<?php
/**
 * Router helper interfase component of Alonity Framework
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

interface RouterHelperInterface {

	public static function getCurrent();

	/**
	 * Выставляет настройки по умолчанию
	 *
	 * @static
	 *
	 * @param $params array
	 *
	 * @return void
	 */
	public static function setOptions($params);

	/**
	 * Возвращает маршрут по умолчанию
	 *
	 * @return array
	 */
	public static function getDefaultRoute();

	/**
	 * Возвращает массив маршрутов
	 *
	 * @static
	 *
	 * @return array
	 */
	public static function getRoutes();

	/**
	 * Возвращает маршрут по названию
	 *
	 * @static
	 *
	 * @param $name string
	 *
	 * @return array|null
	 */
	public static function getRouteByName($name);

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
	public static function setRoute($name, $value=[]);

	/**
	 * Удаляет маршрут из массива
	 *
	 * @static
	 *
	 * @param $name string
	 *
	 * @return void
	 */
	public static function delRoute($name);
}