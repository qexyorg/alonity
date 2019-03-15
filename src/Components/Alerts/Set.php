<?php
/**
 * Alerts Set component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Components\Alerts;

class Set {

	private $session_name = null;

	private $logic = 0x00;

	public function __construct($session_name){
		$this->session_name = $session_name;
	}

	private $messages = [];

	private $redirect = null;

	public function message($text='', $title='', $type=false, $data=null){
		if($this->logic===Alerts::JSON_LOGIC){

			if(is_null($data)){
				$data = [];
			}

			$message = [
				'title' => $title,
				'text' => $text,
				'type' => $type
			];

			$this->messages[] = array_replace_recursive($message, $data);
		}elseif($this->logic===Alerts::HTTP_LOGIC){
			$this->messages[] = [
				'title' => $title,
				'text' => $text,
			];
		}

		return $this;
	}

	public function redirect($url=null){
		if(is_null($url)){
			if(isset($_SERVER['HTTP_REFERER'])){
				$this->redirect = $_SERVER['HTTP_REFERER'];
			}else{
				$this->redirect = '/';
			}
		}else{
			$this->redirect = $url;
		}

		return $this;
	}

	public function execute(){

		if($this->logic===Alerts::HTTP_LOGIC){
			if(is_null($this->redirect)){
				return $this->messages;
			}

			if(!isset($_SESSION)){
				session_start();
			}

			$_SESSION[$this->session_name] = $this->messages;

			header("Location: {$this->redirect}");

		}elseif($this->logic===Alerts::JSON_LOGIC){
			if(empty($this->messages)){
				exit('{}');
			}

			$len = sizeof($this->messages);

			if($len==1){
				echo json_encode($this->messages[0]);
			}else{
				echo json_encode($this->messages);
			}
		}

		exit;
	}

	public function logic($logic){
		$this->logic = $logic;

		return $this;
	}
}

?>