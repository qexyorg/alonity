<?php
/**
 * Database MySQLi wrapper component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.0.0
 */

namespace Framework\Components\Database\MySQLi;

use Framework\Alonity\Keys\Key;
use Framework\Components\Database\DatabaseException;

class Wrapper {

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

		$connection =  new \mysqli($host, $user, $password, $database, $port);

		if($connection->connect_errno){
			throw new DatabaseException("MySQLi connection error: {$connection->connect_error}");
		}

		if(!@$connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, $timeout)){
			throw new DatabaseException("MySQLi error set options: {$connection->connect_error}");
		}

		$this->connections[$token] = $connection;

		$this->connections[$token] = $this->setCharset($charset, $connection);

		return $this->connections[$token];
	}

	/**
	 * Изменяет базу данных для работы
	 *
	 * @param $name string
	 * @param $obj \mysqli|null
	 *
	 * @throws DatabaseException
	 *
	 * @return \mysqli
	 */
	public function setDB($name, $obj=null){

		if(empty($name)){
			throw new DatabaseException("database name must be not empty");
		}

		if(!is_string($name)){
			throw new DatabaseException("database name must be a string");
		}

		if(!@$obj->select_db($name)){
			throw new DatabaseException("change database error: ".$obj->error);
		}

		return $obj;
	}

	/**
	 * Изменяет кодировку соединения
	 *
	 * @param $encoding string
	 * @param $obj \mysqli|null
	 *
	 * @throws DatabaseException
	 *
	 * @return \mysqli
	 */
	public function setCharset($encoding='utf8', $obj=null){

		if(empty($encoding)){
			throw new DatabaseException("database encoding must be not empty");
		}

		if(!is_string($encoding)){
			throw new DatabaseException("database encoding must be a string");
		}

		if(!@$obj->set_charset($encoding)){
			throw new DatabaseException("change database charset error: ".$obj->error);
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

		$this->connections[$key]->close();

		unset($this->connections[$key]);

		return true;
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\MySQLi\Select
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

		if(!class_exists('Framework\Components\Database\MySQLi\Select')){
			throw new DatabaseException("Class Framework\\Components\\Database\\MySQLi\\Select not found");
		}

		return new Select($obj);
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\MySQLi\Insert
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

		if(!class_exists('Framework\Components\Database\MySQLi\Insert')){
			throw new DatabaseException("Class Framework\\Components\\Database\\MySQLi\\Insert not found");
		}

		return new Insert($obj);
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\MySQLi\Update
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

		if(!class_exists('Framework\Components\Database\MySQLi\Update')){
			throw new DatabaseException("Class Framework\\Components\\Database\\MySQLi\\Insert not found");
		}

		return new Update($obj);
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\MySQLi\Delete
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

		if(!class_exists('Framework\Components\Database\MySQLi\Delete')){
			throw new DatabaseException("Class Framework\\Components\\Database\\MySQLi\\Delete not found");
		}

		return new Delete($obj);
	}

	/**
	 * Создает запрос к MySQLi
	 *
	 * @param $sql string
	 *
	 * @throws DatabaseException
	 *
	 * @return \mysqli_result
	 */
	public function query($sql){

		$obj = $this->getObj();

		if($obj===false){
			throw new DatabaseException("Object is false");
		}

		return $obj->query($sql);
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

		return $obj->real_escape_string($string);
	}

	/**
	 * Возвращает объект соединения с базой данных
	 *
	 * @return \mysqli | boolean
	 */
	public function getObj(){
		$key = (isset($this->options['key'])) ? $this->options['key'] : null;

		$key = Key::make($key);

		if(!isset($this->connections[$key])){ return false; }

		return $this->connections[$key];
	}
}

?>