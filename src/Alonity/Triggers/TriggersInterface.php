<?php
/**
 * Triggers interface of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Alonity\Triggers;

interface TriggersInterface {

	/**
	 * @param $params mixed
	 *
	 * @throws TriggersException
	 *
	 * @return mixed
	*/
	function call($params=null);
}

?>