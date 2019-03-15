<?php
/**
 * Router add interface component of Alonity Framework
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

interface RouterAddInterface {

	/**
	 * Конструктор класса
	 * Принимает в качестве параметра - патерн
	 *
	 * @param $pattern string
	 */
	public function __construct($pattern);

	/**
	 * Методы приема маршрутом
	 *
	 * @param $methods array|string
	 *
	 * @return $this
	 */
	public function via($methods);

	/**
	 * Пространство имен маршрута
	 *
	 * @param $name string
	 *
	 * @return $this
	 */
	public function _namespace($name);

	/**
	 * Контроллер маршрута
	 *
	 * @param $name string
	 *
	 * @return $this
	 */
	public function controller($name);

	/**
	 * Исполняемый метод маршрута
	 *
	 * @param $name string
	 *
	 * @return $this
	 */
	public function action($name);

	/**
	 * Выставляет алиасы маршрута
	 *
	 * @param $pattern string
	 * @param $params array|null
	 *
	 * @return $this
	 */
	public function aliases($pattern, $params=null);

	/**
	 * Передаваемые маршрутом параметры
	 *
	 * @param $params array
	 *
	 * @return $this
	 */
	public function params($params);

	/**
	 * Метод, выполняющий сборку маршрута
	 *
	 * @return boolean
	 */
	public function execute();
}