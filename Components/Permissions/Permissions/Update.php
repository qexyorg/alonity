<?php
/**
 * Permissions update component of Alonity Framework
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

namespace Alonity\Components\Permissions;

class PermissionsUpdateException extends \Exception {}

class Update {
	private $data = [];

	private $filename = __DIR__;

	private $updated = false;

	public function __construct($data){
		$this->data = $data;

		$this->filename = dirname(dirname(dirname(__DIR__))).'/Uploads/permissions/default.php';
	}

	private static function hashed($name){
		return md5($name);
	}

	/**
	 * Устанавливает новые данные привилегии
	 *
	 * @param $name mixed
	 * @param $value mixed
	 * @param $title mixed
	 * @param $text mixed
	 * @param $default mixed
	 *
	 * @throws PermissionsUpdateException
	 *
	 * @return \Alonity\Components\Permissions\Update()
	*/
	public function set($name, $value=null, $title=null, $text=null, $default=null){
		$key = $this->hashed($name);

		if(!is_null($value)){
			$this->data[$key]['value'] = $value;
		}

		if(!is_null($title)){
			$this->data[$key]['title'] = strval($title);
		}

		if(!is_null($text)){
			$this->data[$key]['text'] = strval($text);
		}

		if(!is_null($default)){
			$this->data[$key]['default'] = $default;
		}

		if(!isset($this->data[$key]['value'])){
			throw new PermissionsUpdateException('index "value" is not set');
		}

		if(!isset($this->data[$key]['default'])){
			$this->data[$key]['default'] = $this->data[$key]['value'];
		}

		if(!isset($this->data[$key]['title'])){
			$this->data[$key]['title'] = strval($name);
		}

		if(!isset($this->data[$key]['text'])){
			$this->data[$key]['text'] = '';
		}

		$this->updated = true;

		return $this;
	}

	/**
	 * Удаляет привилегию по имени
	 *
	 * @param $name mixed
	 *
	 * @return \Alonity\Components\Permissions\Update()
	 */
	public function delete($name){
		$key = $this->hashed($name);

		if(isset($this->data[$key])){
			$this->updated = true;

			unset($this->data[$key]);
		}

		return $this;
	}

	/**
	 * Устанавливает имя файла привилегий
	 *
	 * @param $filename string
	 *
	 * @return \Alonity\Components\Permissions\Update()
	 */
	public function filename($filename){
		$this->filename = $filename;

		$this->updated = true;

		return $this;
	}

	/**
	 * Сохраняет выполненные настройки
	*/
	public function execute(){
		if(!$this->updated){
			return true;
		}

		$data = "<?php // ".date("d.m.Y H:i:s").PHP_EOL.PHP_EOL;
		$data .= '	return '.var_export($this->data, true).';'.PHP_EOL.PHP_EOL;
		$data .= '?>';

		$dir = dirname($this->filename);

		if(!file_exists($dir) || !is_dir($dir)){
			@mkdir($dir, 0777, true);
		}

		@file_put_contents($this->filename, $data);

		return true;
	}
}

?>