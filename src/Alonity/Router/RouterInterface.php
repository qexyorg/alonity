<?php
/**
 * Router Interface of Alonity Framework
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

interface RouterInterface {
	/**
	 * Получает текущий маршрут
	 *
	 * @return array
	 */
	public function getCurrent();

	/**
	 * Возвращает маршрут по ключу(паттерну)
	 *
	 * @param $name string
	 *
	 * @return array|null
	 */
	public function get($name);

	/**
	 * Возвращает список всех выставленных маршрутов
	 *
	 * @return array
	 */
	public function getAll();

	/**
	 * Удаляет маршрут по ключу
	 *
	 * @param $name string
	 *
	 * @return void
	 */
	public function del($name);

	/**
	 * Добавляет маршрут по ключу
	 *
	 * @param $name string
	 * @param $pattern string
	 *
	 * @return RouterAdd()
	 */
	public function add($name, $pattern);

	/**
	 * Добавляет массив маршрутов
	 *
	 * @param $routes array
	 *
	 * @return void
	 */
	public function addMultiple($routes);
}