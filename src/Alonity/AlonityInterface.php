<?php
/**
 * Alonity Framework Interface
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Alonity;

interface AlonityInterface {

	/**
	 * Возвращает директорию корня сайта
	 *
	 * @return string
	 */
	public function getRoot();

	/**
	 * Получение массива параметров приложения
	 *
	 * @return array
	 */
	public function getApp();

	/**
	 * Возвращает версию приложения
	 *
	 * @return string
	 */
	public function getVersion();

	/**
	 * Возвращает информацию о приложении
	 *
	 * @return string
	 */
	public function getAbout();

	/**
	 * Возвращает автора приложения
	 *
	 * @return string
	 */
	public function getAuthor();

	/**
	 * Возвращает автора приложения
	 *
	 * @return string
	 */
	public function getSite();

	/**
	 * Возвращает ключ приложения
	 *
	 * @return string
	 */
	public function getKey();

	/**
	 * Возвращает имя приложения
	 *
	 * @deprecated
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Возвращает путь к главному файлу приложения
	 *
	 * @return string
	 */
	public function getFilename();

	/**
	 * Возвращает пространство имен приложения
	 *
	 * @return string
	 */
	public function getNamespace();

	/**
	 * Возвращает имя класса приложения
	 *
	 * @return string
	 */
	public function getClass();

	/**
	 * Возвращает имя метода активатора приложения
	 *
	 * @return string
	 */
	public function getMethod();

	/**
	 * Возвращает экземпляр класса приложения
	 *
	 * @return string
	 */
	public function getAppObject();

	public function run();
}

?>