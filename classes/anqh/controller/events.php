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
	 * Action: add
	 */
	public function action_add() {
		$this->page_title = __('Event');

		return $this->_edit_event();
	}


	/**
	 * Action: browse calendar
	 */
	public function action_browse() {
		$this->page_title = __('Events');
		$this->tab_id = 'browse';

		$year  = (int)$this->request->param('year');
		$month = (int)$this->request->param('month');
		$day   = (int)$this->request->param('day');
		$week  = (int)$this->request->param('week');

		$day   = $day ? $day : date('j');
		$month = $month ? $month : date('n');
		$year  = $year ? $year : date('Y');
		$first = mktime(0, 0, 0, $month, $day, $year);
		$last  = strtotime('+1 week', $first);

		// Load events
		$events = Jelly::select('event')->between($first, $last)->execute_grouped();
		if (count($events)) {
			$this->page_subtitle = __2(':events event', ':events events', count($events), array(':events' => '<var>' . count($events) . '</var>'));

			Widget::add('main', View_Module::factory('generic/filters', array(
				'filters' => $this->_filters($events),
			)));
			Widget::add('main', View_Module::factory('events/events', array(
				'events' => $events,
			)));
		}

		// Calendar
		Widget::add('side', View_Module::factory('events/calendar', array(
			'date'      => $first,
			'url_day'   => '/events/:year/:month/:day',
			'url_month' => '/events/:year/:month',
		)));

	}


	/**
	 * Action: delete event
	 */
	public function action_delete() {
		$this->history = false;

		// Load venue
		$event_id = (int)$this->request->param('id');
		$event = Jelly::select('event')->load($event_id);
		if (!$event->loaded()) {
			throw new Model_Exception($event, $event_id);
		}

		Permission::required($event, Model_Event::PERMISSION_DELETE, $this->user);

		if (!Security::csrf_valid()) {
			$this->request->redirect(Route::model($event));
		}

		$date = $event->stamp_begin;
		$event->delete();

		$this->request->redirect(Route::get('events_ymd')->uri(array('year' => date('Y', $date), 'month' => date('m', $date), 'day' => date('d', $date))));
	}


	/**
	 * Action: edit event
	 */
	public function action_edit() {
		return $this->_edit_event((int)$this->request->param('id'));
	}


	/**
	 * Action: event
	 */
	public function action_event() {
		$event_id = (int)$this->request->param('id');

		// Load event
		$event = Jelly::select('event')->load($event_id);
		if (!$event->loaded()) {
			throw new Model_Exception($event, $event_id);
		}
		Permission::required($event, Model_Event::PERMISSION_READ, $this->user);

		// Set actions
		if (Permission::has($event, Model_Event::PERMISSION_FAVORITE, $this->user)) {
			if ($event->is_favorite($this->user)) {
				$this->page_actions[] = array('link' => Route::model($event, 'unfavorite') . '?token=' . Security::csrf(), 'text' => __('Remove favorite'), 'class' => 'favorite-delete');
			} else {
				$this->page_actions[] = array('link' => Route::model($event, 'favorite') . '?token=' . Security::csrf(), 'text' => __('Add favorite'), 'class' => 'favorite-add');
			}
		}
		if (Permission::has($event, Model_Event::PERMISSION_UPDATE, $this->user)) {
			$this->page_actions[] = array('link' => Route::model($event, 'edit'), 'text' => __('Edit event'), 'class' => 'event-edit');
		}

		$this->page_title = HTML::chars($event->name);
		$this->page_subtitle = HTML::time(Date('l ', $event->stamp_begin) . Date::format('DDMMYYYY', $event->stamp_begin), $event->stamp_begin, true);

		Widget::add('main', View_Module::factory('events/event', array('event' => $event)));
		Widget::add('side', View_Module::factory('events/event_info', array('user' => $this->user, 'event' => $event)));
	}


	/**
	 * Action: add to favorites
	 */
	public function action_favorite() {
		$this->history = false;

		// Load event
		$event_id = (int)$this->request->param('id');
		$event = Jelly::select('event')->load($event_id);
		if (!$event->loaded()) {
			throw new Model_Exception($event, $event_id);
		}
		Permission::required($event, Model_Event::PERMISSION_FAVORITE, $this->user);

		if (Security::csrf_valid()) {
			$event->add_favorite($this->user);
		}

		$this->request->redirect(Route::model($event));
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
		$this->tab_id = 'past';

		// Set actions
		if (Permission::has(new Model_Event, Model_Event::PERMISSION_CREATE, $this->user)) {
			$this->page_actions[] = array('link' => Route::get('events')->uri(array('action' => 'add')), 'text' => __('Add event'), 'class' => 'event-add');
		}

		// Load events
		$events = Jelly::select('event')->past()->limit(25)->execute_grouped();
		if (count($events)) {
			$this->page_subtitle = __2(':events event', ':events events', count($events), array(':events' => '<var>' . count($events) . '</var>'));

			Widget::add('main', View_Module::factory('generic/filters', array(
				'filters' => $this->_filters($events),
			)));
			Widget::add('main', View_Module::factory('events/events', array(
				'events' => $events,
			)));
		}

		// Calendar
		Widget::add('side', View_Module::factory('events/calendar', array(
			'url_day'   => '/events/:year/:month/:day',
			'url_month' => '/events/:year/:month',
		)));

	}


	/**
	 * Action: add to favorites
	 */
	public function action_unfavorite() {
		$this->history = false;

		// Load event
		$event_id = (int)$this->request->param('id');
		$event = Jelly::select('event')->load($event_id);
		if (!$event->loaded()) {
			throw new Model_Exception($event, $event_id);
		}
		Permission::required($event, Model_Event::PERMISSION_FAVORITE, $this->user);

		if (Security::csrf_valid()) {
			$event->delete_favorite($this->user);
		}

		$this->request->redirect(Route::model($event));
	}


	/**
	 * Action: upcoming events
	 */
	public function action_upcoming() {
		$this->page_title = __('Upcoming events');
		$this->tab_id = 'upcoming';

		// Set actions
		if (Permission::has(new Model_Event, Model_Event::PERMISSION_CREATE, $this->user)) {
			$this->page_actions[] = array('link' => Route::get('events')->uri(array('action' => 'add')), 'text' => __('Add event'), 'class' => 'event-add');
		}

		// Load events
		$events = Jelly::select('event')->upcoming()->limit(25)->execute_grouped();
		if (count($events)) {
			$this->page_subtitle = __2(':events event', ':events events', count($events), array(':events' => '<var>' . count($events) . '</var>'));

			Widget::add('main', View_Module::factory('generic/filters', array(
				'filters' => $this->_filters($events),
			)));
			Widget::add('main', View_Module::factory('events/events', array(
				'events' => $events,
			)));
		}

		// Calendar
		Widget::add('side', View_Module::factory('events/calendar', array(
			'url_day'   => '/events/:year/:month/:day',
			'url_month' => '/events/:year/:month',
		)));

	}


	/**
	 * Edit event
	 *
	 * @param  integer  $event_id
	 */
	protected function _edit_event($event_id = null) {
		$this->history = false;

		if ($event_id) {

			// Editing old
			$event = Jelly::select('event')->load($event_id);
			if (!$event->loaded()) {
				throw new Model_Exception($event, $event_id);
			}
			Permission::required($event, Model_Event::PERMISSION_UPDATE, $this->user);
			$cancel = Request::back(Route::model($event), true);

			// Set actions
			if (Permission::has($event, Model_Event::PERMISSION_DELETE, $this->user)) {
				$this->page_actions[] = array('link' => Route::model($event, 'delete') . '?token=' . Security::csrf(), 'text' => __('Delete event'), 'class' => 'event-delete');
			}

			// Dummy fields
			if (!$_POST) {
				$date_begin = $event->meta()->fields('date_begin');
				$event->date_begin = $date_begin->pretty_format ? date($date_begin->pretty_format, $event->stamp_begin) : $event->stamp_begin;
				$event->time_begin = Date::format('HHMM', $event->stamp_begin);
				$event->time_end   = Date::format('HHMM', $event->stamp_end);
			}

		} else {

			// Creating new
			$event = Jelly::factory('event');
			Permission::required($event, Model_Event::PERMISSION_CREATE, $this->user);
			$cancel = Request::back(Route::get('events')->uri(), true);

			$event->author = $this->user;

		}

		// Handle post
		$errors = array();
		if ($_POST) {
			$post = Arr::extract($_POST, Model_Event::$editable_fields);
			$post['stamp_begin'] = !empty($post['date_begin']) && !empty($post['time_begin']) ? $post['date_begin'] . ' ' . $post['time_begin'] : null;
			$post['stamp_end']   = !empty($post['date_begin']) && !empty($post['time_end']) ? $post['date_begin'] . ' ' . $post['time_end'] : null;
			$event->set($post);
			try {
				$event->validate();

				// Make sure end time is after start time, i.e. the next day
				if ($event->stamp_end < $event->stamp_begin) {
					$event->stamp_end += 60 * 60 * 24;
				}
				$event->save();

				$this->request->redirect(Route::model($event));
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validation');
				$errors['date_begin'] = Arr::get($errors, 'date_begin', Arr::get($errors, 'stamp_begin'));
				$errors['time_begin'] = Arr::get($errors, 'time_begin', Arr::get($errors, 'stamp_begin'));
				$errors['time_end']   = Arr::get($errors, 'time_end', Arr::get($errors, 'stamp_end'));
			}
		}

		// Build form
		$form = array(
			'values' => $event,
			'errors' => $errors,
			'cancel' => $cancel,
			'hidden' => array(
				'city'  => $event->city ? $event->city->id : 0,
				'venue' => $event->venue ? $event->venue->id : 0,
			),
			'groups' => array(
				'event' => array(
					'header' => __('Event'),
					'fields' => array(
						'name'     => array(),
						'homepage' => array(),
					),
				),
				'when' => array(
					'header' => __('When?'),
					'fields' => array(
						'date_begin' => array(),
						'time_begin' => array(),
						'time_end'   => array(),
					)
				),
				'where' => array(
					'header' => __('Where?'),
					'fields' => array(
						'venue_name' => array(),
						'city_name'  => array(),
						'age'        => array(),
					)
				),
				'tickets' => array(
					'header' => __('Tickets'),
					'fields' => array(
						'price'  => array('attributes' => array('title' => __('Set to zero for free entry'))),
						'price2' => array(),
					),
				),
				'who' => array(
					'header' => __('Who?'),
					'fields' => array(
						'dj' => array(),
					)
				),
				'what' => array(
					'header' => __('What?'),
					'fields' => array(
						'info' => array(),
					)
				)
			)
		);

		// Tags
		$tag_group = Jelly::select('tag_group')->where('name', '=', 'Music')->limit(1)->execute();
		if ($tag_group->loaded() && count($tag_group->tags)) {
			$tags = array();
			foreach ($tag_group->tags as $tag) {
				$tags[$tag->id()] = $tag->name();
			}
			$form['groups']['what']['fields']['tags'] = array(
				'class'  => 'pills',
				'values' => $tags,
			);
		}

		// Autocomplete city
		$this->autocomplete_city('city_name', 'city_id');

		// Autocomplete venue
		$venues = Jelly::select('venue')->with('city')->event_hosts()->execute();
		$hosts = array();
		if (count($venues)) {
			foreach ($venues as $venue) {
				$hosts[] = array(
					'value'   => $venue->id,
					'label'   => HTML::chars($venue->name),
					'city'    => HTML::chars($venue->city->name),
					'city_id' => $venue->city->id,
				);
			}
		}
		Widget::add('foot', HTML::script_source('
var venues = ' . json_encode($hosts) . ';
$("#field-venue_name").autocomplete({
	minLength: 0,
	source: venues,
	focus: function(event, ui) {
		$("input[name=venue_name]").val(ui.item.label);
		return false;
	},
	select: function(event, ui) {
		$("input[name=venue_name]").val(ui.item.label);
		$("input[name=venue]").val(ui.item.value);
		$("input[name=city_name]").val(ui.item.city);
		$("input[name=city]").val(ui.item.city_id);
		return false;
	}
})
.data("autocomplete")._renderItem = function(ul, item) {
	return $("<li></li>")
		.data("item.autocomplete", item)
		.append("<a>" + item.label + ", " + item.city + "</a>")
		.appendTo(ul);
};
'));

		Widget::add('foot', HTML::script_source('$("#field-date_begin").datepicker({ dateFormat: "d.m.yy", firstDay: 1, changeFirstDay: false, showOtherMonths: true, showWeeks: true, showStatus: true, showOn: "both" });'));
		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}

	/**
	 * Build filter items
	 *
	 * @param   array  $events
	 * @return  array
	 */
	protected function _filters(array $events = null) {
		$filters = array();
		if (count($events))	{
			$cities = array();
			$empty = false;
			$elsewhere = URL::title(__('Elsewhere'));

			// Build filter list
			foreach ($events as $day) {
				foreach ($day as $city => $city_events) {

					// City filter
					$filter = URL::title($city);
					if ($filter == $elsewhere) {
						$empty = true;
						continue;
					}
					if (!isset($cities[$filter])) {
						$cities[$filter] = $city;
					}

				}
			}

			// Drop empty to last
			ksort($cities);
			if ($empty) {
				$cities[$elsewhere] = UTF8::ucfirst(mb_strtolower(__('Elsewhere')));
			}

			// Build city filter
			$filters['city'] = array(
				'name'    => __('City'),
				'filters' => $cities,
			);

		}

		return $filters;
	}

}
