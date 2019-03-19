<?php
/**
 * Database PostgreSQL wrapper component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.1.0
 */

namespace Framework\Components\Database\PostgreSQL;

use Framework\Alonity\Keys\Key;
use Framework\Components\Database\DatabaseException;

class Wrapper {

	private $options = [];

	private $connections = [];

	/**
	 * Создает соединение с базой PostgreSQL
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

		$connection = pg_connect("host='{$host}' port='{$port}' dbname='{$database}' user='{$user}' password='{$password}' connect_timeout='{$timeout}' options='--client_encoding={$charset}'");

		if(!$connection){
			throw new DatabaseException("PostgreSQL connection error: ".pg_last_error());
		}

		$this->connections[$key] = $connection;

		return $this->connections[$token];
	}

	/**
	 * Изменяет базу данных для работы
	 *
	 * @param $name string
	 * @param $obj resource | null
	 *
	 * @throws DatabaseException
	 *
	 * @return resource
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

		if(!@pg_close($obj)){
			throw new DatabaseException("error close connection: ".pg_last_error($obj));
		}

		$obj = @pg_connect("host='{$this->options['host']}' port='{$this->options['port']}' dbname='{$name}' user='{$this->options['user']}' password='{$this->options['password']}' connect_timeout='{$this->options['timeout']}' options='--client_encoding={$this->options['charset']}'");

		if(!$obj){
			throw new DatabaseException("PostgreSQL connection error: ".pg_last_error());
		}

		return $obj;
	}

	/**
	 * Изменяет кодировку соединения
	 *
	 * @param $encoding string
	 * @param $obj resource | null
	 *
	 * @throws DatabaseException
	 *
	 * @return resource
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

		if(!@pg_set_client_encoding($obj, $encoding)){
			throw new DatabaseException("change database charset error: ".pg_last_error($obj));
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

		@pg_close($this->connections[$key]);

		unset($this->connections[$key]);

		return true;
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\PostgreSQL\Select
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

		if(!class_exists('Framework\Components\Database\PostgreSQL\Select')){
			throw new DatabaseException("Class Framework\\Components\\Database\\PostgreSQL\\Select not found");
		}

		return new Select($obj);
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\PostgreSQL\Insert
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

		if(!class_exists('Framework\Components\Database\PostgreSQL\Insert')){
			throw new DatabaseException("Class Framework\\Components\\Database\\PostgreSQL\\Insert not found");
		}

		return new Insert($obj);
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\PostgreSQL\Update
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

		if(!class_exists('Framework\Components\Database\PostgreSQL\Update')){
			throw new DatabaseException("Class Framework\\Components\\Database\\PostgreSQL\\Update not found");
		}

		return new Update($obj);
	}

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\PostgreSQL\Delete
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

		if(!class_exists('Framework\Components\Database\PostgreSQL\Delete')){
			throw new DatabaseException("Class Framework\\Components\\Database\\PostgreSQL\\Delete not found");
		}

		return new Delete($obj);
	}

	/**
	 * Создает запрос к PostgreSQL
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

		return pg_query($obj, $sql);
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

		return pg_escape_string($obj, $string);
	}

	/**
	 * Возвращает объект соединения с базой данных
	 *
	 * @return resource | boolean
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

		return pg_last_error($obj);
	}
}

?>