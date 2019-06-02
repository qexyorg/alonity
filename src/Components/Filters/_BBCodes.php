<?php
/**
 * BBCodes filter component of Alonity Framework
 *
 * @author Qexy <admin@qexy.org>
 * @copyright Copyright (c) 2019, Qexy
 * @link http://qexy.org
 *
 * @license https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @version 2.1.0
 */

namespace Framework\Components\Filters;

class _BBCodes {

	private static $patterns = null;

	private static function preg_replace_recursive($pattern, $replacement, $string){

		$string = preg_replace($pattern, $replacement, $string);

		return (!preg_match($pattern, $string)) ? $string : self::preg_replace_recursive($pattern, $replacement, $string);
	}

	private static function codeparse($string){
		return preg_replace_callback("/\[code(=\"(\w+)\")?\](.*)\[\/code\]/isU", function($matches){
			$str = str_replace(['[', ']'], ['&#91;', '&#93;'], $matches[3]);

			if(!empty($matches[2])){
				return '<div class="bb-code"><div class="bb-code-language">'.$matches[2].'</div><div class="bb-code-text">'.$str.'</div></div>';
			}else{
				return '<div class="bb-code">'.$str.'</div>';
			}
		}, $string);
	}

	public static function parse($string, $specialchars=true){
		if($specialchars){
			$string = htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8');
		}

		$string = self::codeparse($string);

		foreach(self::getPatterns() as $pattern => $replace){

			$repl = (is_array($replace) && isset($replace[1])) ? $replace[0] : $replace;

			if(is_array($replace) && isset($replace[1]) && $replace[1]===false){
				$string = preg_replace("/$pattern/isU", $repl, $string);
			}else{
				$string = self::preg_replace_recursive("/$pattern/isU", $repl, $string);
			}
		}

		return nl2br($string);
	}

	public static function addPattern($regexp, $replacement){
		self::$patterns[$regexp] = $replacement;
	}

	public static function delPattern($regexp){
		if(isset(self::$patterns[$regexp])){
			unset(self::$patterns[$regexp]);
		}
	}

	private static function getPatterns(){
		if(!is_null(self::$patterns)){ return self::$patterns; }

		self::$patterns = [
			'\[b\](.*)\[\/b\]' => '<b class="bb-bold">$1</b>',

			'\[i\](.*)\[\/i\]' => '<i class="bb-italic">$1</i>',

			'\[u\](.*)\[\/u\]' => '<u class="bb-underline">$1</u>',

			'\[s\](.*)\[\/s\]' => '<s class="bb-strike">$1</s>',

			'\[left\](.*)\[\/left\]' => '<div class="bb-text-left">$1</div>',

			'\[center\](.*)\[\/center\]' => '<div class="bb-text-center">$1</div>',

			'\[right\](.*)\[\/right\]' => '<div class="bb-text-right">$1</div>',

			'\[line\]' => ['<div class="bb-line"></div>', false],

			'\[youtube\]https\:\/\/www\.youtube\.com\/watch\?v=([\w\-]{5,15})\[\/youtube\]' => ['<iframe class="bb-youtube" width="516" height="290" src="https://www.youtube.com/embed/$1" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>', false],

			'\[spoiler\](.*)\[\/spoiler\]' => '<div class="bb-spoiler-wrapper"><div class="bb-spoiler"><a href="#" class="bb-spoiler-trigger">Спойлер</a><div class="bb-spoiler-text">$1</div></div></div>',

			'\[spoiler="([^"\>\<\n]+)"\](.*)\[\/spoiler\]' => '<div class="bb-spoiler-wrapper"><div class="bb-spoiler"><a href="#" class="bb-spoiler-trigger">$1</a><div class="bb-spoiler-text">$2</div></div></div>',

			'\[color="#([0-9a-f]{6})"\](.*)\[\/color\]' => '<span class="bb-color" style="color: #$1;">$2</span>',

			'\[size="([1-6])"\](.*)\[\/size\]' => '<h$1 class="bb-size-$1">$2</h$1>',

			'\[img\]((?:f|ht)(?:tp)s?\:\/\/[^\s]+)\[\/img\]' => ['<img class="bb-image" src="$1" alt="IMAGE" />', false],

			'\[quote\](.*)\[\/quote\]' => '<div class="bb-quote-wrapper"><div class="bb-quote"><div class="bb-quote-text">$1</div></div></div>',

			'\[quote="([^\n"\>\<]+)"\](.*)\[\/quote\]' => '<div class="bb-quote-wrapper"><div class="bb-quote"><div class="bb-quote-title">$1</div><div class="bb-quote-text">$2</div></div></div>',

			'\[url="((?:f|ht)(?:tp)s?\:\/\/[^\s"\n]+)"\](.*)\[\/url\]' => ['<a class="bb-url" href="$1">$2</a>', false],

			'\[url\]((?:f|ht)(?:tp)s?\:\/\/[^\s"]+)\[\/url\]' => ['<a class="bb-url" href="$1">$1</a>', false],
		];

		return self::$patterns;
	}
}

?>