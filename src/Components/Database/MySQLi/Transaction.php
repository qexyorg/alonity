<?php
/**
 * Database MySQLi Transaction component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Components\Database\MySQLi;

use Framework\Components\Database\DeleteInterface;
use Framework\Components\Database\InsertInterface;
use Framework\Components\Database\SelectInterface;
use Framework\Components\Database\TransactionInterface;
use Framework\Components\Database\UpdateInterface;

class Transaction implements TransactionInterface {

	/**
	 * @var \mysqli
	 */
	private $obj = null;

	private $add = [];

	public function __construct($obj){
		/**
		 * @return \mysqli
		 */
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
		return $this->obj->error;
	}

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @return boolean
	 */
	public function execute(){

		$this->obj->autocommit(false);

		$this->obj->begin_transaction(0, MYSQLI_TRANS_START_READ_WRITE);

		$rollback = false;

		foreach($this->add as $sql){
			if(!$this->obj->query($sql)){
				$rollback = true;
			}
		}

		if($rollback){
			$this->obj->rollback(); return false;
		}

		return $this->obj->commit();
	}
}

?>