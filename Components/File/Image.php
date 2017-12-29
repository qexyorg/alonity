<?php
/**
 * Image component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 *
 */

namespace Alonity\Components;

use ImageException;

require_once(__DIR__.'/ImageException.php');

class Image {

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

	private static function restructFiles($files){
		if(!is_array($files) || empty($files)){
			throw new ImageException("param files must be array");
		}

		$result = [];

		foreach(self::$complection as $v){
			if(!isset($files[$v])){
				throw new ImageException("file is not complected");
				break;
			}

			if(empty($files[$v])){
				throw new ImageException("file must be not empty");
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
	 * Загружает изображения на сервер. Исходное изображение будет создано заново библиотекой GD
	 * Для загрузки используется формат $_FILES
	 * Возвращает массив полных путей до загруженных файлов
	 *
	 * @param $files array
	 * @param $options array
	 *
	 * @throws ImageException
	 *
	 * @return array
	 */
	public static function upload($files, $options=[]){

		if(!is_array($options)){
			throw new ImageException('param options must be array');
		}

		if(!is_array($files)){
			throw new ImageException("param files must be array");
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

		$files = self::restructFiles($files);

		if(!file_exists($options['dir'])){
			@mkdir($options['dir'], 0755, true);
		}

		$options['dir'] = self::getRoot().$options['dir'];

		$result = [];

		$num = 0;

		foreach($files as $k => $file){

			$num++;

			if($options['maxsize']>0 && $file['size']>$options['maxsize']){
				throw new ImageException("max file size is {$options['maxsize']}b");
			}

			if(!empty($file['error'])){
				throw new ImageException($file['error']);
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
					throw new ImageException('upload only for files '.implode(', ', $options['extensions']));
				}
			}

			$newpath = "{$options['dir']}/$newname";

			@move_uploaded_file($file['tmp_name'], $newpath);

			$size = @getimagesize($newpath);

			if(!$size){
				@unlink($newpath);
				throw new ImageException("<b>image is not valid $newpath</b>");
			}

			switch($size['mime']){
				case 'image/png': $image = @imagecreatefrompng($newpath); break;
				case 'image/bmp':
					if(!function_exists('imagecreatefrombmp')){ continue; }
					$image = @imagecreatefrombmp($newpath);
				break;
				case 'image/gif': $image = @imagecreatefromgif($newpath); break;
				case 'image/jpeg': $image = @imagecreatefromjpeg($newpath); break;
				case 'image/vnd.wap.wbmp': $image = @imagecreatefromwbmp($newpath); break;
				case 'image/webp': $image = @imagecreatefromwebp($newpath); break;
				case 'image/xbm': $image = @imagecreatefromxbm($newpath); break;

				default: throw new ImageException("image is not valid"); break;
			}

			if(!$image){
				throw new ImageException("image can't be created");
			}

			@imagealphablending($image, true);
			@imagesavealpha($image, true);

			switch($size['mime']){
				case 'image/png': @imagepng($image, $newpath, 0); break;
				case 'image/bmp':
					if(!function_exists('imagebmp')){ continue; }
					@imagebmp($image, $newpath, false);
				break;
				case 'image/gif': @imagegif($image, $newpath); break;
				case 'image/jpeg': @imagejpeg($image, $newpath, 100); break;
				case 'image/vnd.wap.wbmp': @imagewbmp($image, $newpath); break;
				case 'image/webp': @imagewebp($image, $newpath, 100); break;
				case 'image/xbm': @imagexbm($image, $newpath); break;

				default: throw new ImageException("image is not valid"); break;
			}

			@imagedestroy($image);

			$result[] = $newpath;

			if($options['maxfiles']>0 && $options['maxfiles']>=$num){
				break;
			}
		}

		return $result;
	}

	/**
	 * Проверяет, является ли файл изображением. Необходимо PHP расширение GD!
	 * Внимание! Данный метод не проверяет наличие в файле постороннего кода
	 *
	 * @param $filename string
	 * @param $types array | string
	 *
	 * @return boolean
	*/
	public static function isImage($filename, $types=[]){

		if(!file_exists($filename)){
			return false;
		}

		if(!empty($types)){
			$ext = pathinfo($filename, PATHINFO_EXTENSION);

			if(is_array($types)){
				if(!in_array($ext, $types)){
					return false;
				}
			}elseif(is_string($types)){
				if($types!=$ext){
					return false;
				}
			}
		}

		$gis = @getimagesize($filename);

		if(!$gis){
			return false;
		}

		return true;
	}
}

?>