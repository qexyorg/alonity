<?php
/**
 * Database MySQL Transaction component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Components\Database\MySQL;

use Framework\Components\Database\DeleteInterface;
use Framework\Components\Database\InsertInterface;
use Framework\Components\Database\SelectInterface;
use Framework\Components\Database\TransactionInterface;
use Framework\Components\Database\UpdateInterface;

class Transaction implements TransactionInterface {

	private $obj = null;

	private $add = [];

	public function __construct($obj){
		$this->obj = $obj;
	}

	/**
	 * Добавляет запросы типов Delete/Update/Insert/Select в тело транзакции
	 *
	 * @param $object DeleteInterface|UpdateInterface|InsertInterface|SelectInterface
	 *
	 * @return $this
	 */
	public function add($object){

		$this->add[] = $object->getSQL();

		return $this;
	}

	/**
	 * Возвращает последнюю ошибку результата запроса
	 *
	 * @return string
	 */
	public function getError(){
		return mysql_error($this->obj);
	}

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @return boolean
	 */
	public function execute(){

		@mysql_query("SET AUTOCOMMIT=0", $this->obj);

		@mysql_query("START TRANSACTION", $this->obj);

		@mysql_query("BEGIN", $this->obj);

		$rollback = false;

		foreach($this->add as $sql){
			if(!mysql_query($sql, $this->obj)){
				$rollback = true;
			}
		}

		if($rollback){
			@mysql_query("ROLLBACK", $this->obj); return false;
		}

		return @mysql_query("COMMIT", $this->obj);
	}
}

?>