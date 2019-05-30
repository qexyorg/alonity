<?php
/**
 * Alerts component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Components\Alerts;

class Alerts {

	const HTTP_LOGIC = 0x00;

	const JSON_LOGIC = 0x01;

	private static $session_name = 'alonity_alerts_data';

	public static function get($name=null, $remove=true){
		if(!isset($_SESSION)){
			session_start();
		}

		$name = (is_null($name)) ? self::$session_name : $name;

		if(!isset($_SESSION[$name])){
			return [];
		}

		$alerts = $_SESSION[$name];

		if($remove){
			unset($_SESSION[$name]);
		}

		return $alerts;
	}

	public static function set($name=null){
		$name = (is_null($name)) ? self::$session_name : $name;

		return new Set($name);
	}
}

?>