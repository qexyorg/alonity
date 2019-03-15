<?php
/**
 * Database PostgreSQL Delete component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.0.0
 */

namespace Framework\Components\Database\PostgreSQL;

use Framework\Components\Database\DatabaseException;

class Delete {

	const WHERE_AND = 0x538;
	const WHERE_OR = 0x539;

	const point = '?';

	private $sql = null;

	private $result = null;

	private $obj = null;

	private $from = '';

	private $where = [];

	private $limit = 0;

	private $offset = 0;

	private $order = null;

	public function __construct($obj){
		$this->obj = $obj;
	}

	private function changeQuotes($value){
		return str_replace('`', '"', $value);
	}

	/**
	 * Имя таблицы, которая будет использоваться для удаления
	 *
	 * @param $table string
	 *
	 * @example 'my_table' returned "my_table"
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function from($table){

		if(empty($table)){
			throw new DatabaseException('from must be not empty');
		}

		if(!is_string($table)){
			throw new DatabaseException('from must be a string');
		}

		$this->from = $table;

		return $this;
	}

	/**
	 * Ограничение кол-ва удаляемых строк
	 *
	 * @param $limit integer
	 *
	 * @return $this
	 */
	public function limit($limit){
		$this->limit = $limit;

		return $this;
	}

	/**
	 * Выставляет смещение ограничения удаляемого результата
	 *
	 * @param $offset integer
	 *
	 * @example 10 returned OFFSET 10
	 *
	 * @return $this
	 */
	public function offset($offset){
		$this->offset = $offset;

		return $this;
	}

	/**
	 * Условия
	 *
	 * @param $where array
	 * @param $values array
	 * @param $type integer
	 *
	 * @example ["name=?", "`id`>='?'", "`id`<=3"],  returned name=?, "id">='?', "id"<=3
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function where($where, $values=[], $type=0x538){

		if(empty($where)){
			throw new DatabaseException("param where must be not empty");
		}

		if(!is_array($where)){
			throw new DatabaseException('param where must be array');
		}

		if(!is_array($values)){
			throw new DatabaseException("param values must be array");
		}

		if(!is_integer($type)){
			throw new DatabaseException("param type must be a const");
		}

		$this->where[] = [
			'where' => $where,
			'values' => $values,
			'type' => $type
		];

		return $this;
	}

	/**
	 * Выставляет порядок сортировки
	 *
	 * @param $params array
	 *
	 * @example array('id') returned ORDER BY `id` ASC
	 * @example array('id' => 'desc') returned ORDER BY `id` DESC
	 * @example array('id' => 'desc', 'name' => 'ASC') returned ORDER BY `id` DESC, `name` ASC
	 * @example array('id' => 'desc', 'name') returned ORDER BY `id` DESC, `name` ASC
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function order($params){
		if(!is_array($params) && !is_string($params)){
			throw new DatabaseException("order params must be array or string");
		}

		$this->order = $params;

		return $this;
	}

	private function filterFrom($from){
		if(empty($from)){
			throw new DatabaseException("from must be not empty");
		}

		if(!is_string($from)){
			throw new DatabaseException("from must be a string");
		}

		return "\"$from\"";
	}

	private function filterWhere($where){

		$result = "";

		if(empty($where)){ return ""; }

		if(!is_array($where)){
			throw new DatabaseException("where must be array");
		}

		foreach($where as $ar){
			$ar['where'] = array_map([$this, 'changeQuotes'], $ar['where']);

			if($ar['type']==self::WHERE_OR){
				$result .= (empty($result)) ? implode(' OR ', $ar['where']) : " OR ".implode(' OR ', $ar['where']);
			}else{
				$result .= (empty($result)) ? implode(' AND ', $ar['where']) : " AND ".implode(' AND ', $ar['where']);
			}

			$count = mb_substr_count($result, self::point, 'UTF-8');

			if($count!=sizeof($ar['values'])){
				throw new DatabaseException("params where and values is not complete");
			}

			foreach($ar['values'] as $value){
				$pos = mb_strpos($result, self::point, 0, 'UTF-8');

				if($pos===false){ continue; }

				$value = @pg_escape_string($this->obj, $value);

				$len = mb_strlen($result, 'UTF-8');

				$result = mb_substr($result, 0, $pos, 'UTF-8').$value.mb_substr($result, $pos+1, $len, 'UTF-8');
			}
		}

		return empty($result) ? "" : "WHERE $result";
	}

	private function filterLimit($limit){

		$limit = intval($limit);

		if(empty($limit)){
			return "";
		}

		return "LIMIT $limit";
	}

	private function filterOffset($offset){

		$offset = intval($offset);

		if(empty($offset)){
			return "";
		}

		return "OFFSET $offset";
	}

	private function filterOrder($order){

		$result = "";

		if(empty($order)){
			return $result;
		}

		if(!is_array($order) && !is_string($order)){
			throw new DatabaseException("order must be array or string");
		}

		if(is_string($order)){
			$result = "`$order`";
		}else{
			foreach($order as $k => $v){

				if(!is_array($v)){
					$by = (strtolower($v)!='desc') ? 'ASC' : 'DESC';

					$result .= "$k $by,";
				}else{
					$by = (strtolower($v[0])!='desc') ? 'ASC' : 'DESC';

					unset($v[0]);

					$v = array_map(function($value){ return "'$value'"; }, $v);

					$props = implode(", ", $v);

					$result .= "FIELD($k, $props) $by,";
				}
			}

			$result = mb_substr($result, 0, -1, 'UTF-8');
		}

		return "ORDER BY $result";
	}

	public function getError(){
		return pg_last_error($this->obj);
	}

	public function getDeletedNum(){
		return pg_affected_rows($this->result);
	}

	/**
	 * Возвращает SQL запрос
	 *
	 * @return string
	 */
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

		$order = $this->filterOrder($this->order);

		$limit = $this->filterLimit($this->limit);

		$offset = $this->filterOffset($this->offset);

		return "DELETE FROM $from $where $order $limit $offset";
	}

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @return boolean
	 */
	public function execute(){

		$sql = $this->getSQL();

		$this->result = pg_query($this->obj, $sql);

		if(!$this->result){
			return false;
		}

		return true;
	}
}

?>