<?php
/**
 * Database component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Alonity\Components;

use DatabaseException;

require_once(__DIR__.'/DatabaseException.php');

class Database {

	const WHERE_AND = 0x538;
	const WHERE_OR = 0x539;

	private static $options = [
		'engine' => 'mysqli',
		'mysqli' => [
			'host' => '127.0.0.1',
			'port' => 3306,
			'charset' => 'utf8',
			'timeout' => 3,
			'database' => 'database',
			'user' => 'root',
			'password' => '',
			'class' => 'Alonity\Components\Database\MySQLi',
			'dir' => '/MySQL/MySQLi.php',
			'key' => 0
		],
		'mysql' => [
			'host' => '127.0.0.1',
			'port' => 3306,
			'charset' => 'utf8',
			'timeout' => 3,
			'database' => 'database',
			'user' => 'root',
			'password' => '',
			'class' => 'Alonity\Components\Database\MySQL',
			'dir' => '/MySQL/MySQL.php',
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
			'class' => 'Alonity\Components\Database\PostgreSQL',
			'dir' => '/PostgreSQL/PostgreSQL.php',
			'key' => 0
		],
		'redis' => [
			'host' => '127.0.0.1',
			'port' => 6379,
			'timeout' => 3,
			'database' => 0,
			'password' => '',
			'class' => 'Alonity\Components\Database\Redis',
			'dir' => '/Redis/Redis.php',
			'key' => 0
		],
		'memcache' => [
			'host' => '127.0.0.1',
			'port' => 11211,
			'timeout' => 3,
			'class' => 'Alonity\Components\Database\Memcache',
			'dir' => '/Memcache/Memcache.php',
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
	 * @throws DatabaseException
	 *
	 * @return boolean
	 */
	public static function setOptions($params){
		if(!is_array($params) || empty($params)){
			throw new DatabaseException("Options is not set");
		}

		self::$options = array_replace_recursive(self::$options, $params);

		return true;
	}

	/**
	 * Создание подключение к базе данных. Если подключение уже существует, возвращает его экземпляр.
	 *
	 * @throws DatabaseException
	 *
	 * @return object
	*/
	public static function connect(){

		$engine = self::$options['engine'];

		if(!isset(self::$options[$engine])){
			self::$last_error = "Unexpected default engine options";
			throw new DatabaseException(self::$last_error);
		}

		$options = self::$options[$engine];

		$token = md5(var_export($options['key'], true));

		if(isset(self::$connections[$token])){ return self::$connections[$token]; }

		$classname = $options['class'];

		if(!class_exists($classname)){
			require_once(__DIR__.$options['dir']);
		}

		if(!isset(self::$objects[$engine])){
			self::$objects[$engine] = new $classname();
		}

		$object = self::$objects[$engine];

		self::$connections[$token] = $object->connect($options);

		return self::$connections[$token];
	}

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

	public static function getLastError(){
		return self::$last_error;
	}

	public static function select(){
		return self::$objects[self::$options['engine']]->select();
	}

	public static function insert(){
		return self::$objects[self::$options['engine']]->insert();
	}

	public static function update(){
		return self::$objects[self::$options['engine']]->update();
	}

	public static function delete(){
		return self::$objects[self::$options['engine']]->delete();
	}

	public static function query($sql){
		return self::$objects[self::$options['engine']]->query($sql);
	}
}

?>