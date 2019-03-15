<?php
/**
 * Triggers of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.0.0
 */

namespace Framework\Alonity\Triggers;

use Framework\Alonity\DI\DI;
use Framework\Alonity\Keys\Key;

trait Triggers {

	private $triggers = [];

	/**
	 * @param $name string
	 * @param $params mixed
	 *
	 * @throws TriggersException
	 *
	 * @return mixed
	*/
	public function callToTrigger($name, $params=null){

		$key = Key::make([__METHOD__, $name, $params]);

		if(isset($this->triggers[$key])){
			return $this->triggers[$key]->call($params);
		}

		$appname = $this->getKey();

		$triggers_path = DI::get('ALONITY')->getRoot()."/Applications/{$appname}/Triggers/";

		if(!file_exists($triggers_path)){
			return false;
		}

		$filename = $triggers_path.$name.'.php';

		if(!file_exists($filename)){
			return false;
		}

		$classname = "App\\{$appname}\\Triggers\\{$name}";

		if(!class_exists($classname)){
			throw new TriggersException("Class \"$name\" not found");
		}

		if(!method_exists($classname, 'call')){
			throw new TriggersException("Method \"call\" not found in class \"$name\"");
		}

		$this->triggers[$key] = new $classname();

		return $this->triggers[$key]->call($params);
	}
}

?>