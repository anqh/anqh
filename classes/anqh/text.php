<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Text helper
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Text extends Kohana_Text {

	/**
	 * Capitalize initials
	 *
	 * @static
	 * @param   string  $str
	 * @return  string
	 */
	public static function capitalize($str) {
		return mb_convert_case($str, MB_CASE_TITLE, Kohana::$charset);
	}


	/**
	 * Recursively cleans arrays, objects, and strings. Removes ASCII control
	 * codes and converts to the requested charset while silently discarding
	 * incompatible characters.
	 *
	 * @param   mixed  $str
	 * @return  string
	 */
	public static function clean($str) {
		if (is_array($str) || is_object($str)) {
			foreach ($str as $key => $val) {
				$str[$key] = self::clean($val);
			}
		} else if (is_string($str) && $str !== '') {
			$str = mb_strtolower(UTF8::strip_ascii_ctrl($str));
			if (!UTF8::is_ascii($str)) {
				$str = strtolower(self::transliterate_to_ascii($str));
			}
			if (!UTF8::is_ascii($str)) {
				$str = UTF8::strip_non_ascii($str);
			}
		}

		return $str;
	}


	/**
	 * Return text with smileys
	 *
	 * @param  string  $text
	 */
	public static function smileys($text) {
		static $smileys;

		// Load smileys
		if (!is_array($smileys)) {
			$smileys = array();

			$config = Kohana::config('site.smiley');
			if (!empty($config)) {
				$url = /*URL::base() .*/ $config['dir'] . '/';
				foreach ($config['smileys'] as $name => $smiley) {
					$smileys[$name] = HTML::image($url . $smiley['src'], array('class' => 'smiley', 'alt' => HTML::chars($name), 'title' => HTML::chars($name)));
				}
			}

		}

		// Smile!
		return empty($smileys) ? $text : str_replace(array_keys($smileys), $smileys, $text);
	}


	/**
	 * Replaces special/accented UTF-8 characters by ASCII-7 'equivalents'.
	 *
	 * @param   string   string to transliterate
	 * @param   integer  -1 lowercase only, +1 uppercase only, 0 both cases
	 * @return  string
	 */
	public static function transliterate_to_ascii($str, $case = 0) {
		static $UTF8_SPECIAL_CHARS = NULL;

		if ($UTF8_SPECIAL_CHARS === null) {
			$UTF8_SPECIAL_CHARS = array(
				'⁰' => '0', '₀' => '0', '¹' => '1', 'ˡ' => 'l', '₁' => '1', '²' => '2', '₂' => '2',
				'³' => '3', '₃' => '3', '⁴' => '4', '₄' => '4', '⁵' => '5', '₅' => '5', '⁶' => '6',
				'₆' => '6', '⁷' => '7', '₇' => '7', '⁸' => '8', '₈' => '8', '⁹' => '9', '₉' => '9',
				'¼' => '1/4', '½' => '1/2', '¾' => '3/4', '⅓' => '1/3', '⅔' => '2/3', '⅕' => '1/5',
				'⅖' => '2/5', '⅗' => '3/5', '⅘' => '4/5', '⅙' => '1/6', '⅚' => '5/6', '⅛' => '1/8',
				'⅜' => '3/8', '⅝' => '5/8', '⅞' => '7/8', '⅟' => '1/', '⁺' => '+', '₊' => '+',
				'⁻' => '-', '₋' => '-', '⁼' => '=', '₌' => '=', '⁽' => '(', '₍' => '(', '⁾' => ')', '₎' => ')',
				'ª' => 'a', '@' => 'a', '€' => 'e', 'ⁿ' => 'n', '°' => 'o', 'º' => 'o', '¤' => 'o', 'ˣ' => 'x',
				'ʸ' => 'y', '$' => 'S', '©' => '(c)', '℠' => 'SM', '℡' => 'TEL', '™' => 'TM',
				'ä' => 'ae', 'Ä' => 'Ae', 'ö' => 'oe', 'Ö' => 'Oe', 'ü' => 'ue', 'Ü' => 'eE', 'å' => 'aa', 'Å' => 'Aa',
			);
		}

		$str = str_replace(
			array_keys($UTF8_SPECIAL_CHARS),
			array_values($UTF8_SPECIAL_CHARS),
			$str
		);

		return UTF8::transliterate_to_ascii($str, $case);
	}

}
