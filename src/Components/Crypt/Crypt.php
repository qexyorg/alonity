<?php
/**
 * Crypt component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2018, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.1.0
 */

namespace Framework\Components\Crypt;

class Crypt {

	/**
	 * Преобразует значение в хэш, используя алгоритмы PHP
	 * @link http://php.net/manual/ru/function.hash.php
	 *
	 * @param $value mixed
	 * @param $type string
	 *
	 * @return string
	 */
	public static function Hash($value, $type){
		return hash($type, $value);
	}

	/**
	 * Преобразует значение в хэш алгоритма MD5
	 *
	 * @param $value mixed
	 *
	 * @return string
	 */
	public static function MD5($value){
		return md5($value);
	}

	/**
	 * Преобразует значение в хэш алгоритма SHA256
	 *
	 * @param $value mixed
	 *
	 * @return string
	 */
	public static function SHA256($value){
		return self::Hash($value, 'sha256');
	}

	/**
	 * Преобразует значение в хэш алгоритма SHA512
	 *
	 * @param $value mixed
	 *
	 * @return string
	 */
	public static function SHA512($value){
		return self::Hash($value, 'sha512');
	}

	/**
	 * Преобразует значение в SHA1 хэш
	 *
	 * @param $value mixed
	 *
	 * @return string
	 */
	public static function SHA1($value){
		return sha1($value);
	}

	/**
	 * Преобразует значение в CRC32 хэш
	 *
	 * @param $value mixed
	 *
	 * @return string
	 */
	public static function CRC32($value){
		return crc32($value);
	}

	/**
	 * Преобразует строку в массив
	 *
	 * @param $string string
	 *
	 * @return array
	 */
	private static function toArray($string){
		return preg_split("//u", $string, -1, PREG_SPLIT_NO_EMPTY);
	}

	/**
	 * Возвращает случайную строку из латинских букв, цифр, кириллических букв, знаков
	 *
	 * @param $min integer
	 * @param $max integer
	 * @param $types array
	 *
	 * @return string
	 */
	public static function random($min=1, $max=10, $types=[]){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789';

		if(in_array('special', $types)){
			$chars .= '!~`@#$%^&*()_+=-?:;][/.,';
		}

		if(in_array('cyrilic', $types)){
			$chars .= 'абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ';
		}

		$symbols = mb_strlen($chars, 'UTF-8')-1;

		$chars = self::toArray($chars);

		$string = '';

		$len = mt_rand($min, $max);

		for($i=0;$i<$len;$i++){
			$string .= $chars[mt_rand(0, $symbols)];
		}

		return $string;
	}

	/**
	 * Возвращает случайную строку из латинских букв и цифр
	 *
	 * @param $min integer
	 * @param $max integer
	 *
	 * @return string
	 */
	public static function randomStringLatin($min=1, $max=10){
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789';

		$symbols = mb_strlen($chars, 'UTF-8')-1;

		$chars = self::toArray($chars);

		$string = '';

		for($i=0;$i<mt_rand($min, $max);$i++){
			$string .= $chars[mt_rand(0, $symbols)];
		}

		return $string;
	}

	/**
	 * Возвращает случайное целое число
	 *
	 * @param $min integer
	 * @param $max integer
	 *
	 * @return integer
	 */
	public static function randomInt($min=1, $max=10){
		if(function_exists('random_int')){
			return random_int($min, $max);
		}

		return mt_rand($min, $max);
	}

	/**
	 * Возвращает случайное число с плавающей запятой
	 *
	 * @param $min integer
	 * @param $max integer
	 *
	 * @return float
	 */
	public static function randomFloat($min=1, $max=10){
		if(function_exists('random_int')){
			$left = random_int($min, $max);
			$right = random_int($min, $max);
		}else{
			$left = mt_rand($min, $max);
			$right = mt_rand($min, $max);
		}

		return floatval($left.'.'.$right);
	}

	/**
	 * Возвращает случайное булевое значение true/false
	 *
	 * @return boolean
	*/
	public static function randomBoolean(){
		return (mt_rand(0, 1)==1);
	}

	/**
	 * Создает хэш пароля используя алгоритм Blowfish
	 *
	 * @param $value string
	 * @param $cost integer
	 *
	 * @throws CryptException
	 *
	 * @return string
	 */
	public static function createPassword($value, $cost=12){

		if(!function_exists('password_hash')){
			throw new CryptException('Function password_hash is not found. Use PHP >=5.5');
		}

		return password_hash($value, PASSWORD_BCRYPT, [
			'cost' => $cost
		]);
	}

	/**
	 * Проверяет пароль с хэшем
	 *
	 * @param $value string
	 * @param $hash string
	 *
	 * @throws CryptException
	 *
	 * @return boolean
	*/
	public static function checkPassword($value, $hash){

		if(!function_exists('password_verify')){
			throw new CryptException('Function password_verify is not found. Use PHP >=5.5');
		}

		return password_verify($value, $hash);
	}
}

?>