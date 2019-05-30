<?php
/**
 * Database SelectInterface component of Alonity Framework
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

interface SelectInterface {

	public function __construct($obj);

	/**
	 * Выставляет таблицу из которой будет извлечен результат
	 *
	 * @param $table array | string
	 *
	 * @example 'my_table' returned "my_table"
	 * @example array('t' => 'my_table') returned "t"."my_table"
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	*/
	public function from($table=[]);

	/**
	 * Выставляет условия AND по которым будет извлечен результат
	 *
	 * @param $where array
	 * @param $values array
	 * @param $type integer
	 *
	 * @example array("`t`.`id`='?'" => 3, "`t`.`name`='?'" => 'hello') returned AND "t"."id"='3' AND "t"."name"='hello'
	 * @example array("`t`.`name`='?-?'" => 'hello') returned AND "t"."name"='hello-hello'
	 * @example array("`t`.`name`='?-?'" => ['hello', 'world']) returned AND "t"."name"='hello-world'
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function where($where, $values=[], $type=0x538);

	/**
	 * Выставляет условия группировки
	 *
	 * @param $value string
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function having($value);

	/**
	 * Выставляет ограничения возвращаемых результатов
	 *
	 * @param $limit integer
	 *
	 * @example 10 returned LIMIT 10
	 *
	 * @return $this
	 */
	public function limit($limit);

	/**
	 * Выставляет смещение ограничения возвращаемых результатов
	 *
	 * @param $offset integer
	 *
	 * @example 10 returned OFFSET 10
	 *
	 * @return $this
	 */
	public function offset($offset);

	/**
	 * Выставляет порядок сортировки
	 *
	 * @param $params array
	 *
	 * @example array('id') returned ORDER BY `id` ASC
	 * @example array('id' => 'desc') returned ORDER BY "id" DESC
	 * @example array('id' => 'desc', 'name' => 'ASC') returned ORDER BY "id" DESC, "name" ASC
	 * @example array('id' => 'desc', 'name') returned ORDER BY "id" DESC, "name" ASC
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function order($params);

	/**
	 * Выставляет поля для выборки. Повторное использование в одном экземпляре Select объединяет колонки
	 *
	 * @param $columns array
	 *
	 * @example ['`example`', '`t`.`test`'] returned SELECT "example", "t"."test"
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function columns($columns);

	/**
	 * Группирует результаты запроса по выбранным колонкам
	 *
	 * @param $columns array
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function group($columns);

	/**
	 * Создает вхождение в таблицу используя оператор LEFT JOIN
	 *
	 * @param $table string
	 * @param $alias string
	 * @param $columns array
	 * @param $values array
	 * @param $type integer
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function leftjoin($table, $alias='', $columns=[], $values=[], $type=0x538);

	/**
	 * Создает вхождение в таблицу используя оператор RIGHT JOIN
	 *
	 * @param $table string
	 * @param $alias string
	 * @param $columns array
	 * @param $values array
	 * @param $type integer
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function rightjoin($table, $alias='', $columns=[], $values=[], $type=0x538);

	/**
	 * Создает вхождение в таблицу используя оператор INNER JOIN
	 *
	 * @param $table string
	 * @param $alias string
	 * @param $columns array
	 * @param $values array
	 * @param $type integer
	 *
	 * @throws DatabaseException
	 *
	 * @return $this
	 */
	public function innerjoin($table, $alias='', $columns=[], $values=[], $type=0x538);

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

	/**
	 * Возвращает последнюю произошедшую ошибку
	 *
	 * @return string
	*/
	public function getError();

	/**
	 * Возвращает числовой массив из выборки
	 *
	 * @return array
	*/
	public function getArray();

	/**
	 * Возвращает ассоциотивный массив из выборки
	 *
	 * @return array
	*/
	public function getAssoc();

	/**
	 * Возвращает кол-во выбранных записей
	 *
	 * @return integer
	*/
	public function getNum();

	/**
	 * Возвращает результат COUNT запроса
	 *
	 * @return integer
	*/
	public function getCount();
}

?>