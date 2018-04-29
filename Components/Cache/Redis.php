<?php
/**
 * Cache>Redis component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.2.1
 */

namespace Alonity\Components\Cache;

class RedisCacheException extends \Exception {}

class Redis {

	private $local = [];

	public function setOptions($options){
		$this->options = array_merge($this->options, $options);
	}

	private $options = [
		'host' => '127.0.0.1',
		'port' => 6379,
		'password' => '',
		'base' => 0,
		'timeout' => 3,
		'key' => 'alonitycache',
		'expire' => 0
	];

	private $redis = null;

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
	 * Взаимодействие с хранилищем Redis
	 *
	 * @throws RedisCacheException
	 * @throws \RedisException
	 *
	 * @return \Redis
	 */
	private function getRedis(){
		if(!is_null($this->redis)){ return $this->redis; }

		if(!class_exists('\Redis')){
			throw new RedisCacheException("Redis is not found");
		}

		$this->redis = new \Redis();

		$link = $this->redis->connect($this->options['host'], $this->options['port'], $this->options['timeout']);

		if(!$link){
			throw new \RedisException("Connection error");
		}

		if(!$this->redis->auth($this->options['password'])){
			throw new \RedisException("Incorrect auth");
		}

		if(!$this->redis->select($this->options['base'])){
			throw new \RedisException("Selection error");
		}

		return $this->redis;
	}

	/**
	 * Возвращает кэшируемое значение из хранилища Redis
	 *
	 * @param $key mixed
	 *
	 * @throws RedisCacheException
	 *
	 * @return mixed
	 */
	public function get($key){
		$key = $this->makeKey($key);

		if(isset($this->local[$key])){
			return $this->local[$key];
		}

		$get = $this->getRedis()->hGet($this->options['key'], $key);

		if($get===false){
			return null;
		}

		$result = json_decode($get, true);

		$this->local[$key] = $result;

		return $result;
	}

	/**
	 * Возвращает кэшируемые значения из хранилища Redis, используя массив ключей
	 *
	 * @param $keys array
	 *
	 * @throws RedisCacheException
	 *
	 * @return array
	 */
	public function getMultiple($keys){

		$result = [];

		if(!is_array($keys) || empty($keys)){
			return $result;
		}

		$redis = $this->getRedis();

		foreach($keys as $key){

			$key = $this->makeKey($key);

			if(isset($this->local[$key])){
				$result[$key] = $this->local[$key];
				continue;
			}

			$get = $redis->hGet($this->options['key'], $key);

			if($get===false){
				continue;
			}

			$res = json_decode($get, true);

			$this->local[$key] = $res;

			$result[$key] = $res;
		}

		return $result;
	}

	/**
	 * Кэширует значение в хранилище Redis
	 *
	 * @param $key mixed
	 * @param $value mixed
	 * @param $expire integer | null
	 *
	 * @throws \RedisException
	 *
	 * @return mixed
	 */
	public function set($key, $value, $expire=null){

		if(is_null($expire)){ $expire = $this->options['expire']; }

		$key = self::makeKey($key);

		if($this->getRedis()->hSet($this->options['key'], $key, json_encode($value))===false){
			throw new \RedisException("Redis method hSet return false");
		}

		if($expire>0){
			$this->getRedis()->setTimeout($this->options['key'].':'.$key, $expire);
		}

		$this->local[$key] = $value;

		return $value;
	}

	/**
	 * Кэширует значения в хранилище Redis, используя ассоциотивный массив
	 *
	 * @param $params array
	 * @param $expire integer | null
	 *
	 * @throws \RedisException
	 *
	 * @return array
	 */
	public function setMultiple($params, $expire=null){

		$result = [];

		if(!is_array($params) || empty($params)){
			return $result;
		}

		if(is_null($expire)){ $expire = $this->options['expire']; }

		foreach($params as $k => $v){

			$key = self::makeKey($k);

			if($this->getRedis()->hSet($this->options['key'], $key, json_encode($v))===false){
				throw new \RedisException("Redis method hSet return false");
			}

			if($expire>0){
				$this->getRedis()->setTimeout($this->options['key'].':'.$key, $expire);
			}

			$result[$key] = $v;

			$this->local[$key] = $v;
		}

		return $result;
	}

	/**
	 * Удаляет кэшируемое значение из хранилища Redis
	 *
	 * @param $key mixed
	 *
	 * @return boolean
	 */
	public function remove($key){

		$key = $this->makeKey($key);

		if(isset($this->local[$key])){
			unset($this->local[$key]);
		}

		if($this->getRedis()->hDel($this->options['key'], $key)===false){
			return false;
		}

		return true;
	}

	/**
	 * Удаляет кэшируемые значения из хранилища Redis, используя массив ключей
	 *
	 * @param $keys array
	 *
	 * @return array
	 */
	public function removeMultiple($keys){

		$redis = $this->getRedis();

		$result = [];

		foreach($keys as $key){

			$key = $this->makeKey($key);

			if(isset($this->local[$key])){
				unset($this->local[$key]);
			}

			if($redis->hDel($this->options['key'], $key)===false){
				continue;
			}

			$result[] = $key;
		}

		return $result;
	}

	/**
	 * Очищает хранилище Redis. Возвращает кол-во удаленных ключей
	 *
	 * @return integer
	 */
	public function clear(){

		$delete = $this->getRedis()->del($this->options['key']);

		return ($delete===false) ? 0 : intval($delete);
	}

	/**
	 * Возвращает экземпляр класса Redis
	 *
	 * @return \Redis
	*/
	public function getInstance(){
		return $this->getRedis();
	}
}

?>