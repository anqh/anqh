<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Events controller
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Events extends Controller_Template {

	/**
	 * @var  DateTime
	 */
	public $date;


	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->date = new DateTime;
		$this->tabs = array(
			'upcoming' => array('link' => Route::get('events')->uri(array('action' => 'upcoming')), 'text' => __('Upcoming events')),
			'past'     => array('link' => Route::get('events')->uri(array('action' => 'past')),     'text' => __('Past events')),
			'browse'   => array('link' => Route::get('events')->uri(array('action' => 'browse')),   'text' => __('Browse calendar')),
		);
	}


	/**
	 * Action: browse calendar
	 */
	public function action_browse() {
		$this->page_title = __('Events');
	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		return $this->action_upcoming();
	}


	/**
	 * Action: past events
	 */
	public function action_past() {
		$this->page_title = __('Past events');

		// Set actions
		if (Permission::has(new Model_Event, Model_Event::PERMISSION_CREATE, $this->user)) {
			$this->page_actions[] = array('link' => Route::get('events')->uri(array('action' => 'add')), 'text' => __('Add event'), 'class' => 'event-add');
		}

		// Load events
		$events = Jelly::select('event')->past()->limit(25)->execute_grouped();

		if (count($events)) {
			$this->page_subtitle = __2(':events event', ':events events', count($events), array(':events' => '<var>' . count($events) . '</var>'));

			Widget::add('main', View_Module::factory('events/events', array(
				'events' => $events,
			)));
		}
	}


	/**
	 * Action: upcoming events
	 */
	public function action_upcoming() {
		$this->page_title = __('Upcoming events');

		// Set actions
		if (Permission::has(new Model_Event, Model_Event::PERMISSION_CREATE, $this->user)) {
			$this->page_actions[] = array('link' => Route::get('events')->uri(array('action' => 'add')), 'text' => __('Add event'), 'class' => 'event-add');
		}

		// Load events
		$events = Jelly::select('event')->upcoming()->limit(25)->execute_grouped();

		if (count($events)) {
			$this->page_subtitle = __2(':events event', ':events events', count($events), array(':events' => '<var>' . count($events) . '</var>'));

			Widget::add('main', View_Module::factory('events/events', array(
				'events' => $events,
			)));
		}
	}

}
