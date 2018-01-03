<?php
/**
 * Database MySQLi Select component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.1
 */

namespace Alonity\Components\Database\MySQLi;

class MySQLiSelectException extends \Exception {}

class Select {

	const WHERE_AND = 0x538;
	const WHERE_OR = 0x539;

	const point = '?';

	private $sql = null;

	private $from = [];

	private $where = [];

	private $limit = null;

	private $order = null;

	private $obj = null;

	private $columns = [];

	private $group = [];

	private $result = null;

	private $join = [];

	private $jointypes = ['inner', 'left', 'right'];

	public function __construct($obj){
		$this->sql = "";
		$this->from = [];
		$this->where = [];
		$this->whereor = [];
		$this->limit = null;
		$this->order = null;
		$this->columns = [];
		$this->group = [];

		$this->join = [];

		/**
		 * @return \mysqli
		*/
		$this->obj = $obj;
	}

	/**
	 * Выставляет таблицу из которой будет извлечен результат
	 *
	 * @param $table array | string
	 *
	 * @example 'my_table' returned `my_table`
	 * @example array('t' => 'my_table') returned `t`.`my_table`
	 *
	 * @throws MySQLiSelectException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Select()
	*/
	public function from($table=[]){

		if(empty($table)){
			throw new MySQLiSelectException('from must be not empty');
		}

		if(!is_array($table) && !is_string($table)){
			throw new MySQLiSelectException('from must be array or string');
		}

		$this->from = $table;

		return $this;
	}

	/**
	 * Выставляет условия AND по которым будет извлечен результат
	 *
	 * @param $where array
	 * @param $values array
	 * @param $type integer
	 *
	 * @example array("`t`.`id`='?'" => 3, "`t`.`name`='?'" => 'hello') returned AND `t`.`id`='3' AND `t`.`name`='hello'
	 * @example array("`t`.`name`='?-?'" => 'hello') returned AND `t`.`name`='hello-hello'
	 * @example array("`t`.`name`='?-?'" => ['hello', 'world']) returned AND `t`.`name`='hello-world'
	 *
	 * @throws MySQLiSelectException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Select()
	 */
	public function where($where, $values=[], $type=0x538){

		if(!is_array($where)){
			throw new MySQLiSelectException('param where must be array');
		}

		if(!is_array($values)){
			throw new MySQLiSelectException("param values must be array");
		}

		if(!is_integer($type)){
			throw new MySQLiSelectException("param type must be a const");
		}

		$this->where[] = [
			'where' => $where,
			'values' => $values,
			'type' => $type
		];

		return $this;
	}

