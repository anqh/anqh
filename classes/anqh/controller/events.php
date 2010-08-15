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
			'upcoming' => array('url' => Route::get('events')->uri(array('action' => 'upcoming')), 'text' => __('Upcoming events')),
			'past'     => array('url' => Route::get('events')->uri(array('action' => 'past')),     'text' => __('Past events')),
			'browse'   => array('url' => Route::get('events')->uri(array('action' => 'browse')),   'text' => __('Browse calendar')),
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

		// Set actions
		if (Permission::has(new Model_Event, Model_Event::PERMISSION_CREATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::get('events')->uri(array('action' => 'add')), 'text' => __('Add event'), 'class' => 'event-add');
		}

		// Load events
		$events = Jelly::select('event')->between($first, $last, 'ASC')->execute_grouped();
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

		// Tabs
		$this->_tabs();

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

		Permission::required($event, Model_Event::PERMISSION_DELETE, self::$user);

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
		Permission::required($event, Model_Event::PERMISSION_READ, self::$user);

		// Set actions
		if (Permission::has($event, Model_Event::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($event, 'edit'), 'text' => __('Edit event'), 'class' => 'event-edit');
		}
		if (Permission::has($event, Model_Event::PERMISSION_FAVORITE, self::$user)) {
			if ($event->is_favorite(self::$user)) {
				$this->page_actions[] = array('link' => Route::model($event, 'unfavorite') . '?token=' . Security::csrf(), 'text' => __('Remove favorite'), 'class' => 'favorite-delete');
			} else {
				$this->page_actions[] = array('link' => Route::model($event, 'favorite') . '?token=' . Security::csrf(), 'text' => __('Add favorite'), 'class' => 'favorite-add');
			}
		}
		$this->page_actions[] = array('link' => Route::get('forum_event')->uri(array('id' => $event->id)), 'text' => __('Forum'));
		$this->page_actions[] = array('link' => Route::get('gallery_event')->uri(array('id' => $event->id)), 'text' => __('Gallery'));

		$this->page_title = HTML::chars($event->name);
		$this->page_subtitle = HTML::time(Date('l ', $event->stamp_begin) . Date::format('DDMMYYYY', $event->stamp_begin), $event->stamp_begin, true);

		// Event performers and extra info
		Widget::add('main', View_Module::factory('events/event', array('event' => $event)));

		// Slideshow
		if (count($event->images) > 1) {
			$images = array();
			foreach ($event->images as $image) $images[] = $image;
			Widget::add('side', View_Module::factory('generic/image_slideshow', array(
				'images'     => array_reverse($images),
			)));
		}

		// Event flyers
		if ($event->flyer_front->id || $event->flyer_back->id || !($event->flyer_front_url || $event->flyer_back_url)) {
			Widget::add('side', $this->_get_mod_image($event));
		} else if ($event->flyer_front_url || $event->flyer_back_url) {

			// To be deprecated
			Widget::add('side', View_Module::factory('events/flyers', array(
				'event' => $event,
			)));

		}

		// Event quick info
		Widget::add('side', View_Module::factory('events/event_info', array(
			'mod_title' => __('Event information'),
			'event'     => $event
		)));

		// Favorites
		if ($event->favorite_count) {
			Widget::add('side', View_Module::factory('generic/users', array(
				'mod_title' => _('Favorites'),
				'viewer'    => self::$user,
				'users'     => $event->find_favorites()
			)));
		}

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
		Permission::required($event, Model_Event::PERMISSION_FAVORITE, self::$user);

		if (Security::csrf_valid()) {
			$event->add_favorite(self::$user);

			// News feed
			NewsfeedItem_Events::favorite(self::$user, $event);

		}

		$this->request->redirect(Route::model($event));
	}


	/**
	 * Action: hover card
	 */
	public function action_hover() {
		$this->history = false;

		// Hover card works only with ajax
		if (!$this->ajax) {
			return $this->action_event();
		}

		$event = Jelly::select('event')->load((int)$this->request->param('id'));
		if ($event->loaded())	{
			echo View_Module::factory('events/hovercard', array(
				'mod_title' => HTML::chars($event->name),
				'event'      => $event
			));
		}
	}


	/**
	 * Action: image
	 */
	public function action_image() {
		$this->history = false;

		// Load event
		$event_id = (int)$this->request->param('id');
		$event = Jelly::select('event')->load($event_id);
		if (!$event->loaded()) {
			throw new Model_Exception($event, $event_id);
		}
		Permission::required($event, Model_Event::PERMISSION_UPDATE, self::$user);

		if (!$this->ajax) {
			$this->page_title = HTML::chars($event->name);
			$this->page_subtitle = HTML::time(Date('l ', $event->stamp_begin) . Date::format('DDMMYYYY', $event->stamp_begin), $event->stamp_begin, true);
		}

		// Delete existing
		if (isset($_REQUEST['delete'])) {
			$image = Jelly::select('image')->load((int)$_REQUEST['delete']);
			if (Security::csrf_valid() && $image->loaded() && $event->has('images', $image)) {
				$event->remove('images', $image);
				if ($image->id == $event->flyer_front->id) {
					$event->flyer_front = null;
					$event->flyer_front_url = null;
				} else if ($image->id == $event->flyer_back->id) {
					$event->flyer_back = null;
					$event->flyer_back_back = null;
				}
				$event->save();
				$image->delete();
			}
			$cancel = true;
		}

		// Cancel change
		if (isset($cancel) || isset($_REQUEST['cancel'])) {
			if ($this->ajax) {
				echo $this->_get_mod_image($event);
				return;
			}

			$this->request->redirect(Route::model($event));
		}

		// Front or back flyer
		$flyer = isset($_REQUEST['back']) ? 'flyer_back' : 'flyer_front';

		$image = Jelly::factory('image')->set(array(
			'author' => self::$user,
		));

		// Handle post
		$errors = array();
		if ($_POST && $_FILES && Security::csrf_valid()) {
			$image->file = Arr::get($_FILES, 'file');
			try {
				$image->save();

				// Add exif, silently continue if failed - not critical
				try {
					Jelly::factory('image_exif')
						->set(array('image' => $image))
						->save();
				} catch (Kohana_Exception $e) { }

				// Set the image as flyer
				$event->add('images', $image);
				$event->$flyer = $image;
				$event->{$flyer . '_url'} = $image->get_url();
				$event->save();

				NewsfeedItem_Events::event_edit(self::$user, $event);

				if ($this->ajax) {
					echo $this->_get_mod_image($event);
					return;
				}

				$this->request->redirect(Route::model($event));

			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validation');
			} catch (Kohana_Exception $e) {
				echo Kohana::debug($e);
				$errors = array('file' => __('Failed with image'));
			}
		}

		// Build form
		$form = array(
			'ajaxify'    => $this->ajax,
			'values'     => $image,
			'errors'     => $errors,
			'attributes' => array('enctype' => 'multipart/form-data'),
			'cancel'     => $this->ajax ? Route::model($event, 'image') . '?cancel' : Route::model($event),
			'hidden'     => array(isset($_REQUEST['back']) ? 'back' : 'front' => 1),
			'groups'     => array(
				array(
					'fields' => array(
						'file' => array(),
					),
				),
			)
		);

		$view = View_Module::factory('form/anqh', array(
			'mod_title' => __('Add image'),
			'form'      => $form
		));

		if ($this->ajax) {
			echo $view;
			return;
		}

		Widget::add('main', $view);
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
		if (Permission::has(new Model_Event, Model_Event::PERMISSION_CREATE, self::$user)) {
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

		// Tabs
		$this->_tabs();

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
		Permission::required($event, Model_Event::PERMISSION_FAVORITE, self::$user);

		if (Security::csrf_valid()) {
			$event->delete_favorite(self::$user);
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
		if (Permission::has(new Model_Event, Model_Event::PERMISSION_CREATE, self::$user)) {
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

		// Tabs
		$this->_tabs();

	}


	/**
	 * Edit event
	 *
	 * @param  integer  $event_id
	 */
	protected function _edit_event($event_id = null) {
		$this->history = false;

		Widget::add('head', HTML::script('js/jquery.markitup.pack.js'));
		Widget::add('head', HTML::script('js/markitup.bbcode.js'));

		if ($event_id) {

			// Editing old
			$event = Jelly::select('event')->load($event_id);
			if (!$event->loaded()) {
				throw new Model_Exception($event, $event_id);
			}
			Permission::required($event, Model_Event::PERMISSION_UPDATE, self::$user);
			$cancel = Request::back(Route::model($event), true);

			$this->page_title = HTML::chars($event->name);

			// Set actions
			if (Permission::has($event, Model_Event::PERMISSION_DELETE, self::$user)) {
				$this->page_actions[] = array('link' => Route::model($event, 'delete') . '?token=' . Security::csrf(), 'text' => __('Delete event'), 'class' => 'event-delete');
			}
			$edit = true;


		} else {

			// Creating new
			$event = Jelly::factory('event');
			Permission::required($event, Model_Event::PERMISSION_CREATE, self::$user);
			$cancel = Request::back(Route::get('events')->uri(), true);

			$this->page_title = __('New event');

			$event->author = self::$user;
			$edit = false;

		}

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			$post = Arr::extract($_POST, Model_Event::$editable_fields);
			if (isset($post['stamp_begin']['date']) && isset($post['stamp_end']['time'])) {
				$post['stamp_end']['date'] = $post['stamp_begin']['date'];
			}
			$event->set($post);

			// GeoNames
			if ($_POST['city_id'] && $city = Geo::find_city((int)$_POST['city_id'])) {
				$event->city = $city;
			}

			try {
				$validation = 'event';
				$event->validate();

				// Add venue?
				if (empty($_POST['venue']) && $_POST['venue_name']) {
					$venue = Jelly::factory('venue');
					$venue->name = $_POST['venue_name'];
					$venue->address = $_POST['address'];
					$venue->city_name = $_POST['city_name'];
					isset($city) and $venue->city = $city;
					$venue->event_host = true;
					$validation = 'venue';
					$venue->save();
					$event->venue = $venue;
				}

				// Make sure end time is after start time, i.e. the next day
				if ($event->stamp_end < $event->stamp_begin) {
					$event->stamp_end += 60 * 60 * 24;
				}
				$validation = 'event';
				$event->save();

				// News feed
				$edit ? NewsfeedItem_Events::event_edit(self::$user, $event) : NewsfeedItem_Events::event(self::$user, $event);

				$this->request->redirect(Route::model($event));
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validation');
				if ($validation == 'venue' && isset($errors['name'])) {
					$errors['venue_name'] = Arr::get_once($errors, 'name');
				}
			}
		}

		// Build form
		$form = array(
			'values' => $event,
			'errors' => $errors,
			'cancel' => $cancel,
			'hidden' => array(
				'city_id'  => $event->city ? $event->city->id : 0,
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
					'header'     => __('When?'),
					'attributes' => array(
						'class' => 'horizontal',
					),
					'fields'     => array(
						'stamp_begin' => array(
							'default_time' => '22:00',
						),
						'stamp_end'   => array(
							'default_time' => '04:00',
						),
					)
				),
				'tickets' => array(
					'header' => __('Tickets'),
					'fields' => array(
						'price'  => array('attributes' => array('title' => __('Set to zero for free entry'))),
						'price2' => array(),
					),
				),
				'where' => array(
					'header' => __('Where?'),
					'fields' => array(
						'venue_name' => array(),
						'address'    => array(
							'model' => $event->venue,
						),
						'city_name'  => array(),
						'age'        => array(),
					)
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
var venue = "";
$("#field-venue-name").autocomplete({
	minLength: 1,
	source: venues,
	focus: function(event, ui) {
		$("input[name=venue_name]").val(ui.item.label);
		venue = ui.item.label;
		return false;
	},
	select: function(event, ui) {
		$("input[name=venue_name]").val(ui.item.label);
		$("input[name=venue]").val(ui.item.value);
		$("input[name=city_name]").val(ui.item.city);
		$("input[name=city]").val(ui.item.city_id);
		return false;
	},
	close: function(event, ui) {
		if ($("input[name=venue_name]").val() != venue) {
			$("input[name=venue]").val("");
		}
	}
})
.data("autocomplete")._renderItem = function(ul, item) {
	return $("<li></li>")
		.data("item.autocomplete", item)
		.append("<a>" + item.label + ", " + item.city + "</a>")
		.appendTo(ul);
};
'));

		$options = array(
			'changeMonth'     => true,
			'changeYear'      => true,
			'dateFormat'      => 'd.m.yy',
			'dayNames'        => array(
				__('Sunday'), __('Monday'), __('Tuesday'), __('Wednesday'), __('Thursday'), __('Friday'), __('Saturday')
			),
			'dayNamesMin'    => array(
				__('Su'), __('Mo'), __('Tu'), __('We'), __('Th'), __('Fr'), __('Sa')
			),
			'firstDay'        => 1,
			'monthNames'      => array(
				__('January'), __('February'), __('March'), __('April'),
				__('May'), __('June'), __('July'), __('August'),
				__('September'), __('October'), __('November'), __('December')
			),
			'monthNamesShort' => array(
				__('Jan'), __('Feb'), __('Mar'), __('Apr'),
				__('May'), __('Jun'), __('Jul'), __('Aug'),
				__('Sep'), __('Oct'), __('Nov'), __('Dec')
			),
			'nextText'        => __('&raquo;'),
			'prevText'        => __('&laquo;'),
			'showWeek'        => true,
			'showOtherMonths' => true,
			'weekHeader'      => __('Wk'),
		);

		Widget::add('foot', HTML::script_source('$("#field-stamp-begin-date").datepicker(' . json_encode($options) . ');'));
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


	/**
	 * Get image mod
	 *
	 * @param   Model_Event  $event
	 * @return  View_Module
	 */
	protected function _get_mod_image(Model_Event $event) {

		// Display front flyer by default
		if ($event->flyer_front->id) {
			$image = $event->flyer_front;
		} else if ($event->flyer_back->id) {
			$image = $event->flyer_back;
		} else {
			$image = null;
		}

		if (Permission::has($event, Model_User::PERMISSION_UPDATE, self::$user)) {
			$actions = array();
			!$event->flyer_front->id and $actions[] = array('link' => Route::model($event, 'image') . '?front', 'text' => __('Add front flyer'), 'class' => 'image-add ajaxify');
			!$event->flyer_back->id and $actions[] = array('link' => Route::model($event, 'image') . '?back', 'text' => __('Add back flyer'), 'class' => 'image-add ajaxify');
			$image and $actions[] = array('link' => Route::model($event, 'image') . '?token=' . Security::csrf() . '&delete=' . $image->id, 'text' => __('Delete'), 'class' => 'image-delete');
		} else {
			$actions = null;
		}
		return View_Module::factory('generic/side_image', array(
			'mod_actions2' => $actions,
			'image'        => $image,
		));
	}


	/**
	 * New, updated and hot events
	 */
	protected function _tabs() {
		$tabs = array(
			'hot' => array('href' => '#events-hot', 'title' => __('Hot events'), 'tab' => View_Module::factory('events/event_list', array(
				'mod_id'    => 'events-hot',
				'mod_class' => 'cut tab events',
				'title'     => __('Hot Events'),
				'events'    =>  Jelly::select('event')->where('stamp_begin', '>', time())->order_by('favorite_count', 'DESC')->limit(20)->execute(),
			))),
			'active' => array('href' => '#events-new', 'title' => __('New events'), 'tab' => View_Module::factory('events/event_list', array(
				'mod_id'    => 'events-new',
				'mod_class' => 'cut tab events',
				'title'     => __('New Events'),
				'events'    =>  Jelly::select('event')->order_by('id', 'DESC')->limit(20)->execute(),
			))),
			'latest' => array('href' => '#events-updated', 'title' => __('Updated events'), 'tab' => View_Module::factory('events/event_list', array(
				'mod_id'    => 'events-updated',
				'mod_class' => 'cut tab events',
				'title'     => __('Updated Events'),
				'events'    =>  Jelly::select('event')->where('modified', 'IS NOT', null)->order_by('modified', 'DESC')->limit(20)->execute(),
			))),
		);

		Widget::add('side', View::factory('generic/tabs_side', array('id' => 'events-tab', 'tabs' => $tabs)));
	}

}
