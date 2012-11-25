<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * vEvent for iCalendar page.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_vEvent extends View_Base {
	public $description;
	public $dtend;
	public $dtstamp;
	public $dtstart;
	public $last_modified;
	public $location;
	public $summary;
	public $uid;
	public $url;


	/**
	 * Render section.
	 *
	 * @return  string
	 */
	public function render() {
		ob_start();

		echo "BEGIN:VEVENT\r\n";
		echo "UID:" . $this->uid . "\r\n";
		echo "DTSTAMP:" . $this->dtstamp . "\r\n";
		echo "DTSTART:" . $this->dtstart . "\r\n";
		echo "DTEND:" . $this->dtend . "\r\n";
		if ($this->last_modified) {
			echo "LAST-MODIFIED:" . $this->last_modified . "\r\n";
		}
		echo "SUMMARY:" . $this->summary . "\r\n";
		if ($this->description) {
			echo "DESCRIPTION:" . $this->description . "\r\n";
		}
		if ($this->url) {
			echo "URL:" . $this->url . "\r\n";
		}
		echo "LOCATION:" . $this->location . "\r\n";
		echo "END:VEVENT\r\n";

		return str_replace(',', "\,", ob_get_clean());
	}

}