	/**
	 * Выставляет ограничения возвращаемых результатов
	 *
	 * @param $limit array | integer
	 *
	 * @example 10 returned LIMIT 10
	 * @example array(10, 20) returned LIMIT 10, 20
	 *
	 * @throws MySQLiSelectException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Select()
	 */
	public function limit($limit){
		$this->limit = $limit;

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
	 * @throws MySQLiSelectException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Select()
	 */
	public function order($params){
		if(!is_array($params) && !is_string($params)){
			throw new MySQLiSelectException("order params must be array or string");
		}

		$this->order = $params;

		return $this;
	}

	/**
	 * Выставляет поля для выборки. Повторное использование в одном экземпляре Select объединяет колонки
	 *
	 * @param $columns array
	 *
	 * @example ['`example`', '`t`.`test`'] returned SELECT `example`, `t`.`test`
	 *
	 * @throws MySQLiSelectException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Select()
	 */
	public function columns($columns){

		if(empty($columns)){
			throw new MySQLiSelectException('columns must be not empty');
		}

		if(!is_array($columns)){
			throw new MySQLiSelectException('columns must be array');
		}

		$this->columns = array_replace_recursive($this->columns, $columns);

		return $this;
	}

	/**
	 * Группирует результаты запроса по выбранным колонкам
	 *
	 * @param $columns array
	 *
	 * @throws MySQLiSelectException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Select()
	 */
	public function group($columns){

		if(empty($columns)){
			throw new MySQLiSelectException('group must be not empty');
		}

		if(!is_array($columns)){
			throw new MySQLiSelectException('group must be array');
		}

		$this->group = array_replace_recursive($this->group, $columns);

		return $this;
	}

	/**
	 * Создает вхождение в таблицу используя оператор LEFT JOIN
	 *
	 * @param $table string
	 * @param $alias string
	 * @param $columns array
	 * @param $values array
	 * @param $type integer
	 *
	 * @throws MySQLiSelectException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Select()
	 */
	public function leftjoin($table, $alias='', $columns=[], $values=[], $type=0x538){
		return $this->_join('left', $table, $alias, $columns, $values, $type);
	}

	/**
	 * Создает вхождение в таблицу используя оператор RIGHT JOIN
	 *
	 * @param $table string
	 * @param $alias string
	 * @param $columns array
	 * @param $values array
	 * @param $type integer
	 *
	 * @throws MySQLiSelectException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Select()
	 */
	public function rightjoin($table, $alias='', $columns=[], $values=[], $type=0x538){
		return $this->_join('right', $table, $alias, $columns, $values, $type);
	}

	/**
	 * Создает вхождение в таблицу используя оператор INNER JOIN
	 *
	 * @param $table string
	 * @param $alias string
	 * @param $columns array
	 * @param $values array
	 * @param $type integer
	 *
	 * @throws MySQLiSelectException
	 *
	 * @return \Alonity\Components\Database\MySQLi\Select()
	 */
	public function innerjoin($table, $alias='', $columns=[], $values=[], $type=0x538){
		return $this->_join('inner', $table, $alias, $columns, $values, $type);
	}

	private function _join($jointype='', $table, $alias='', $columns=[], $values=[], $type=0x538){

		if(!empty($jointype) && !in_array($jointype, $this->jointypes)){
			throw new MySQLiSelectException("unexpected jointype");
		}

		if(empty($table) || !is_string($table)){
			throw new MySQLiSelectException("join param table must be a string");
		}

		if(!is_string($alias)){
			throw new MySQLiSelectException("join param alias must be a string");
		}

		if(empty($columns)){
			throw new MySQLiSelectException("join param columns must be not empty");
		}

		if(!is_array($columns)){
			throw new MySQLiSelectException("join param columns must be array");
		}

		if(!is_array($values)){
			throw new MySQLiSelectException("join param values must be array");
		}

		$this->join[] = [
			'jointype' => $jointype,
			'table' => $table,
			'alias' => (is_string($alias) && !empty($alias)) ? $alias : null,
			'columns' => $columns,
			'values' => $values,
			'type' => $type
		];

		return $this;
	}

	private function filterJoin($join){
		$result = "";

		if(empty($join)){
			return $result;
		}

		foreach($join as $ar){
			if(!empty($ar['jointype']) && !in_array($ar['jointype'], $this->jointypes)){
				throw new MySQLiSelectException("unexpected jointype");
			}

			if(empty($ar['table']) || !is_string($ar['table'])){
				throw new MySQLiSelectException("join param table must be a string");
			}

			if(!is_string($ar['alias'])){
				throw new MySQLiSelectException("join param alias must be a string");
			}

			if(!isset($ar['columns']) || empty($ar['columns'])){
				throw new MySQLiSelectException("join param columns must be not empty");
			}

			if(!is_array($ar['columns'])){
				throw new MySQLiSelectException("join param columns must be array");
			}

			if(!is_array($ar['values'])){
				throw new MySQLiSelectException("join param values must be array");
			}

			$jointype = strtoupper($ar['jointype']);

			$as = (is_null($ar['alias'])) ? "" : "AS `{$ar['alias']}`";

			$result .= "$jointype JOIN `{$ar['table']}` $as ON ";

			if($ar['type']==self::WHERE_OR){
				$result .= implode(' OR ', $ar['columns']);
			}else{
				$result .= implode(' AND ', $ar['columns']);
			}

			$count = mb_substr_count($result, self::point, 'UTF-8');

			if($count!=sizeof($ar['values'])){
				throw new MySQLiSelectException("params columns and values is not complete");
			}

			foreach($ar['values'] as $value){
				$pos = mb_strpos($result, self::point, 0, 'UTF-8');

				if($pos===false){ continue; }

				$value = $this->obj->escape_string($value);

				$len = mb_strlen($result, 'UTF-8');

				$result = mb_substr($result, 0, $pos, 'UTF-8').$value.mb_substr($result, $pos+1, $len, 'UTF-8');
			}
		}

		return $result;
	}

	private function filterGroup($columns){

		if(empty($columns)){ return ""; }

		if(!is_array($columns)){
			throw new MySQLiSelectException("group must be array");
		}

		return 'GROUP BY '.implode(', ', $columns);
	}

	private function filterFrom($from){
		if(empty($from)){
			throw new MySQLiSelectException("from must be not empty");
		}

		if(is_array($from)){
			$as = key($from);

			if(sizeof($from)!=1 || !is_string($as)){
				throw new MySQLiSelectException("from array key must be a string");
			}

			$result = "FROM `{$from[$as]}` AS `$as`";
		}elseif(is_string($from)){
			$result = "FROM `{$from}`";
		}else{
			throw new MySQLiSelectException("from must be array or string");
		}

		return $result;
	}

	private function filterColumns($columns){

		if(empty($columns)){ return "*"; }

		if(!is_array($columns)){
			throw new MySQLiSelectException("columns must be array");
		}

		$items = [];

		foreach($columns as $k => $v){
			if(is_int($k)){
				$items[] = "$v";
			}else{
				$items[] = "$k AS $v";
			}
		}

		return implode(', ', $items);
	}

	private function filterWhere($where){
		$result = "";

		if(empty($where)){ return $result; }

		if(empty($where)){
			throw new MySQLiSelectException("where must be not empty");
		}

		if(!is_array($where)){
			throw new MySQLiSelectException("where must be array");
		}

		foreach($where as $ar){
			if($ar['type']==self::WHERE_OR){
				$result .= (empty($result)) ? implode(' OR ', $ar['where']) : " OR ".implode(' OR ', $ar['where']);
			}else{
				$result .= (empty($result)) ? implode(' AND ', $ar['where']) : " AND ".implode(' AND ', $ar['where']);
			}

			$count = mb_substr_count($result, self::point, 'UTF-8');

			if($count!=sizeof($ar['values'])){
				throw new MySQLiSelectException("params where and values is not complete");
			}

			foreach($ar['values'] as $value){
				$pos = mb_strpos($result, self::point, 0, 'UTF-8');

				if($pos===false){ continue; }

				$value = $this->obj->escape_string($value);

				$len = mb_strlen($result, 'UTF-8');

				$result = mb_substr($result, 0, $pos, 'UTF-8').$value.mb_substr($result, $pos+1, $len, 'UTF-8');
			}
		}

		return empty($result) ? "" : "WHERE $result";
	}

	private function filterLimit($limit){
		$result = "";

		if(empty($limit)){
			return $result;
		}

		if(!is_array($limit) && !is_int($limit)){
			throw new MySQLiSelectException("limit must be array or integer");
		}

		if(is_array($limit)){
			$num = intval(key($limit));
			$offset = intval($limit[$num]);

			$result = "LIMIT $num, $offset";
		}else{
			$limit = intval($limit);
			$result = "LIMIT $limit";
		}

		return $result;
	}

	private function filterOrder($order){

		$result = "";

		if(empty($order)){
			return $result;
		}

		if(!is_array($order) && !is_string($order)){
			throw new MySQLiSelectException("order must be array or string");
		}

		if(is_string($order)){
			$result = "`$order`";
		}else{
			foreach($order as $k => $v){
				$by = (strtolower($v)!='desc') ? 'ASC' : 'DESC';

				$result .= "$k $by,";
			}

			$result = mb_substr($result, 0, -1, 'UTF-8');
		}

		return "ORDER BY $result";
	}

	public function getError(){
		return $this->obj->error;
	}

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @return boolean
	 */
	public function execute(){

		$columns = $this->filterColumns($this->columns);

		$from = $this->filterFrom($this->from);

		$join = $this->filterJoin($this->join);

		$where = $this->filterWhere($this->where);

		$group = $this->filterGroup($this->group);

		$order = $this->filterOrder($this->order);

		$limit = $this->filterLimit($this->limit);

		$this->sql = "SELECT $columns $from $join $where $group $order $limit";

		$this->result = $this->obj->query($this->sql);

		if($this->result===false){
			return false;
		}

		return true;
	}

	public function getArray(){
		$result = [];

		if(is_null($this->result) || $this->result===false){
			return $result;
		}

		return $this->result->fetch_all(MYSQLI_NUM);
	}

	public function getAssoc(){
		$result = [];

		if(is_null($this->result) || $this->result===false){
			return $result;
		}

		return $this->result->fetch_all(MYSQLI_ASSOC);
	}

	public function getNum(){
		if(is_null($this->result) || $this->result===false){
			return 0;
		}

		return $this->result->num_rows;
	}
}

?>