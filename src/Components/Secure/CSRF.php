<?php
/**
 * Secure CSRF component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Framework\Components\Secure;

class CSRF {

	private static $ip = null;

	private static $salt = '(18100*cj1&&1';

	private static $token = null;

	public static function setSalt($salt){
		self::$salt = $salt;
	}

	public static function getSalt(){
		return self::$salt;
	}

	public static function isValidToken($token){
		return (self::getToken() === $token);
	}

	public static function getToken(){

		if(!is_null(self::$token)){
			return self::$token;
		}

		self::$token = md5(self::getSalt().self::getIP()).'_'.hash('sha256', self::getIP().self::getSalt());

		return self::$token;
	}

	private static function getIP(){
		if(!is_null(self::$ip)){
			return self::$ip;
		}

		if(!empty($_SERVER['HTTP_CF_CONNECTING_IP'])){
			self::$ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
		}elseif(!empty($_SERVER['HTTP_X_REAL_IP'])){
			self::$ip = $_SERVER['HTTP_X_REAL_IP'];
		}elseif(!empty($_SERVER['HTTP_CLIENT_IP'])){
			self::$ip = $_SERVER['HTTP_CLIENT_IP'];
		}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			self::$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}else{
			self::$ip = $_SERVER['REMOTE_ADDR'];
		}

		self::$ip = mb_substr(self::$ip, 0, 16, "UTF-8");

		return self::$ip;
	}

	/**
	 * Преобразует строку в массив
	 *
	 * @param $string string
	 *
	 * @return array
	 */
	private static function toArray($string){
		$len = mb_strlen($string, 'UTF-8');

		$result = [];

		for($i=0; $i<$len; $i++){
			$result[] = mb_substr($string, $i, 1, 'UTF-8');
		}

		return $result;
	}

	/**
	 * Возвращает случайную строку из латинских букв, цифр, кириллических букв, знаков
	 *
	 * @param $length integer
	 *
	 * @return string
	 */
	public static function generateSalt($length = 32){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789';
		$chars .= '!~`@#$%^&*()_+=-?:;][/.,';
		$chars .= 'абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';

		$symbols = mb_strlen($chars, 'UTF-8')-1;

		$chars = self::toArray($chars);

		$string = '';

		for($i=0;$i<$length;$i++){
			$string .= $chars[mt_rand(0, $symbols)];
		}

		return $string;
	}
}

?>