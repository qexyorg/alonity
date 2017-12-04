<?php
/**
 * Cache>File component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Alonity\Components\Cache;

class FileCacheException extends \Exception {}

class File {

	private $options = [
		'path' => '/Uploads/cache'
	];

	private $rootDir = null;

	public function setOptions($options){
		$this->options = array_merge($this->options, $options);
	}

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

	private function getRoot(){
		if(!is_null($this->rootDir)){
			return $this->rootDir;
		}

		$this->rootDir = dirname(dirname(__DIR__));

		return $this->rootDir;
	}

	/**
	 * Возвращает кэшируемое значение из файлового хранилища
	 *
	 * @param $key mixed
	 *
	 * @return mixed
	 */
	public function get($key){
		$key = $this->makeKey($key);

		$filename = $this->getRoot().$this->options['path'].'/'.$key.'.php';

		$cache = null;

		if(!file_exists($filename)){
			return $cache;
		}

		include($filename);

		return $cache;
	}

	/**
	 * Возвращает кэшируемые значения из файлового хранилища, используя массив ключей
	 *
	 * @param $keys array
	 *
	 * @throws FileCacheException
	 *
	 * @return array
	 */
	public function getMultiple($keys){

		$result = [];

		if(!is_array($keys) || empty($keys)){
			return $result;
		}

		$filepath = $this->getRoot().$this->options['path'].'/';

		$cache = null;

		foreach($keys as $key){

			$filename = $filepath.$this->makeKey($key).'.php';

			if(!file_exists($filename)){
				continue;
			}

			include($filename);

			$result[$key] = $cache;
		}

		return $result;
	}

	/**
	 * Кэширует значение в файловое хранилище
	 *
	 * @param $key mixed
	 * @param $value mixed
	 *
	 * @throws FileCacheException
	 *
	 * @return mixed
	 */
	public function set($key, $value){

		$key = $this->makeKey($key);

		$filepath = $this->getRoot().$this->options['path'];

		$data = '<?php // Last update: '.date("d.m.Y H:i:s").PHP_EOL;
		$data .= '	$cache = '.var_export($value, true).';'.PHP_EOL;
		$data .= '?>';

		if(!file_exists($filepath)){ mkdir($filepath, 0755, true); }

		$filename = "{$filepath}/{$key}.php";

		file_put_contents($filename, $data);

		return $value;
	}

	/**
	 * Кэширует значения в файловое хранилище, используя ассоциотивный массив
	 *
	 * @param $params array
	 *
	 * @throws FileCacheException
	 *
	 * @return array
	 */
	public function setMultiple($params){

		$result = [];

		if(!is_array($params) || empty($params)){
			return $result;
		}

		$filepath = $this->getRoot().$this->options['path'];

		foreach($params as $k => $v){

			$data = '<?php '.date("d.m.Y H:i:s").'\n\n';
			$data .= '$cache = '.var_export($v, true).';\n\n';
			$data .= '?>';

			$key = $this->makeKey($k);

			$filename = "{$filepath}/{$key}.php";

			file_put_contents($filename, $data);

			$result[$k] = $v;
		}

		return $result;
	}

	/**
	 * Удаляет кэшируемое значение из файлового хранилища
	 *
	 * @param $key mixed
	 *
	 * @return boolean
	 */
	public function remove($key){

		$key = $this->makeKey($key);

		$filename = $this->getRoot().$this->options['path'].'/'.$key.'.php';

		if(!file_exists($filename)){
			return false;
		}

		@unlink($filename);

		return true;
	}

	/**
	 * Удаляет кэшируемые значения из файлового хранилища, используя массив ключей
	 *
	 * @param $keys array
	 *
	 * @return array
	 */
	public function removeMultiple($keys){

		$filepath = $this->getRoot().$this->options['path'];

		$result = [];

		foreach($keys as $k){

			$filename = $filepath.'/'.$this->makeKey($k).'.php';

			if(!file_exists($filename)){
				continue;
			}

			@unlink($filename);

			$result[] = $k;
		}

		return $result;
	}

	/**
	 * Очищает файловое хранилище хранилище. Возвращает кол-во удаленных ключей
	 *
	 * @return integer
	 */
	public function clear(){

		$filepath = $this->getRoot().$this->options['path'];

		$num = 0;

		foreach(scandir($filepath) as $v){
			if($v=='.' || $v=='..'){ continue; }

			$filename = "{$filepath}/{$v}";

			if(!is_file($filename)){ continue; }

			@unlink($filename);

			$num++;
		}

		return $num;
	}
}

?>