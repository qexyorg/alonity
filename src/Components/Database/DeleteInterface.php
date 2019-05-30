<?php
/**
 * Database DeleteInterface component of Alonity Framework
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

interface DeleteInterface {

	public function __construct($obj);

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
	public function from($table);

	/**
	 * Ограничение кол-ва удаляемых строк
	 *
	 * @param $limit integer
	 *
	 * @return $this
	 */
	public function limit($limit);

	/**
	 * Выставляет смещение ограничения удаляемого результата
	 *
	 * @param $offset integer
	 *
	 * @example 10 returned OFFSET 10
	 *
	 * @return $this
	 */
	public function offset($offset);

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
	public function order($params);

	/**
	 * Возвращает последнюю ошибку результата запроса
	 *
	 * @return string
	 */
	public function getError();

	/**
	 * Возвращает количество удаленных записей
	 *
	 * @return integer
	 */
	public function getDeletedNum();

	/**
	 * Возвращает строку SQL запроса
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