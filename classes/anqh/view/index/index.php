<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Home view
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_Index_Index extends View_Layout {

	/**
	 * Var method for page title.
	 *
	 * @return  string
	 */
	public function title() {
		return __('Welcome to :site', array(':site' => Kohana::config('site.site_name')));
	}


	/**
	 * Var method for birthdays.
	 *
	 * @return  View_Users_Birthdays
	 */
	public function view_birthdays() {
		return new View_Users_Birthdays();
	}


	/**
	 * Var method for view_events.
	 *
	 * @return  View_Events_Day
	 */
	public function view_events() {
		$events = Model_Event::factory()->find_grouped_between($today = strtotime('today'), strtotime('+1 day', $today));
		if (count($events)) {
			$view_events        = new View_Events_Day();
			$view_events->title = __("Today's events");

			// Grouped function returns events grouped by date and city, need to combine
			$_events = array();
			foreach (reset($events) as $city) {
				$_events[] = array_values($city);
			}
			$view_events->events = reset($_events);

			return $view_events;
		}

		return null;
	}


	/**
	 * Var method for view_now.
	 *
	 * @return  View_Events_Day
	 */
	public function view_now() {
		$events = Model_Event::factory()->find_now();
		if (count($events)) {
			$view_events         = new View_Events_Day();
			$view_events->title  = __('Happening now');
			$view_events->events = $events;

			return $view_events;
		}

		return null;
	}


	/**
	 * Var method for view_online.
	 *
	 * @return  string
	 */
	public function view_online() {
		return New View_Users_Online();
	}


	/**
	 * Var method for newsfeed.
	 *
	 * @return  View_Newsfeed
	 */
	public function view_newsfeed() {
		$view_newsfeed = new View_Newsfeed();
		$view_newsfeed->role  = 'main';
		$view_newsfeed->type  = Arr::get($_GET, 'newsfeed', View_Newsfeed::TYPE_ALL);
		$view_newsfeed->limit = 15;

		return $view_newsfeed;
	}


	/**
	 * Var method for shouts.
	 *
	 * @return  View_Index_Shouts
	 */
	public function view_shouts() {
		return new View_Shouts_Shouts();
	}


	/**
	 * Var method for main slot.
	 *
	 * @return  array
	 */
	public function views_main() {
		return array(
			'top' => $this->view_newsfeed()
		);
	}


	/**
	 * Var method for side slot.
	 *
	 * @return  array
	 */
	public function views_side() {
		return array(
			'top'    => Widget::get('side'),
		);
	}

}
