<?php
/**
 * Database WrapperInterface of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Components\Database;

interface WrapperInterface {

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
	public function connect($host='127.0.0.1', $database='database', $user='root', $password='', $port=3306, $charset='utf8', $timeout=3, $key=null);

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
	public function setDB($name, $obj=null);

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
	public function setCharset($encoding='utf8', $obj=null);

	/**
	 * Закрывает соединение с базой по ключу и удаляет линк
	 *
	 * @param $key integer
	 *
	 * @return boolean
	*/
	public function disconnect($key=0);

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\...\Select
	 *
	 * @throws DatabaseException
	 *
	 * @return Select
	 */
	public function select();

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\...\Insert
	 *
	 * @throws DatabaseException
	 *
	 * @return Insert
	 */
	public function insert();

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\...\Update
	 *
	 * @throws DatabaseException
	 *
	 * @return Update
	 */
	public function update();

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\...\Delete
	 *
	 * @throws DatabaseException
	 *
	 * @return Delete
	 */
	public function delete();

	/**
	 * Возвращает экземпляр класса Framework\Components\Database\...\Transaction
	 *
	 * @throws DatabaseException
	 *
	 * @return Transaction
	 */
	public function transaction();

	/**
	 * Создает запрос к MySQLi
	 *
	 * @param $sql string
	 *
	 * @throws DatabaseException
	 *
	 * @return \mysqli_result
	 */
	public function query($sql);

	/**
	 * Экранирует спецсимволы
	 *
	 * @param $string string
	 *
	 * @throws DatabaseException
	 *
	 * @return string
	 */
	public function safeSQL($string);

	/**
	 * Возвращает объект соединения с базой данных
	 *
	 * @return \mysqli | boolean
	 */
	public function getObj();

	/**
	 * Возвращает последнюю ошибку запроса
	 *
	 * @throws DatabaseException
	 *
	 * @return string
	*/
	public function getError();
}

?>