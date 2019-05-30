<?php
/**
 * Database PostgreSQL Transaction component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Components\Database\PostgreSQL;

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
		return pg_last_error($this->obj);
	}

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @return boolean
	 */
	public function execute(){

		@pg_query($this->obj, "BEGIN");

		$rollback = false;

		foreach($this->add as $sql){
			if(!pg_query($this->obj, $sql)){
				$rollback = true;
			}
		}

		if($rollback){
			@pg_query($this->obj, "ROLLBACK"); return false;
		}

		return @pg_query($this->obj, "COMMIT");
	}
}

?>