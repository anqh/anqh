<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Events view.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Events_Day extends View_Section {

	/**
	 * @var  string  Section class
	 */
	public $class = 'events';

	/**
	 * @var  string  Day to show, Y-m-d
	 */
	public $date = null;

	/**
	 * @var  Model_Event[]
	 */
	public $events = null;

	/**
	 * @var  boolean  Sticky dates
	 */
	public $title_sticky = true;


	/**
	 * Executed before rendering.
	 */
	public function before() {

		// Generate section title if none set
		if (is_null($this->title) && $this->date) {
			if ($this->date == date('Y-m-d')) {
				$this->title  = __('Today');
				$this->class .= ' today';
			} else if ($this->date == date('Y-m-d', strtotime('tomorrow'))) {
				$this->title  = __('Tomorrow');
				$this->class .= ' tomorrow';
			} else {
				$date = Date::split($this->date);
				$this->title = $date['weekday_short'] . ' '. $date['day'] . ' ' . $date['month_short'];
			}
		}

		// Add articles
		foreach ((array)$this->events as $city => $events) {
			foreach ($events as $event) {
				$article         = new View_Event_Day($event);
				$article->class .= ' city-' . URL::title($city);

				$this->articles[] = $article;
			}
		}

	}

}
