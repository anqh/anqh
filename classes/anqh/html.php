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
				$title = date::format($short ? 'DMYYYY' : 'DMYYYY_HM', $time);
				if ($title != $str) {
					$attributes['title'] = date::format($short ? 'DMYYYY' : 'DMYYYY_HM', $time);
				}
			}

		}

		return '<time' . html::attributes($attributes) . '>' . $str . '</time>';
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
