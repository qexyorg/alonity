<?php
/**
 * Database MySQL Update component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Alonity\Components\Database\MySQL;

class MySQLUpdateException extends \Exception {}

class Update {

	const WHERE_AND = 0x538;
	const WHERE_OR = 0x539;

	const point = '?';

	private $sql = null;

	private $table = '';

	private $set = [];

	private $where = [];

	private $limit = [];

	public function __construct($obj){
		$this->sql = "";
		$this->table = "";
		$this->set = [];
		$this->where = [];

		$this->obj = $obj;
	}

	/**
	 * Имя таблицы, которая будет использоваться для обновления
	 *
	 * @param $table string
	 *
	 * @example 'my_table' returned `my_table`
	 *
	 * @throws MySQLUpdateException
	 *
	 * @return \Alonity\Components\Database\MySQL\Update()
	 */
	public function table($table){

		if(empty($table)){
			throw new MySQLUpdateException('table must be not empty');
		}

		if(!is_string($table)){
			throw new MySQLUpdateException('table must be a string');
		}

		$this->table = $table;

		return $this;
	}

	/**
	 * Ограничение кол-ва обновляемых строк
	 *
	 * @param $end integer
	 * @param $offset integer
	 *
	 * @throws MySQLUpdateException
	 *
	 * @return \Alonity\Components\Database\MySQL\Update()
	 */
	public function limit($end, $offset=0){
		$end = intval($end);
		$offset = intval($offset);

		if($end<=0){ return $this; }

		$this->limit = [$end, $offset];

		return $this;
	}

	/**
	 * Обновляемые колонки и их значения
	 *
	 * @param $set array
	 *
	 * @example ['name' => 'hello', '`desc`' => 'world'] returned name='hello',`desc`='world'
	 *
	 * @throws MySQLUpdateException
	 *
	 * @return \Alonity\Components\Database\MySQL\Update()
	 */
	public function set($set){

		if(empty($set)){
			throw new MySQLUpdateException('set must be not empty');
		}

		if(!is_array($set)){
			throw new MySQLUpdateException('set must be array');
		}

		$this->set = array_replace_recursive($this->set, $set);

		return $this;
	}

	/**
	 * Условия
	 *
	 * @param $where array
	 * @param $values array
	 * @param $type integer
	 *
	 * @example ["name=?", "`id`>='?'", "`id`<=3"],  returned name=?, "`id`>='?'", "`id`<=3"
	 *
	 * @throws MySQLUpdateException
	 *
	 * @return \Alonity\Components\Database\MySQL\Update()
	 */
	public function where($where, $values=[], $type=0x538){

		if(!is_array($where)){
			throw new MySQLUpdateException('param where must be array');
		}

		if(!is_array($values)){
			throw new MySQLUpdateException("param values must be array");
		}

		if(!is_integer($type)){
			throw new MySQLUpdateException("param type must be a const");
		}

		$this->where[] = [
			'where' => $where,
			'values' => $values,
			'type' => $type
		];

		return $this;
	}

	private function filterTable($table){
		if(empty($table)){
			throw new MySQLUpdateException("table must be not empty");
		}

		if(!is_string($table)){
			throw new MySQLUpdateException("table must be a string");
		}

		return "`$table`";
	}

	private function filterSet($set){
		if(empty($set)){
			throw new MySQLUpdateException("columns must be not empty");
		}

		if(!is_array($set)){
			throw new MySQLUpdateException("columns must be array");
		}

		$result = "";

		foreach($set as $k => $v){
			$v = @mysql_escape_string($v);

			$result .= "$k='$v',";
		}

		$result = mb_substr($result, 0, -1, 'UTF-8');

		return "SET $result";
	}

	private function filterWhere($where){

		$result = "";

		if(empty($where)){ return $result; }

		if(empty($where)){
			throw new MySQLUpdateException("where must be not empty");
		}

		if(!is_array($where)){
			throw new MySQLUpdateException("where must be array");
		}

		foreach($where as $ar){
			if($ar['type']==self::WHERE_OR){
				$result .= (empty($result)) ? implode(' OR ', $ar['where']) : " OR ".implode(' OR ', $ar['where']);
			}else{
				$result .= (empty($result)) ? implode(' AND ', $ar['where']) : " AND ".implode(' AND ', $ar['where']);
			}

			$count = mb_substr_count($result, self::point, 'UTF-8');

			if($count!=sizeof($ar['values'])){
				throw new MySQLUpdateException("params where and values is not complete");
			}

			foreach($ar['values'] as $value){
				$pos = mb_strpos($result, self::point, 0, 'UTF-8');

				if($pos===false){ continue; }

				$value = @mysql_escape_string($value);

				$len = mb_strlen($result, 'UTF-8');

				$result = mb_substr($result, 0, $pos, 'UTF-8').$value.mb_substr($result, $pos+1, $len, 'UTF-8');
			}
		}

		return empty($result) ? "" : "WHERE $result";
	}

	private function filterLimit($limit){
		if(empty($limit)){ return ""; }

		if(!is_array($limit) || !isset($limit[1])){
			throw new MySQLUpdateException("limit must be array");
		}

		$end = intval($limit[0]);
		$offset = intval($limit[1]);

		if($end<=0){ return ""; }

		if($offset<=0){
			$offset = 0;
		}

		return "LIMIT $offset, $end";
	}

	public function getError(){
		return mysql_error($this->obj);
	}

	public function getSQL(){
		return $this->sql;
	}

	public function getUpdatedNum(){
		return mysql_affected_rows($this->obj);
	}

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @return boolean
	 */
	public function execute(){

		$table = $this->filterTable($this->table);

		$set = $this->filterSet($this->set);

		$where = $this->filterWhere($this->where);

		$limit = $this->filterLimit($this->limit);

		$this->sql = "UPDATE $table $set $where $limit";

		$this->result = mysql_query($this->sql, $this->obj);

		if($this->result===false){
			return false;
		}

		return true;
	}
}

?>