<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * iCalendar page.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_iCalendar extends View_Base {

	/**
	 * @var  string  iCalendar name
	 */
	public $calname;

	/**
	 * @var  array  iCalendar events
	 */
	public $events = array();

	/**
	 * @var  string  Timezone id
	 */
	public $tzid = 'Europe/Helsinki';

	/**
	 * @var  string  Timezone name
	 */
	public $tzname = 'EET';
	public $tznamedst = 'EEST';

	/**
	 * @var  string  DST offset
	 */
	public $tzoffsetfrom = '+0200';
	public $tzoffsetto   = '+0300';

	/**
	 * @var  string  iCalendar version
	 */
	public $version = '2.0';


	/**
	 * Escape iCalendar text.
	 *
	 * @param   string  $string
	 * @return  string
	 */
	public static function escape($string) {
		return str_replace(array(',', ';', "\\"), array("\,", "\;", "\\\\"), $string);
	}


	/**
	 * Convert unix timestamp to iCalendar format.
	 *
	 * @param   integer  $stamp
	 * @return  string
	 */
	public static function stamp($stamp) {
		return date("Ymd\THis", $stamp);
	}


	/**
	 * Get section articles.
	 *
	 * @return  array
	 */
	public function events() {
		return $this->events;
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		return implode('', $this->events());
	}


	/**
	 * Render section.
	 *
	 * @return  string
	 */
	public function render() {
		ob_start();

		echo "BEGIN:VCALENDAR\r\n";
		echo "VERSION:" . $this->version . "\r\n";
		echo "PRODID:-//" . Kohana::$config->load('site.site_name') . "//NONSGML Anqh v" . Anqh::VERSION . "//EN\r\n";

		if ($this->calname) {
			echo "X-WR-CALNAME:" . $this->calname . "\r\n";
		}

		// Timezone
		if ($this->tzid) {
			echo "BEGIN:VTIMEZONE\r\n";
			echo "TZID:" . $this->tzid . "\r\n";
			echo "X-LIC-LOCATION:" . $this->tzid . "\r\n";

			// Daylight saving time
			if ($this->tzoffsetfrom && $this->tzoffsetto) {
				echo "BEGIN:DAYLIGHT\r\n";
				echo "TZOFFSETFROM:" . $this->tzoffsetfrom . "\r\n";
				echo "TZOFFSETTO:" . $this->tzoffsetto . "\r\n";
				echo "TZNAME:" . $this->tznamedst . "\r\n";
				echo "DTSTART:19700329T030000\r\n";
				echo "RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU\r\n";
				echo "END:DAYLIGHT\r\n";

				echo "BEGIN:STANDARD\r\n";
				echo "TZOFFSETFROM:" . $this->tzoffsetto . "\r\n";
				echo "TZOFFSETTO:" . $this->tzoffsetfrom . "\r\n";
				echo "TZNAME:" . $this->tzname . "\r\n";
				echo "DTSTART:19701025T040000\r\n";
				echo "RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU\r\n";
				echo "END:STANDARD\r\n";
			}

			echo "END:VTIMEZONE\r\n";
		}

		echo $this->content();

		echo "END:VCALENDAR\r\n";

		return ob_get_clean();
	}

}
