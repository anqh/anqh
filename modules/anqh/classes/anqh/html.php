<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * HTML helper.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_HTML extends Kohana_HTML {

	/**
	 * Print user avatar.
	 *
	 * @param   string   $avatar
	 * @param   string   $title
	 * @param   string   $class
	 * @param   boolean  $lazy
	 * @return  string
	 */
	public static function avatar($avatar, $title = '', $class = null, $lazy = true) {
		$placeholder = URL::site('avatar/unknown.png');
		$lazy        = $lazy && !Request::$current->is_ajax();
		if (empty($avatar) || strpos($avatar, '/') === false) {
			$avatar = 'avatar/unknown.png';
		}

		// Absolute uri
		if (strpos($avatar, '//') === false) {
			$avatar = URL::site($avatar);
		}

		if (!$class) {
			$class = 'avatar';
		} else {
			$class = 'avatar ' . ($class === true ? ' small' : $class);
		}

		if (empty($title)) {
			return '<span class="' . $class . '">' . (!$lazy
				? HTML::image($avatar, array('alt' => __('Avatar'), 'class' => 'img-circle'))
				: HTML::image($placeholder, array('alt' => __('Avatar'), 'class' => 'img-circle lazy', 'data-original' => $avatar))
			) . '</span>';
		} else {
			return HTML::anchor(URL::user($title), (!$lazy
				? HTML::image($avatar, array('title' => $title, 'alt' => $title, 'class' => 'img-circle'))
				: HTML::image($placeholder, array('title' => $title, 'alt' => $title, 'class' => 'img-circle lazy', 'data-original' => $avatar))
			), array('class' => $class . ' hoverable'));
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

		if (is_numeric($value)) {

			// Format number
			$formatted = Num::format($value, 0);
			$plural = $plural ? $plural : $singular;
			$title = ($singular && $plural) ? ' title="' . __($value == 1 ? $singular : $plural, array($var => $formatted)) . '"' : '';

		} else {

			// Value is a string, no formatting
			$formatted = HTML::chars($singular);
			$title     = '';

		}

		return '<var class="' . $class . '"' . $title . '>' . $formatted . '</var>';
	}


	/**
	 * Creates a image link.
	 *
	 *     echo HTML::image('media/img/logo.png', array('alt' => 'My Company'));
	 *
	 * @param   string  $file       file name
	 * @param   array   $attributes default attributes
	 * @param   mixed   $protocol   protocol to pass to URL::base()
	 * @param   boolean $index      include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    HTML::attributes
	 */
	public static function image($file, array $attributes = null, $protocol = null, $index = false) {

		// Add the base URL
		if (strpos($file, '//') === false) {
			$file = URL::site($file, $protocol, $index);
		}

		// Add the image link
		$attributes['src'] = $file;

		return '<img' . HTML::attributes($attributes) . ' />';
	}


	/**
	 * Creates a script link.
	 *
	 *     echo HTML::script('media/js/jquery.min.js');
	 *
	 * @param   string  $file       file name
	 * @param   array   $attributes default attributes
	 * @param   mixed   $protocol   protocol to pass to URL::base()
	 * @param   boolean $index      include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    HTML::attributes
	 */
	public static function script($file, array $attributes = null, $protocol = null, $index = false) {

		// Add the base URL
		if (strpos($file, '//') === false) {
			$file = URL::site($file, $protocol, $index);
		}

		// Set the script link
		$attributes['src'] = $file;

		// Set the script type
		$attributes['type'] = 'text/javascript';

		return '<script' . HTML::attributes($attributes) . '></script>';
	}


	/**
	 * JavaScript source code block
	 *
	 * @param   string|array  $source
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
	 * List separator.
	 *
	 * @static
	 * @param   boolean  $html  Include span
	 * @return  string
	 */
	public static function separator($html = true) {
		return $html ? ' <span class="separator">&middot;</span> ' : ' &middot; ';
	}


	/**
	 * Override style() to allow overriding attributes.
	 *
	 *  echo HTML::style('media/css/screen.css');
	 *
	 * @param   string   $file
	 * @param   array    $attributes
	 * @param   mixed    $protocol    to pass to URL::base()
	 * @param   boolean  $index       include the index page
	 * @return  string
	 * @uses    URL::base
	 * @uses    HTML::attributes
	 */
	public static function style($file, array $attributes = null, $protocol = null, $index = false) {
		if (strpos($file, '//') === false) {
			// Add the base URL
			$file = URL::base($protocol, $index).$file;
		}

		// Set the stylesheet link
		$attributes['href'] = $file;

		// Set the stylesheet rel and type if not set
		$attributes = (array)$attributes + array(
			'rel'  => 'stylesheet',
			'type' => 'text/css'
		);

		return '<link' . HTML::attributes($attributes) . ' />';
	}


	/**
	 * Return formatted <time> tag
	 *
	 * @param   string        $str
	 * @param   array|string  $attributes  handled as time if not an array
	 * @param   boolean       $short       use only date
	 * @return  string
	 */
	public static function time($str, $attributes = null, $short = false) {

		// Extract datetime
		$datetime = (is_array($attributes)) ? Arr::get_once($attributes, 'datetime') : $attributes;
		if ($datetime) {
			$time = is_numeric($datetime) ? $datetime : strtotime($datetime);
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
					$attributes['title'] = $title;
				}
			}

		}

		return '<time' . HTML::attributes($attributes) . '>' . $str . '</time>';
	}


	/**
	 * Returns user link
	 *
	 * @param   mixed   $user
	 * @param   string  $nick
	 * @param   array   $attributes
	 * @param   string  $url         override url
	 * @return  string
	 */
	public static function user($user, $nick = null, array $attributes = null, $url = null) {
		static $viewer = true;

		// Load current user for friend styling
		if ($viewer === true) {
			$viewer = Visitor::instance()->get_user();
		}

		$class = array('user', 'hoverable');
		if (is_array($user) || $user && $user = Model_User::find_user_light($user)) {
			if ($user) {
				$nick = $user['username'];
				if ($viewer && $viewer->is_friend($user)) {
					$class[] = 'friend ';
				}
				switch ($user['gender']) {
					case 'f': $class[] = 'female'; break;
					case 'm': $class[] = 'male'; break;
				}
			}
		}
		$class[] = Arr::get($attributes, 'class');
		$attributes['class'] = trim(implode(' ', $class));

		return empty($nick) ? __('Unknown') : HTML::anchor($url ? $url : URL::user($nick), $nick, $attributes);
	}

}
