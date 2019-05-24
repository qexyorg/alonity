<?php
/**
 * Database component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.1.1
 */

namespace Framework\Components\Database;

use Framework\Alonity\Keys\Key;
use Framework\Components\Database\MySQLi\Wrapper;

class Database {

	const WHERE_AND = 0x538;
	const WHERE_OR = 0x539;

	private static $queries = 0;

	private static $options = [
		'engine' => 'mysqli',
		'mysqli' => [
			'host' => '127.0.0.1',
			'port' => 3306,
			'charset' => 'utf8mb4',
			'timeout' => 3,
			'database' => 'database',
			'user' => 'root',
			'password' => '',
			'class' => '\Framework\Components\Database\MySQLi\Wrapper',
			'key' => 0
		],
		'mysql' => [
			'host' => '127.0.0.1',
			'port' => 3306,
			'charset' => 'utf8mb4',
			'timeout' => 3,
			'database' => 'database',
			'user' => 'root',
			'password' => '',
			'class' => '\Framework\Components\Database\MySQL\Wrapper',
			'key' => 0
		],
		'postgres' => [
			'host' => '127.0.0.1',
			'port' => 5432,
			'charset' => 'utf8',
			'timeout' => 3,
			'database' => 'database',
			'user' => 'postgres',
			'password' => '',
			'class' => '\Framework\Components\Database\PostgreSQL\Wrapper',
			'key' => 0
		],
		'redis' => [
			'host' => '127.0.0.1',
			'port' => 6379,
			'timeout' => 3,
			'database' => 0,
			'password' => '',
			'class' => '\Framework\Components\Database\Redis',
			'key' => 0
		],
		'memcache' => [
			'host' => '127.0.0.1',
			'port' => 11211,
			'timeout' => 3,
			'class' => '\Framework\Components\Database\Memcache',
			'key' => 0
		]
	];

	private static $connections = [];

	private static $objects = [];

	private static $last_error = null;

	/**
	 * Выставление настроек
	 *
	 * @param $params array
	 *
	 * @return boolean
	 */
	public static function setOptions($params){
		if(!is_array($params) || empty($params)){
			return false;
		}

		self::$options = array_replace_recursive(self::$options, $params);

		return true;
	}

	/**
	 * Создание подключение к базе данных. Если подключение уже существует, возвращает его экземпляр.
	 *
	 * @throws DatabaseException
	 *
	 * @return Wrapper
	*/
	public static function connect(){

		$engine = self::$options['engine'];

		if(!isset(self::$options[$engine])){
			self::$last_error = "Unexpected default engine options";
			throw new DatabaseException(self::$last_error);
		}

		$options = self::$options[$engine];

		$token = Key::make($options['key']);

		if(isset(self::$connections[$token])){ return self::$connections[$token]; }

		$classname = $options['class'];

		if(!class_exists($classname)){
			self::$last_error = "Undefined class \"{$classname}\"";
			throw new DatabaseException(self::$last_error);
		}

		if(!isset(self::$objects[$engine])){
			self::$objects[$engine] = new $classname();
		}

		$object = self::$objects[$engine];

		try {
			self::$connections[$token] = $object->connect(
				$options['host'],
				$options['database'],
				$options['user'],
				$options['password'],
				$options['port'],
				$options['charset'],
				$options['timeout']
			);
		}catch(\Exception $e){
			$error = $e->getMessage();
		}

		if(isset($error)){
			throw new DatabaseException($error);
		}

		return self::$connections[$token];
	}

	/**
	 * Отключение от базы данных
	 *
	 * @param $engine string|null
	 * @param $key mixed
	 *
	 * @return boolean
	*/
	public static function disconnect($engine=null, $key=0){

		if(is_null($engine)){
			$engine = self::$options['engine'];
		}

		if(!isset(self::$objects[$engine])){
			return false;
		}

		if(!self::$objects[$engine]->disconnect($key)){
			return false;
		}

		unset(self::$objects[$engine]);

		return true;
	}

	/**
	 * Возвращает последнюю ошибку базы данных
	 *
	 * @return string|null
	*/
	public static function getLastError(){
		return self::$last_error;
	}

