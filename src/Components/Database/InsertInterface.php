<?php
/**
 * Database InsertInterface component of Alonity Framework
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

interface InsertInterface {

	public function __construct($obj);

	/**
	 * Имя таблицы, которая будет использоваться для вставки
	 *
	 * @param $table string
	 *
	 * @example 'my_table' returned "my_table"
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	*/
	public function into($table);

	/**
	 * Поля для вставки
	 *
	 * @param $columns array
	 *
	 * @example ['name', 'description'] returned ("name", "description")
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function columns($columns);

	/**
	 * Значения для вставки
	 *
	 * @param $values array
	 *
	 * @example ['Hello', 'World'] returned ('Hello', 'World')
	 * @example [['Hello', 'World'], ['Example', 'Field']] returned ('Hello', 'World'), ('Hello', 'World')
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function values($values);

	/**
	 * Возвращает последнюю ошибку результата запроса
	 *
	 * @return string
	*/
	public function getError();

	/**
	 * Возвращает строку SQL запроса
	 *
	 * @param $last_id string|null
	 *
	 * @return string
	 */
	public function getSQL($last_id=null);

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @param $last_id string|null
	 *
	 * @return boolean
	 */
	public function execute($last_id=null);

	/**
	 * Возвращает кол-во вставленных записей
	 *
	 * @return integer
	*/
	public function getInsertNum();

	/**
	 * Возвращает последний вставленный ID
	 *
	 * @return integer
	*/
	public function getLastID();
}

?>