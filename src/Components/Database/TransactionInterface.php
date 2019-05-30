<?php
/**
 * Database TransactionInterface component of Alonity Framework
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

interface TransactionInterface {

	public function __construct($obj);

	/**
	 * Добавляет запросы типов Delete/Update/Insert/Select в тело транзакции
	 *
	 * @param $object DeleteInterface|UpdateInterface|InsertInterface|SelectInterface
	 *
	 * @return $this
	 */
	public function add($object);

	/**
	 * Возвращает последнюю ошибку результата запроса
	 *
	 * @return string
	 */
	public function getError();

	/**
	 * Объединяет все элементы и создает запрос
	 *
	 * @return boolean
	 */
	public function execute();
}

?>