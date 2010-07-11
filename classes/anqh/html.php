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
	 * Print user avatar
	 *
	 * @param   string  $avatar
	 * @param   string  $title
	 * @param   bool    $mini
	 * @return  string
	 */
	public static function avatar($avatar, $title = '', $mini = false) {
		if (empty($avatar) || /*strpos($avatar, ':') ||*/ strpos($avatar, '/') === false) $avatar = 'avatar/unknown.png';
		$class = $mini ? 'avatar small' : 'avatar';

		if (empty($title)) {
			return '<div class="' . $class . '">' . HTML::image($avatar, array('alt' => 'Avatar')) . '</div>';
		} else {
			return '<div class="' . $class . '">' . HTML::anchor(URL::user($title), HTML::image($avatar, array('title' => $title, 'alt' => $title))) . '</div>';
		}
	}


	/**
	 * Prints date box
	 *
	 * @param   string|integer  $date
	 * @param   boolean         $show_year
	 * @param   string          $class
	 * @return  string
	 */
	public static function box_day($date, $show_year = false, $class = '') {

		// Get date elements
		$date = !is_numeric($date) ? strtotime($date) : (int)$date;
		list($weekday, $day, $month, $year) = explode(' ', date('D d M y', $date));
		if ($show_year) {
			$month .= " '" . $year;
		}

		// Today?
		if (date('Y-m-d', $date) == date('Y-m-d')) {
			$class .= ' date today';
			$weekday = __('Today');
		} else {
			$class .= ' date';
		}

		$template = '<span class="weekday">%s</span><span class="day">%s</span><span class="month">%s</span>';

		return self::time(sprintf($template, $weekday, $day, $month), array('class' => trim($class), 'datetime' => $date), true);
	}


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
			: (array)$errors;

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
	 * Ratings
	 *
	 * @static
	 * @param   integer  $total
	 * @param   integer  $count
	 * @param   boolean  $rate   allow rating
	 * @return  string
	 */
	public static function rating($total, $count, $rate = false) {
		return View::factory('generic/rating', array(
			'total' => $total,
			'count' => $count,
			'rate'  => $rate
		));
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
	 * Creates a style sheet link element.
	 *
	 *     echo HTML::style('media/css/screen.css');
	 *
	 * @param   string  file name
	 * @param   array   default attributes
	 * @param   boolean  include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    HTML::attributes
	 */
	public static function style($file, array $attributes = null, $index = false) {

		// Add the base URL
		if (strpos($file, '://') === FALSE) {
			$file = URL::base($index).$file;
		}

		// Set the stylesheet link
		$attributes['href'] = $file;

		// Set the stylesheet rel
		$attributes['rel'] = Arr::get($attributes, 'rel', 'stylesheet');

		// Set the stylesheet type
		$attributes['type'] = Arr::get($attributes, 'type', 'text/css');

		return '<link'.HTML::attributes($attributes).' />';
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

		return empty($nick) ? __('Unknown') : HTML::anchor(URL::user($nick), $nick, array('class' => implode(' ', $class)));
	}

}
