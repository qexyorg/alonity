<?php
/**
 * Database MySQL Insert component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.1.0
 */

namespace Framework\Components\Database\MySQL;

use Framework\Components\Database\DatabaseException;
use Framework\Components\Database\InsertInterface;

class Insert implements InsertInterface {

	private $sql = null;

	private $into = '';

	private $result = null;

	private $columns = [];

	private $values = [];

	private $insert_num = 0;

	private $obj = null;

	public function __construct($obj){
		$this->obj = $obj;
	}

	/**
	 * Имя таблицы, которая будет использоваться для вставки
	 *
	 * @param $table string
	 *
	 * @example 'my_table' returned `my_table`
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	*/
	public function into($table){

		if(empty($table)){
			throw new DatabaseException('into must be not empty');
		}

		if(!is_string($table)){
			throw new DatabaseException('into must be a string');
		}

		$this->into = $table;

		return $this;
	}

	/**
	 * Поля для вставки
	 *
	 * @param $columns array
	 *
	 * @example ['name', 'description'] returned (`name`, `description`)
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function columns($columns){

		if(empty($columns)){
			throw new DatabaseException('columns must be not empty');
		}

		if(!is_array($columns)){
			throw new DatabaseException('columns must be array');
		}

		$this->columns = array_replace_recursive($this->columns, $columns);

		return $this;
	}

	/**
	 * Значения для вставки
	 *
	 * @param $values array
	 *
	 * @example ['Hello', 'World'] returned ('Hello', 'World')
	 * @example [['Hello', 'World'], ['Example', 'Field']] returned ('Hello', 'World'), ('Hello', 'World')
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function values($values){

		if(empty($values)){
			throw new DatabaseException('values must be not empty');
		}

		if(!is_array($values)){
			throw new DatabaseException('values must be array');
		}

		$this->values = $values;

		return $this;
	}

	private function filterInto($table){

		if(empty($table)){
			throw new DatabaseException("into must be not empty");
		}

		if(!is_string($table)){
			throw new DatabaseException("into must be a string");
		}

		return "`$table`";
	}

	private function filterColumns($columns){
		if(empty($columns)){
			throw new DatabaseException("columns must be not empty");
		}

		if(!is_array($columns)){
			throw new DatabaseException("columns must be array");
		}

		$result = "";

		foreach($columns as $v){
			$column = @mysql_escape_string($v);

			$result .= "`$column`,";
		}

		$result = mb_substr($result, 0, -1, 'UTF-8');

		return "($result)";
	}

	private function filterValues($values){
		if(empty($values)){
			throw new DatabaseException("values must be not empty");
		}

		if(!is_array($values)){
			throw new DatabaseException("values must be array");
		}

		$assoc = false;

		foreach($values as $v){
			if(!is_array($v)){ continue; }

			$assoc = true;
		}

		$lines = "";

		$columns = sizeof($this->columns);

		if($assoc){

			foreach($values as $array){
				$items = "";

				if($columns!=sizeof($array)){
					throw new DatabaseException("columns size not equal values size");
				}

				foreach($array as $value){
					$value = @mysql_escape_string($value);

					$items .= "'$value',";
				}

				$this->insert_num++;

				$lines .= '('.mb_substr($items, 0, -1, 'UTF-8').'),';
			}
		}else{
			$items = "";

			if($columns!=sizeof($values)){
				throw new DatabaseException("columns size not equal values size");
			}

			foreach($values as $value){
				$value = @mysql_escape_string($value);

				$items .= "'$value',";
			}

			$this->insert_num++;

			$lines .= '('.mb_substr($items, 0, -1, 'UTF-8').'),';
		}

		return mb_substr($lines, 0, -1, 'UTF-8');
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
	 * Возвращает строку SQL запроса
	 *
	 * @param $last_id string|null
	 *
	 * @return string
	 */
	public function getSQL($last_id=null){

		if(!is_null($this->sql)){
			return $this->sql;
		}

		$this->sql = $this->compileSQL();

		return $this->sql;
	}

	private function compileSQL(){
		$into = $this->filterInto($this->into);

		$columns = $this->filterColumns($this->columns);

		$values = $this->filterValues($this->values);

		return "INSERT INTO $into $columns VALUES $values";
	}

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @param $last_id null
	 *
	 * @return boolean
	 */
	public function execute($last_id=null){

		$sql = $this->getSQL();

		$this->result = mysql_query($sql, $this->obj);

		if($this->result===false){
			$this->insert_num = 0;
			return false;
		}

		return true;
	}

	/**
	 * Возвращает кол-во вставленных записей
	 *
	 * @return integer
	 */
	public function getInsertNum(){
		return $this->insert_num;
	}

	/**
	 * Возвращает последний вставленный ID
	 *
	 * @return integer
	 */
	public function getLastID(){
		return mysql_insert_id($this->obj);
	}
}

?>