<?php
/**
 * Cache File component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.0.2
 */

namespace Framework\Components\Cache;

use Framework\Alonity\DI\DI;

class File {

	private $options = [];

	private $rootDir = null;

	private $local = [];

	public function __construct(){
		$this->options = [
			'path' => '/tmp/cache'
		];
	}

	public function setOptions($options){
		$this->options = array_replace_recursive($this->options, $options);
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

		$this->rootDir = DI::get('ALONITY')->getRoot();

		return $this->rootDir;
	}

	public function getTime($key, $path=null){

		if(is_null($path)){ $path = $this->options['path']; }

		$path = str_replace(':', '/', $path);

		$key = $this->makeKey($key);

		if(!isset($this->local[$path.$key])){ $this->get($key, $path); }

		return $this->local[$path.$key]['time'];
	}

	/**
	 * Возвращает кэшируемое значение из файлового хранилища
	 *
	 * @param $key mixed
	 * @param $path string | null
	 *
	 * @return mixed
	 */
	public function get($key, $path=null){

		if(is_null($path)){ $path = $this->options['path']; }

		$path = str_replace(':', '/', $path);

		$key = $this->makeKey($key);

		if(isset($this->local[$path.$key])){ return $this->local[$path.$key]['cache']; }

		$filename = $this->getRoot().$path.'/'.$key.'.php';

		$cache = null;
		$time = time();

		if(!file_exists($filename)){
			return $cache;
		}

		include($filename);

		if(isset($expire) && $expire>0 && $expire<=time()){
			@unlink($filename);
			return null;
		}

		$this->local[$path.$key] = [
			'cache' => $cache,
			'time' => $time
		];

		return $cache;
	}

	/**
	 * Возвращает кэшируемые значения из файлового хранилища, используя массив ключей
	 *
	 * @param $keys array
	 * @param $path string | null
	 *
	 * @return array
	 */
	public function getMultiple($keys, $path=null){

		$result = [];

		if(!is_array($keys) || empty($keys)){
			return $result;
		}

		if(is_null($path)){ $path = $this->options['path']; }

		$path = str_replace(':', '/', $path);

		$filepath = $this->getRoot().$path.'/';

		$cache = null;
		$time = time();

		foreach($keys as $key){

			$k = $this->makeKey($key);

			$filename = $filepath.$k.'.php';

			if(isset($this->local[$path.$k])){
				$result[$k] = $this->local[$path.$k]['cache'];
			}

			if(!file_exists($filename)){
				continue;
			}

			include($filename);

			if(isset($expire) && $expire<=$time){
				@unlink($filename);
				continue;
			}

			$this->local[$path.$k] = [
				'cache' => $cache,
				'time' => $time
			];

			$result[$path.$k] = $cache;
		}

		return $result;
	}

	/**
	 * Кэширует значение в файловое хранилище
	 *
	 * @param $key mixed
	 * @param $value mixed
	 * @param $expire integer | null
	 * @param $path string | null
	 *
	 * @return mixed
	 */
	public function set($key, $value, $expire=null, $path=null){

		if(is_null($path)){ $path = $this->options['path']; }

		$path = str_replace(':', '/', $path);

		$key = $this->makeKey($key);

		$filepath = $this->getRoot().$path;

		$time = time();

		$data = '<?php // Last update: '.date("d.m.Y H:i:s").PHP_EOL.PHP_EOL;
		$data .= '$cache = '.var_export($value, true).';'.PHP_EOL.PHP_EOL;
		$data .= '$time = '.$time.';'.PHP_EOL.PHP_EOL;
		if(!is_null($expire)){
			$data .= '$expire = '.($time+$expire).';'.PHP_EOL.PHP_EOL;
		}else{
			$data .= '$expire = 0;'.PHP_EOL.PHP_EOL;
		}
		$data .= '?>';

		if(!file_exists($filepath)){ mkdir($filepath, 0755, true); }

		$filename = "{$filepath}/{$key}.php";

		file_put_contents($filename, $data);

		$this->local[$path.$key] = [
			'cache' => $value,
			'time' => $time
		];

		return $value;
	}

	/**
	 * Кэширует значения в файловое хранилище, используя ассоциотивный массив
	 *
	 * @param $params array
	 * @param $expire integer | null
	 * @param $path string | null
	 *
	 * @return array
	 */
	public function setMultiple($params, $expire=null, $path=null){

		$result = [];

		if(!is_array($params) || empty($params)){
			return $result;
		}

		if(is_null($path)){ $path = $this->options['path']; }

		$path = str_replace(':', '/', $path);

		$filepath = $this->getRoot().$path;

		$time = time();
		$date = date("d.m.Y H:i:s");

		foreach($params as $k => $v){

			$data = '<?php // Last update: '.$date.PHP_EOL.PHP_EOL;
			$data .= '$cache = '.var_export($v, true).';'.PHP_EOL.PHP_EOL;
			$data .= '$time = '.$time.';'.PHP_EOL.PHP_EOL;
			$data .= '$expire = '.($time+$expire).';'.PHP_EOL.PHP_EOL;
			$data .= '?>';

			$key = $this->makeKey($k);

			$filename = "{$filepath}/{$key}.php";

			file_put_contents($filename, $data);

			$this->local[$path.$key] = [
				'cache' => $v,
				'time' => $time
			];

			$result[$path.$key] = $v;
		}

		return $result;
	}

	/**
	 * Удаляет кэшируемое значение из файлового хранилища
	 *
	 * @param $key mixed
	 * @param $path string | null
	 *
	 * @return boolean
	 */
	public function remove($key, $path=null){

		if(is_null($path)){ $path = $this->options['path']; }

		$path = str_replace(':', '/', $path);

		$key = $this->makeKey($key);

		$filename = $this->getRoot().$path.'/'.$key.'.php';

		if(isset($this->local[$path.$key])){
			unset($this->local[$path.$key]);
		}

		if(file_exists($filename)){
			@unlink($filename);
		}

		return true;
	}

	/**
	 * Удаляет кэшируемые значения из файлового хранилища, используя массив ключей
	 *
	 * @param $keys array
	 * @param $path string | null
	 *
	 * @return array
	 */
	public function removeMultiple($keys, $path=null){

		if(is_null($path)){ $path = $this->options['path']; }

		$path = str_replace(':', '/', $path);

		$filepath = $this->getRoot().$path;

		$result = [];

		foreach($keys as $k){

			$key = $this->makeKey($k);

			$filename = $filepath.'/'.$key.'.php';

			if(isset($this->local[$path.$k])){
				unset($this->local[$path.$key]);
			}

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
	 * @param $path string | null
	 *
	 * @return integer
	 */
	public function clear($path=null){

		if(is_null($path)){ $path = $this->options['path']; }

		$path = str_replace(':', '/', $path);

		$filepath = $this->getRoot().$path;

		$num = 0;

		if(!file_exists($filepath)){
			return $num;
		}

		foreach(scandir($filepath) as $v){
			if($v=='.' || $v=='..'){ continue; }

			$filename = "{$filepath}/{$v}";

			$num++;

			if(!is_file($filename)){
				$this->clear("{$path}/$v");
				@rmdir($filename);
				continue;
			}

			@unlink($filename);
		}

		return $num;
	}

	/**
	 * Возвращает экземпляр текущего класса File
	 *
	 * @return $this
	 */
	public function getInstance(){
		return $this;
	}
}

?>