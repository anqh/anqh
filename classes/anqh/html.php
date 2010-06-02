<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * HTML
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_HTML extends Kohana_HTML {

	/**
	 * Returns errors
	 *
	 * @param  string|array  $error(s)
	 * @param  string        $filters
	 * @return string
	 */
	public static function error($errors = false) {
		if (empty($errors)) {
			return '';
		}

		// More than one argument = filters
		if (func_num_args() > 1) {
			$argv = func_get_args();
			$filters = is_array($argv[1]) ? $argv[1] : array_slice($argv, 1);
		}

		$error = is_array($errors)
			? (empty($filters) ? $errors : array_intersect_key($errors, array_flip($filters)))
			: $errors;

		return empty($error) ? '' : '<span class="info">' . implode('<br />', $error). '</span>';
	}


	/**
	 * Print icon with value
	 *
	 * @param  integer|array  $value     :var => value
	 * @param  string         $singular  title for singular value
	 * @param  string         $plural    title for plural value
	 * @param  string         $class     icon class
	 */
	public static function icon_value($value, $singular = '', $plural = '', $class = '') {
		$class = $class ? 'icon ' . $class : 'icon';
		if (is_array($value)) {
			$var = key($value);
			$value = $value[$var];
		}
		$formatted = Num::format($value, 0);
		$plural = $plural ? $plural : $singular;
		$title = ($singular && $plural) ? ' title="' . __2($singular, $plural, $value, array($var => $formatted)) . '"' : '';

		return '<var class="' . $class . '"' . $title . '>' . $formatted . '</var>';
	}


	/**
	 * JavaScript source code block
	 *
	 * @param   string  $source
	 * @return  string
	 */
	public static function script_source($source) {
		$compiled = '';

		if (is_array($source)) {
			foreach ($source as $script) {
				$compiled .= HTML::script_source($script);
			}
		} else {
			$compiled = implode("\n", array('<script>', /*'// <![CDATA[',*/ trim($source), /*'// ]]>',*/ '</script>'));
		}
		return $compiled;
	}


	/**
	 * Return formatted <time> tag
	 *
	 * @param  string        $str
	 * @param  array|string  $attributes  handled as time if not an array
	 * @param  boolean       $short       use only date
	 */
	public static function time($str, $attributes = null, $short = false) {

		// Extract datetime
		$datetime = (is_array($attributes)) ? Arr::get_once($attributes, 'datetime') : $attributes;
		if ($datetime) {
			$time = is_int($datetime) ? $datetime : strtotime($datetime);
			$datetime = Date::format($short ? Date::DATE_8601 : Date::TIME_8601, $time);
			if (is_array($attributes)) {
				$attributes['datetime'] = $datetime;
			} else {
				$attributes = array('datetime' => $datetime);
			}

			// Set title if not the same as content
			if (!isset($attributes['title'])) {
				$title = Date::format($short ? 'DMYYYY' : 'DMYYYY_HM', $time);
				if ($title != $str) {
					$attributes['title'] = Date::format($short ? 'DMYYYY' : 'DMYYYY_HM', $time);
				}
			}

		}

		return '<time' . HTML::attributes($attributes) . '>' . $str . '</time>';
	}


	/**
	 * Returns user link
	 *
	 * @param	  mixed   $user  Model_User, uid or username
	 * @param	  string  $nick
	 * @param   string  $class
	 * @return  string
	 */
	public static function user($user, $nick = null, $class = null) {
		static $viewer = false;

		// Load current user for friend styling
		if ($viewer === false) {
			$viewer = Visitor::instance()->get_user();
		}

		$class = $class ? array($class, 'user') : array('user');

		if ($user instanceof Model_user || $user && $user = Model::factory('user')->find_user($user)) {
			$nick = $user->username;
			if ($viewer && $viewer->is_friend($user)) {
				$class[] = 'friend';
			}
			if ($user->gender) {
				$class[] = $user->gender == 'f' ? 'female' : 'male';
			}
		}

		return empty($nick) ? __('Unknown') : html::anchor(url::user($nick), $nick, array('class' => implode(' ', $class)));
	}

}
