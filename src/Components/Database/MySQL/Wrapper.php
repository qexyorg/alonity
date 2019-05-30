<?php
/**
 * Database MySQL wrapper component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.2.0
 */

namespace Framework\Components\Database\MySQL;

use Framework\Alonity\Keys\Key;
use Framework\Components\Database\DatabaseException;
use Framework\Components\Database\WrapperInterface;

class Wrapper implements WrapperInterface {

	private $options = [];

	private $connections = [];

	/**
	 * Создает соединение с базой MySQL
	 *
	 * @param $host string
	 * @param $database string
	 * @param $user string
	 * @param $password string
	 * @param $port integer|null
	 * @param $charset string|null
	 * @param $timeout integer
	 * @param $key mixed
	 *
	 * @throws DatabaseException
	 *
	 * @return object
	 */
	public function connect($host='127.0.0.1', $database='database', $user='root', $password='', $port=3306, $charset='utf8', $timeout=3, $key=null){

		$token = Key::make($key);

		if(isset($this->connections[$token]) && $this->connections[$token]){
			return $this->connections[$token];
		}

		ini_set('mysql.connect_timeout', $timeout);

		$connection = @mysql_connect("{$host}:{$port}", $user, $password, true);

		if(!$connection){
			throw new DatabaseException("MySQL connection error: ".mysql_error());
		}

		$this->connections[$token] = $connection;

		$this->connections[$token] = $this->setDB($database, $connection);

		$this->connections[$token] = $this->setCharset($charset, $connection);

		return $this->connections[$token];
	}

	/**
	 * Изменяет базу данных для работы
	 *
	 * @param $name string
	 * @param $obj mysql | null
	 *
	 * @throws DatabaseException
	 *
	 * @return mysql
	 */
	public function setDB($name, $obj=null){

		if(is_null($obj)){
			$obj = $this->getObj();
		}

		if(is_null($obj) || $obj===false){
			throw new DatabaseException("connection is not set");
		}

		if(empty($name)){
			throw new DatabaseException("database name must be not empty");
		}

		if(!is_string($name)){
			throw new DatabaseException("database name must be a string");
		}

		if(!@mysql_select_db($name, $obj)){
			throw new DatabaseException("change database error: ".mysql_error($obj));
		}

		return $obj;
	}

	/**
	 * Изменяет кодировку соединения
	 *
	 * @param $encoding string
	 * @param $obj mysql | null
	 *
	 * @throws DatabaseException
	 *
	 * @return mysql
	 */
	public function setCharset($encoding='utf8', $obj=null){

		if(is_null($obj)){
			$obj = $this->getObj();
		}

		if(is_null($obj) || $obj===false){
			throw new DatabaseException("connection is not set");
		}

		if(empty($encoding)){
			throw new DatabaseException("database encoding must be not empty");
		}

		if(!is_string($encoding)){
			throw new DatabaseException("database encoding must be a string");
		}

		if(!@mysql_set_charset($encoding, $obj)){
			throw new DatabaseException("change database charset error: ".mysql_error($obj));
		}

		return $obj;
	}

	/**
	 * Закрывает соединение с базой по ключу и удаляет линк
	 *
	 * @param $key integer
	 *
	 * @return boolean
	*/
	public function disconnect($key=0){

		$key = Key::make($key);

		if(!isset($this->connections[$key])){ return false; }

		@mysql_close($this->connections[$key]);

		unset($this->connections[$key]);

		return true;
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\MySQL\Select
	 *
	 * @throws DatabaseException
	 *
	 * @return Select
	 */
	public function select(){

		$obj = $this->getObj();

		if($obj===false){
			throw new DatabaseException("Object is false");
		}

		if(!class_exists('Framework\Components\Database\MySQL\Select')){
			throw new DatabaseException("Class Framework\\Components\\Database\\MySQL\\Select not found");
		}

		return new Select($obj);
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\MySQL\Insert
	 *
	 * @throws DatabaseException
	 *
	 * @return Insert
	 */
	public function insert(){

		$obj = $this->getObj();

		if($obj===false){
			throw new DatabaseException("Object is false");
		}

		if(!class_exists('Framework\Components\Database\MySQL\Insert')){
			throw new DatabaseException("Class Framework\\Components\\Database\\MySQL\\Insert not found");
		}

		return new Insert($obj);
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\MySQL\Update
	 *
	 * @throws DatabaseException
	 *
	 * @return Update
	 */
	public function update(){

		$obj = $this->getObj();

		if($obj===false){
			throw new DatabaseException("Object is false");
		}

		if(!class_exists('Framework\Components\Database\MySQL\Update')){
			throw new DatabaseException("Class Framework\\Components\\Database\\MySQL\\Update not found");
		}

		return new Update($obj);
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\MySQL\Delete
	 *
	 * @throws DatabaseException
	 *
	 * @return Delete
	*/
	public function delete(){

		$obj = $this->getObj();

		if($obj===false){
			throw new DatabaseException("Object is false");
		}

		if(!class_exists('Framework\Components\Database\MySQL\Delete')){
			throw new DatabaseException("Class Framework\\Components\\Database\\MySQL\\Delete not found");
		}

		return new Delete($obj);
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\MySQL\Transaction
	 *
	 * @throws DatabaseException
	 *
	 * @return Transaction
	 */
	public function transaction(){

		$obj = $this->getObj();

		if($obj===false){
			throw new DatabaseException("Object is false");
		}

		if(!class_exists('Framework\Components\Database\MySQL\Transaction')){
			throw new DatabaseException("Class Framework\\Components\\Database\\MySQL\\Transaction not found");
		}

		return new Transaction($obj);
	}

	/**
	 * Создает запрос к MySQL
	 *
	 * @param $sql string
	 *
	 * @throws DatabaseException
	 *
	 * @return resource
	*/
	public function query($sql){

		$obj = $this->getObj();

		if($obj===false){
			throw new DatabaseException("Object is false");
		}

		return mysql_query($sql, $obj);
	}

	/**
	 * Экранирует спецсимволы
	 *
	 * @param $string string
	 *
	 * @throws DatabaseException
	 *
	 * @return string
	*/
	public function safeSQL($string){

		$obj = $this->getObj();

		if($obj===false){
			throw new DatabaseException("Object is false");
		}

		return mysql_real_escape_string($string, $obj);
	}

	/**
	 * Возвращает объект соединения с базой данных
	 *
	 * @return \mysql | boolean
	*/
	public function getObj(){
		$key = Key::make($this->options['key']);

		if(!isset($this->connections[$key])){ return false; }

		return $this->connections[$key];
	}

	/**
	 * Возвращает последнюю ошибку запроса
	 *
	 * @throws DatabaseException
	 *
	 * @return string
	 */
	public function getError(){

		$obj = $this->getObj();

		if($obj===false){
			throw new DatabaseException("Object is false");
		}

		return mysql_error($obj);
	}
}

?>