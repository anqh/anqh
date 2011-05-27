<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Events view.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
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
	 * Executed before rendering.
	 */
	public function before() {

		// Generate section title of none set
		if (is_null($this->title) && $this->date) {
			if ($this->date == date('Y-m-d')) {
				$this->title  = __('Today');
				$this->class .= ' today';
			} else {
				$date = Date::split($this->date);
				$this->title = $date['weekday_short'] . ' '. $date['day'] . ' ' . $date['month_short'];
			}
		}

		// Add articles
		foreach ((array)$this->events as $city => $events) {
			foreach ($events as $event) {
				$article        = new View_Event_Day($event);
				$article->class = 'event city-' . URL::title($city);

				$this->articles[] = $article;
			}
		}

	}

}
