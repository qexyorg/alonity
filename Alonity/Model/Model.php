<?php
/**
 * Model component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Alonity\Model;

use ModelException;

require_once(__DIR__.'/ModelException.php');

class Model {
	private $current = null;

	private $modelFile = null;

	private $route = null;

	/** @var \Alonity\Alonity */
	private $alonity = null;

	public function __construct($alonity){
		$this->alonity = $alonity;
	}

	/**
	 * Возвращает текущий массив маршрута
	 *
	 * @return array
	 */
	private function getRoute(){
		if(!is_null($this->route)){ return $this->route; }

		$this->route = $this->alonity->Router()->getCurrentRoute();

		return $this->route;
	}

	/**
	 * Возвращает текущий полный путь к модели
	 *
	 * @return string
	 */
	public function getFilename(){
		if(!is_null($this->modelFile)){ return $this->modelFile; }

		$route = $this->getRoute();

		$this->modelFile = $this->alonity->getRoot().'/Applications/'.$this->alonity->getAppKey();
		$this->modelFile .= '/Models/'.$route['baseClass'].'.php';

		return $this->modelFile;
	}

	/**
	 * Возвращает текущее имя экземпляра модели
	 *
	 * @return string
	 */
	public function getClassName(){
		$route = $this->getRoute();

		return $route['modelClass'];
	}

	/**
	 * Возвращает экземпляр текущей модели
	 *
	 * @throws ModelException
	 *
	 * @return object
	 */
	public function getCurrent(){
		if(!is_null($this->current)){ return $this->current; }

		$filename = $this->getFilename();

		if(!file_exists($filename)){
			throw new ModelException("File \"$filename\" not exists");
		}

		require_once($filename);

		$classname = $this->getClassName();

		if(!class_exists($classname)){
			throw new ModelException("Class \"$classname\" not found in \"$filename\"");
		}

		$this->current = new $classname($this->alonity);

		return $this->current;
	}
}

?>