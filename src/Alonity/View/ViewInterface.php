<?php
/**
 * View Interface of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.0.0
 */

namespace Framework\Alonity\View;

use Framework\Alonity\Router\RouterInterface;

interface ViewInterface extends RouterInterface {

	public function getDefaultPublicDir();

	public function setDefaultPublicDir($dir);

	public function getView($filename, $data=[], $defaultDir=null);

	/**
	 * Выводит содержимое шаблона на экран
	 *
	 * @param $filename string
	 * @param $data array
	 * @param $defaultDir string|null
	 *
	 * @return void
	 */
	public function writeView($filename, $data=[], $defaultDir=null);

	/**
	 * Возвращает содержимое шаблона
	 *
	 * @param $filename string
	 * @param $data array
	 * @param $defaultDir string|null
	 *
	 * @throws ViewException
	 *
	 * @return string
	 */
	public function getViewTpl($filename, $data=[], $defaultDir=null);

	/**
	 * Выводит содержимое шаблона на экран
	 *
	 * @param $filename string
	 * @param $data array
	 * @param $defaultDir string|null
	 *
	 * @return void
	 */
	public function writeViewTpl($filename, $data=[], $defaultDir=null);

	/**
	 * Возвращает содержимое в виде JSON строки
	 *
	 * @param $params mixed
	 *
	 * @throws ViewException
	 *
	 * @return string
	 */
	public function getJson($params);

	/**
	 * Выводит содержимое в виде JSON строки
	 *
	 * @param $params mixed
	 *
	 * @return void
	 */
	public function writeJson($params);
}