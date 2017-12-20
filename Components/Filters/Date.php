<?php
/**
 * Date filter component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2017, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 1.0.0
 */

namespace Alonity\Components\Filters;

class FilterDateException extends \Exception {}

class _Date {

	/**
	 * Преобразует секунды в кол-во оставшихся лет/месяцев/дней/часов/минут/секунд
	 *
	 * @param $seconds integer
	 *
	 * @return array
	*/
	public static function expire($seconds){
		$seconds = intval($seconds);

		return [
			'y' => intval($seconds / 31536000),
			'm' => intval(($seconds % 31536000) / 2592000),
			'w' => intval(($seconds % 2592000) / 604800),
			'd' => intval(($seconds % 604800) / 86400),
			'h' => intval(($seconds % 86400) / 3600),
			'min' => intval(($seconds % 3600) / 60),
			's' => intval($seconds % 60)
		];
	}

	/**
	 * Возвращает строку в правильном падеже в зависимости от числа
	 *
	 * @param $number integer
	 * @param $n1 string
	 * @param $n2 string
	 * @param $other string
	 *
	 * @return string
	*/
	public static function toCase($number, $n1='', $n2='', $other=''){
		$number = intval($number);

		if($number>20){ $number = $number % 10; }

		if($number==1){
			return $n1;
		}elseif($number==2 || $number==3 || $number==4){
			return $n2;
		}

		return $other;
	}

	private static function toFormatBefore($time, $default){
		$now = time();

		$seconds = $now-$time;

		if($seconds<5){
			return 'только что';
		}

		if($seconds<60){
			return $seconds.' '.self::toCase($seconds, 'секунду', 'секунды', 'секунд').' назад';
		}

		$minutes = intval($seconds / 60);

		if($seconds<3600){
			return $minutes.' '.self::toCase($minutes, 'минуту', 'минуты', 'минут').' назад';
		}

		if(date("dmY")===date("dmY", $time)){
			return 'сегодня в '.date("H:i", $time);
		}elseif(date('dmY', strtotime("-1 days"))===date("dmY", $time)){
			return 'вчера в '.date("H:i", $time);
		}else{
			return self::writeOut($time, $default);
		}
	}

	private static function toFormatAfter($time, $default){
		$now = time();

		$seconds = $now-$time;

		if($seconds>-5){
			return 'сейчас';
		}

		if($seconds>-60){
			return 'через '.$seconds.' '.self::toCase($seconds, 'секунду', 'секунды', 'секунд');
		}

		$minutes = intval($seconds / 60)*-1;

		if($seconds>-3600){
			return 'через '.$minutes.' '.self::toCase($minutes, 'минуту', 'минуты', 'минут');
		}

		if(date("dmY")===date("dmY", $time)){
			return 'сегодня в '.date("H:i", $time);
		}elseif(date('dmY', strtotime("+1 days"))===date("dmY", $time)){
			return 'завтра в '.date("H:i", $time);
		}else{
			return self::writeOut($time, $default);
		}
	}

	/**
	 * Приводит дату к пользовательскому виду
	 *
	 * @param $time integer | null
	 * @param $default string
	 *
	 * @throws FilterDateException
	 *
	 * @return string
	*/
	public static function toFormat($time=null, $default="d F Y в H:i"){
		$now = time();

		if(!is_string($default)){
			throw new FilterDateException('param default must be a string');
		}

		if(is_null($time)){ $time = $now; }

		$time = intval($time);

		return (($now-$time)>=0) ? self::toFormatBefore($time, $default) : self::toFormatAfter($time, $default);
	}

	/**
	 * Возвращает дату в нужном формате с переводом названий месяцев
	 *
	 * @param $time integer
	 * @param $format string
	 *
	 * @return string
	*/
	public static function writeOut($time=0, $format="d F Y в H:i"){
		$time = intval($time);
		$date = date($format, $time);

		$search = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

		$replace = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');

		$date = str_ireplace($search, $replace, $date);

		return $date;
	}
}

?>