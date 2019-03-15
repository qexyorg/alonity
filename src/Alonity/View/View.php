<?php
/**
 * View component of Alonity Framework
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

use Framework\Alonity\DI\DI;
use Framework\Alonity\Keys\Key;
use Framework\Alonity\Router\Router;
use Framework\Alonity\Router\RouterHelper;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class View extends Router implements ViewInterface {

	private $rootDir = null;

	private $viewCache = [];

	private $defaultPublicDir = null;

	private function getRoot(){
		if(!is_null($this->rootDir)){
			return $this->rootDir;
		}

		$this->rootDir = DI::get('ALONITY')->getRoot();

		return $this->rootDir;
	}

	public function getDefaultPublicDir(){
		if(!is_null($this->defaultPublicDir)){
			return $this->defaultPublicDir;
		}

		$application = RouterHelper::getApp();

		$this->defaultPublicDir = $this->getRoot().$application['default_public_dir'];

		return $this->defaultPublicDir;
	}

	public function setDefaultPublicDir($dir){
		$this->defaultPublicDir = $dir;
	}

	private function load($filename, $data, $defaultDir=null){

		if(is_null($defaultDir)){
			$defaultDir = $this->getDefaultPublicDir();
		}

		$filename = "{$defaultDir}{$filename}";

		if(!file_exists($filename) || !is_file($filename)){
			return '';
		}

		if(!empty($data)){
			extract($data, EXTR_PREFIX_INVALID, '_');
		}

		ob_start();

		include($filename);

		return ob_get_clean();
	}

	public function getView($filename, $data=[], $defaultDir=null){
		$key = Key::make([__METHOD__, $filename, $data, $defaultDir]);

		if(isset($this->viewCache[$key])){ return $this->viewCache[$key]; }

		if(is_null($defaultDir)){
			$defaultDir = $this->getDefaultPublicDir();
		}

		$this->viewCache[$key] = $this->load($filename, $data, $defaultDir);

		return $this->viewCache[$key];
	}

	/**
	 * Выводит содержимое шаблона на экран
	 *
	 * @param $filename string
	 * @param $data array
	 * @param $defaultDir string|null
	 *
	 * @return void
	 */
	public function writeView($filename, $data=[], $defaultDir=null){
		echo $this->getView($filename, $data, $defaultDir);
	}

	/**
	 * Возвращает содержимое шаблона
	 *
	 * @param $filename string
	 * @param $data array
	 * @param $defaultDir string|null
	 *
	 * @return string
	*/
	public function getViewTpl($filename, $data=[], $defaultDir=null){
		$key = Key::make([__METHOD__, $filename, $data, $defaultDir]);

		if(isset($this->viewCache[$key])){ return $this->viewCache[$key]; }

		if(is_null($defaultDir)){
			$defaultDir = $this->getDefaultPublicDir();
		}

		$filename = "{$defaultDir}.{$filename}";

		if(!file_exists($filename) || !is_file($filename)){
			return '';
		}

		$content = file_get_contents($filename);

		$this->viewCache[$key] = $content;

		return $this->viewCache[$key];
	}

	/**
	 * Выводит содержимое шаблона на экран
	 *
	 * @param $filename string
	 * @param $data array
	 * @param $defaultDir string|null
	 *
	 * @return void
	 */
	public function writeViewTpl($filename, $data=[], $defaultDir=null){
		echo $this->getViewTpl($filename, $data, $defaultDir);
	}

	/**
	 * Возвращает содержимое в виде JSON строки
	 *
	 * @param $params mixed
	 *
	 * @return string
	*/
	public function getJson($params){

		if(!is_array($params) && !is_object($params)){
			return '';
		}

		return json_encode($params);
	}

	/**
	 * Выводит содержимое в виде JSON строки
	 *
	 * @param $params mixed
	 *
	 * @return void
	 */
	public function writeJson($params){
		echo $this->getJson($params);
	}

	/**
	 * @return \Twig_Loader_Filesystem
	 */
	public function getTemplaterLoader(){
		if(DI::has('TWIG_LOADER')){
			return DI::get('TWIG_LOADER');
		}

		return DI::set('TWIG_LOADER', new FilesystemLoader());
	}

	/**
	 * @return Environment
	 */
	public function getTemplater(){
		if(DI::has('TWIG_ENV')){
			return DI::get('TWIG_ENV');
		}

		$application = RouterHelper::getApp();

		return DI::set('TWIG_ENV', new Environment($this->getTemplaterLoader(), [
			'cache' => ($application['templater_cache']===false) ? $application['templater_cache'] : dirname(dirname(__DIR__)).$application['templater_cache']
		]));
	}
}

?>