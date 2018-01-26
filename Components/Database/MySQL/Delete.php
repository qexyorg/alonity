<?php
/**
 * Database MySQL Delete component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.1.0
 */

namespace Alonity\Components\Database\MySQL;

class MySQLDeleteException extends \Exception {}

class Delete {

	const WHERE_AND = 0x538;
	const WHERE_OR = 0x539;

	const point = '?';

	private $sql = null;

	private $from = '';

	private $where = [];

	private $result = false;

	private $limit = [];

	public function __construct($obj){

		$this->obj = $obj;
	}

	/**
	 * Имя таблицы, которая будет использоваться для удаления
	 *
	 * @param $table string
	 *
	 * @example 'my_table' returned `my_table`
	 *
	 * @throws MySQLDeleteException
	 *
	 * @return \Alonity\Components\Database\MySQL\Delete()
	 */
	public function from($table){

		if(empty($table)){
			throw new MySQLDeleteException('from must be not empty');
		}

		if(!is_string($table)){
			throw new MySQLDeleteException('from must be a string');
		}

		$this->from = $table;

		return $this;
	}

	/**
	 * Ограничение кол-ва удаляемых строк
	 *
	 * @param $end integer
	 * @param $offset integer
	 *
	 * @throws MySQLDeleteException
	 *
	 * @return \Alonity\Components\Database\MySQL\Delete()
	 */
	public function limit($end, $offset=0){
		$end = intval($end);
		$offset = intval($offset);

		if($end<=0){ return $this; }

		$this->limit = [$end, $offset];

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
	 * @throws MySQLDeleteException
	 *
	 * @return \Alonity\Components\Database\MySQL\Delete()
	 */
	public function where($where, $values=[], $type=0x538){

		if(empty($where)){
			throw new MySQLDeleteException("param where must be not empty");
		}

		if(!is_array($where)){
			throw new MySQLDeleteException('param where must be array');
		}

		if(!is_array($values)){
			throw new MySQLDeleteException("param values must be array");
		}

		if(!is_integer($type)){
			throw new MySQLDeleteException("param type must be a const");
		}

		$this->where[] = [
			'where' => $where,
			'values' => $values,
			'type' => $type
		];

		return $this;
	}

	private function filterFrom($from){
		if(empty($from)){
			throw new MySQLDeleteException("from must be not empty");
		}

		if(!is_string($from)){
			throw new MySQLDeleteException("from must be a string");
		}

		return "`$from`";
	}

	private function filterWhere($where){

		$result = "";

		if(empty($where)){ return ""; }

		if(!is_array($where)){
			throw new MySQLDeleteException("where must be array");
		}

		foreach($where as $ar){
			if($ar['type']==self::WHERE_OR){
				$result .= (empty($result)) ? implode(' OR ', $ar['where']) : " OR ".implode(' OR ', $ar['where']);
			}else{
				$result .= (empty($result)) ? implode(' AND ', $ar['where']) : " AND ".implode(' AND ', $ar['where']);
			}

			$count = mb_substr_count($result, self::point, 'UTF-8');

			if($count!=sizeof($ar['values'])){
				throw new MySQLDeleteException("params where and values is not complete");
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
			throw new MySQLDeleteException("limit must be array");
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

	public function getDeletedNum(){
		return mysql_affected_rows($this->obj);
	}

	public function getSQL(){

		if(!is_null($this->sql)){
			return $this->sql;
		}

		$this->sql = $this->compileSQL();

		return $this->sql;
	}

	private function compileSQL(){
		$from = $this->filterFrom($this->from);

		$where = $this->filterWhere($this->where);

		$limit = $this->filterLimit($this->limit);

		return "DELETE FROM $from $where $limit";
	}

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @return boolean
	 */
	public function execute(){

		$sql = $this->getSQL();

		$this->result = mysql_query($sql, $this->obj);

		if($this->result===false){
			return false;
		}

		return true;
	}
}

?>