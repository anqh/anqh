<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Event side info view.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Event_vEvent extends View_vEvent {

	/**
	 * Create new vEvent.
	 *
	 * @param  Model_Event  $event
	 */
	public function __construct(Model_Event $event) {
		parent::__construct();

		$this->uid         = 'event-' . $event->id . '@' . $_SERVER['HTTP_HOST'];
		$this->summary     = $event->name;
		$this->dtstamp     = View_iCalendar::stamp($event->created);
		$this->dtstart     = View_iCalendar::stamp($event->stamp_begin);
		$this->dtend       = View_iCalendar::stamp($event->stamp_end);
		$this->url         = URL::site(Route::model($event), true);
		$this->description = $this->url;

		if ($venue = $event->venue()) {
			$this->location = $venue->name . ', ' . $venue->address . ', ' . $venue->city_name;
		} else if ($event->venue_name) {
			$this->location = $event->venue_name . ', ' . $event->city_name;
		} else {
			$this->location = ($event->venue_hidden ? __('Underground') . ', ' : '') . $event->city_name;
		}

		if ($event->modified) {
			$this->last_modified = View_iCalendar::stamp($event->modified);
		}
	}

}
