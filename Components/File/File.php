<?php
/**
 * File component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.1.0
 */

namespace Alonity\Components;

use FileException;

require_once(__DIR__.'/FileException.php');

class File {
	private static $cache = [];

	private static $root = null;

	private static $complection = ['name', 'type', 'error', 'tmp_name', 'size'];

	public static function setRoot($dir){
		self::$root = $dir;
	}

	private static function getRoot(){
		if(!is_null(self::$root)){ return self::$root; }

		self::setRoot(dirname(dirname(__DIR__)));

		return self::$root;
	}

	/**
	 * Получает значения конфигурационного файла и локально кэширует полученный результат
	 *
	 * @param $name string
	 *
	 * @throws FileException
	 *
	 * @return mixed
	*/
	public static function getConfig($name){
		$key = md5($name);

		if(isset(self::$cache[$key])){
			return self::$cache;
		}

		$filename = self::getRoot().$name;

		if(!file_exists($filename) || !is_file($filename)){
			throw new FileException("File not found in $filename");
		}

		self::$cache[$key] = (include($filename));

		return self::$cache[$key];
	}

	/**
	 * Устанавливает значение в файл конфигурации. Если файл и/или диреткория не были найдены, то они будут созданы
	 *
	 * @param $name string
	 * @param $data mixed
	 *
	 * @return mixed
	*/
	public static function setConfig($name, $data){
		$key = md5($name);

		$filename = self::getRoot().$name;

		$dir = dirname($filename);

		if(!file_exists($dir)){
			@mkdir($dir, 0755, true);
		}

		$date = date("d.m.Y H:i:s");

		$result = var_export($data, true);

		$data = "<?php // Updated: $date".PHP_EOL.PHP_EOL."return $result;".PHP_EOL.PHP_EOL."?>";

		@file_put_contents($filename, $data);

		self::$cache[$key] = $result;

		return $result;
	}

	/**
	 * Удаляет файл или массив файлов
	 *
	 * @param $files array | string
	 *
	 * @throws FileException
	 *
	 * @return boolean
	 */
	public static function removeFiles($files){

		if(empty($files)){
			throw new FileException("param file must be not empty");
		}

		if(is_array($files)){
			foreach($files as $v){
				$filename = self::getRoot().$v;

				if(!file_exists($filename)){
					continue;
				}

				@unlink($filename);

				if(file_exists($filename)){
					throw new FileException("file $v not removed");
				}
			}
		}else{
			$filename = self::getRoot().$files;

			if(!file_exists($filename)){
				return true;
			}

			@unlink($filename);

			if(file_exists($filename)){
				throw new FileException("file $files not removed");
			}
		}



		return true;
	}

	/**
	 * Удаляет директорию или директории рекурсивно
	 *
	 * @param $dir array | string
	 *
	 * @throws FileException
	 *
	 * @return boolean
	*/
	public static function removeDir($dir){

		$root = self::getRoot();

		if(empty($dir)){
			return true;
		}

		if(is_array($dir)){
			foreach($dir as $v){
				self::removeDir($v);
			}
		}else{
			$dirname = $root.$dir;

			if(!file_exists($dirname)){
				return true;
			}

			$scan = scandir($dirname);

			unset($scan[0], $scan[1]);

			if(empty($scan)){
				rmdir($dirname);

				return true;
			}

			foreach($scan as $v){

				if(is_dir($dirname.'/'.$v)){
					$rescan = scandir($dirname.'/'.$v);

					unset($rescan[0], $rescan[1]);

					if(!empty($rescan)){
						self::removeDir($dir.'/'.$v);

						continue;
					}

					rmdir($dirname.'/'.$v);
				}else{
					@unlink($dirname.'/'.$v);

					if(file_exists($dirname.'/'.$v)){
						throw new FileException("file $v not removed");
					}
				}
			}

			rmdir($dirname);
		}

		return true;
	}

	private static function restructFiles($files){
		if(!is_array($files) || empty($files)){
			throw new FileException("param files must be array");
		}

		$result = [];

		foreach(self::$complection as $v){
			if(!isset($files[$v])){
				throw new FileException("file is not complected");
				break;
			}

			if(empty($files[$v])){
				throw new FileException("file must be not empty");
				break;
			}

			if(is_array($files[$v])){
				foreach($files[$v] as $k => $val){
					$result[$k][$v] = $val;
				}
			}else{
				$result[0][$v] = $val;
			}
		}

		return $result;
	}

	/**
	 * Загружает файлы на сервер. Для загрузки используется формат $_FILES
	 * Возвращает массив полных путей до загруженных файлов
	 *
	 * @param $files array
	 * @param $options array
	 *
	 * @throws FileException
	 *
	 * @return array
	*/
	public static function upload($files, $options=[]){

		if(!is_array($options)){
			throw new FileException('param options must be array');
		}

		if(!is_array($files)){
			throw new FileException("param files must be array");
		}

		if(!isset($options['extensions'])){
			$options['extensions'] = [];
		}

		if(!isset($options['maxsize'])){
			$options['maxsize'] = 0;
		}

		if(!isset($options['rename'])){
			$options['rename'] = false;
		}

		if(!isset($options['dir'])){
			$options['dir'] = '/Uploads/files';
		}

		if(!isset($options['maxfiles'])){
			$options['maxfiles'] = 0;
		}

		if(!file_exists($options['dir'])){
			@mkdir($options['dir'], 0755, true);
		}

		$options['dir'] = self::getRoot().$options['dir'];

		$files = self::restructFiles($files);

		$result = [];

		$num = 0;

		foreach($files as $k => $file){

			$num++;

			if($options['maxsize']>0 && $file['size']>$options['maxsize']){
				throw new FileException("max file size is {$options['maxsize']}b");
			}

			if(!empty($file['error'])){
				throw new FileException($file['error']);
			}

			$newname = $file['name'];

			$info = pathinfo($file['name']);

			if(is_string($options['rename'])){
				$newname = $options['rename'];
			}elseif($options['rename']===true){
				$newname = md5(mt_rand(0,9999999).mt_rand(0,9999999));
			}

			if(!empty($options['extensions'])){
				if(!in_array($options['extensions'], $info['extension'])){
					throw new FileException('upload only for files '.implode(', ', $options['extensions']));
				}
			}

			@move_uploaded_file($file['tmp_name'], $options['dir'].'/'.$newname);

			$result[] = "{$options['dir']}/$newname";

			if($options['maxfiles']>0 && $options['maxfiles']>=$num){
				break;
			}
		}

		return $result;
	}

	/**
	 * Очищает кэш
	 *
	 * @return void
	*/
	public static function clearCache(){
		self::$cache = [];
	}
}

?>