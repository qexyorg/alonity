<?php
/**
 * Database UpdateInterface component of Alonity Framework
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

interface UpdateInterface {

	public function __construct($obj);

	/**
	 * Имя таблицы, которая будет использоваться для обновления
	 *
	 * @param $table string
	 *
	 * @example 'my_table' returned "my_table"
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function table($table);

	/**
	 * Ограничение кол-ва обновляемых строк
	 *
	 * @param $limit integer
	 *
	 * @return $this
	 */
	public function limit($limit);

	/**
	 * Выставляет смещение ограничения обновляемого результата
	 *
	 * @param $offset integer
	 *
	 * @example 10 returned OFFSET 10
	 *
	 * @return $this
	 */
	public function offset($offset);

	/**
	 * Обновляемые колонки и их значения
	 *
	 * @param $set array
	 *
	 * @example ['name' => 'hello', '`desc`' => 'world'] returned name='hello',"desc"='world'
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function set($set);

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
	public function where($where, $values=[], $type=0x538);

	/**
	 * Возвращает последнюю произошедшую ошибку
	 *
	 * @return string
	 */
	public function getError();

	/**
	 * Возвращает кол-во затронутых записей
	 *
	 * @return integer
	 */
	public function getUpdatedNum();

	/**
	 * Возвращает строку сформированного запроса
	 *
	 * @return string
	 */
	public function getSQL();

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @return boolean
	 */
	public function execute();
}

?>