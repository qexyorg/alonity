<?php
/**
 * Cache>Memcache component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.1.0
 */

namespace Alonity\Components\Cache;

class MemcacheCacheException extends \Exception {}

class Memcache {

	public function setOptions($options){
		$this->options = array_merge($this->options, $options);
	}

	private $options = [
		'host' => '127.0.0.1',
		'port' => 11211,
		'timeout' => 3
	];

	private $memcache = null;

	/**
	 * Шифрование ключа
	 *
	 * @param $key mixed
	 *
	 * @return string
	 */
	public function makeKey($key){
		return md5(var_export($key, true));
	}

	/**
	 * Взаимодействие с хранилищем Memcache
	 *
	 * @throws MemcacheCacheException
	 *
	 * @return \Memcache
	 */
	private function getMemcache(){
		if(!is_null($this->memcache)){ return $this->memcache; }

		if(!class_exists('\Memcache')){
			throw new MemcacheCacheException("Memcache is not found");
		}

		$this->memcache = new \Memcache();

		$link = $this->memcache->connect($this->options['host'], $this->options['port'], $this->options['timeout']);

		if(!$link){
			throw new MemcacheCacheException("Connection error");
		}

		return $this->memcache;
	}

	/**
	 * Кэширует значение в хранилище Memcache
	 *
	 * @param $key mixed
	 * @param $value mixed
	 *
	 * @throws MemcacheCacheException
	 *
	 * @return mixed
	 */
	public function set($key, $value){

		if($this->getMemcache()->set($this->makeKey($key), json_encode($value))===false){
			throw new MemcacheCacheException("Memcache set return false");
		}

		return $value;
	}

	/**
	 * Кэширует значения в хранилище Memcache, используя ассоциотивный массив
	 *
	 * @param $params array
	 *
	 * @throws MemcacheCacheException
	 *
	 * @return mixed
	 */
	public function setMultiple($params){

		$result = [];

		if(!is_array($params) || empty($params)){
			return $result;
		}

		$memcache = $this->getMemcache();

		foreach($params as $k => $v){

			if($memcache->set($this->makeKey($k), json_encode($v))===false){
				throw new MemcacheCacheException("Memcache set return false");
			}

			$result[$k] = $v;
		}

		return $result;
	}

	/**
	 * Возвращает кэшируемое значение из хранилища Memcache
	 *
	 * @param $key mixed
	 *
	 * @throws MemcacheCacheException
	 *
	 * @return mixed
	 */
	public function get($key){
		$key = $this->makeKey($key);

		$get = $this->getMemcache()->get($key);

		if($get===false){
			throw new MemcacheCacheException("Memcache get return false");
		}

		return json_decode($get, true);
	}

	/**
	 * Возвращает кэшируемые значения из хранилища Memcache, используя массив ключей
	 *
	 * @param $keys array
	 *
	 * @throws MemcacheCacheException
	 *
	 * @return array
	 */
	public function getMultiple($keys){

		$result = [];

		if(!is_array($keys) || empty($keys)){
			return $result;
		}

		$memcache = $this->getMemcache();

		foreach($keys as $key){
			$get = $memcache->get($this->makeKey($key));

			if($get===false){
				throw new MemcacheCacheException("Memcache get return false");
			}

			$result[$key] = json_decode($get, true);

		}

		return $result;
	}

	/**
	 * Удаляет кэшируемое значение из хранилища Memcache
	 *
	 * @param $key mixed
	 *
	 * @return boolean
	 */
	public function remove($key){
		$key = $this->makeKey($key);

		if($this->getMemcache()->delete($key)===false){
			return false;
		}

		return true;
	}

	/**
	 * Удаляет кэшируемые значения из хранилища Memcache, используя массив ключей
	 *
	 * @param $keys mixed
	 *
	 * @return array
	 */
	public function removeMultiple($keys){

		$memcache = $this->getMemcache();

		$result = [];

		foreach($keys as $key){
			if($memcache->delete($this->makeKey($key))===false){
				continue;
			}

			$result[] = $key;
		}

		return $result;
	}

	/**
	 * Очищает хранилище Memcache. Возвращает кол-во удаленных файлов
	 *
	 * @return integer
	 */
	public function clear(){

		return $this->getMemcache()->flush();
	}

	/**
	 * Возвращает экземпляр класса Memcache
	 *
	 * @return \Memcache
	 */
	public function getInstance(){
		return $this->getMemcache();
	}
}

?>