	/**
	 * Возвращает используемый движок базы данных
	 *
	 * @return object
	*/
	public static function getEngine(){
		if(!isset(self::$objects[self::$options['engine']])){
			try {
				self::connect();
			}catch (DatabaseException $e){
				exit($e);
			}
		}

		return self::$objects[self::$options['engine']];
	}

	/**
	 * Возвращает кол-во запросов
	 *
	 * @return integer
	*/
	public static function getQueriesNum(){
		return self::$queries;
	}

	/**
	 * Создает операцию выборки строк из базы данных
	 *
	 * @return \Framework\Components\Database\MySQLi\Select
	 * @return \Framework\Components\Database\MySQL\Select
	 * @return \Framework\Components\Database\PostgreSQL\Select
	*/
	public static function select(){
		self::$queries++;
		return self::getEngine()->select();
	}

	/**
	 * Создает операцию вставки строк в базу данных
	 *
	 * @return \Framework\Components\Database\MySQLi\Insert
	 * @return \Framework\Components\Database\MySQL\Insert
	 * @return \Framework\Components\Database\PostgreSQL\Insert
	 */
	public static function insert(){
		self::$queries++;
		return self::getEngine()->insert();
	}

	/**
	 * Создает операцию обновления данных в базе
	 *
	 * @return \Framework\Components\Database\MySQLi\Update
	 * @return \Framework\Components\Database\MySQL\Update
	 * @return \Framework\Components\Database\PostgreSQL\Update
	 */
	public static function update(){
		self::$queries++;
		return self::getEngine()->update();
	}

	/**
	 * Создает операцию удаления строк из базы данных
	 *
	 * @return \Framework\Components\Database\MySQLi\Delete
	 * @return \Framework\Components\Database\MySQL\Delete
	 * @return \Framework\Components\Database\PostgreSQL\Delete
	 */
	public static function delete(){
		self::$queries++;
		return self::getEngine()->delete();
	}

	/**
	 * Создает запрос к базе данных
	 *
	 * @param $sql string
	 *
	 * @return \mysqli_result
	 * @return resource
	 */
	public static function query($sql){
		self::$queries++;
		return self::getEngine()->query($sql);
	}

	public static function import($filename, $predrop=false){
		$read = file($filename);

		if(!$read){
			throw new DatabaseException("Can't read file {$filename}");
		}

		$result = true;

		$query = "";

		foreach($read as $line){

			$line = trim($line);

			$fs1 = mb_substr($line, 0, 1, 'UTF-8');
			$fs2 = mb_substr($line, 0, 2, 'UTF-8');
			$ls2 = mb_substr($line, -2, null, 'UTF-8');
			$ls3 = mb_substr($line, -3, null, 'UTF-8');
			$ls1 = mb_substr($line, -1, null, 'UTF-8');

			if($fs1=='#' || $fs2=='--' || $line=='' || $fs2=='/*' || $ls2=='*/' || $ls3=='*/;'){
				continue;
			}

			if($predrop && preg_match("/CREATE TABLE IF NOT EXISTS `([\w\-]+)`/i", $line, $match)){
				if(isset($match[1])){
					self::query("DROP TABLE IF EXISTS `{$match[1]}`;");
				}
			}

			$query .= " {$line}";


			if($ls1==';'){

				$execute = self::query($query);

				if(!$execute){
					throw new DatabaseException("<p><b>".self::getQueryError()."</b></p><p>In SQL:<br>{$query}</p>");
					break;
				}

				$query = "";
			}
		}

		return $result;
	}

	/**
	 * Возвращает последнюю  ошибку запроса
	 *
	 * @return string
	*/
	public static function getQueryError(){
		return self::getEngine()->getError();
	}

	/**
	 * Экранирует спецсимволы в запросе
	 *
	 * @param $string string
	 *
	 * @return string
	 */
	public static function safeSQL($string){
		return self::getEngine()->safeSQL($string);
	}

	/**
	 * Экранирует передаваемые в IN параметры
	 *
	 * @param $array array
	 *
	 * @return array
	*/
	public static function filterIn($array){
		$array = array_unique($array);

		$array = array_map(function($e){
			$e = self::safeSQL($e);

			return "'{$e}'";
		}, $array);

		return $array;
	}
}

?>