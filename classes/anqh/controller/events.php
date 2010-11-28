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
			//$this->page_subtitle = __2(':events event', ':events events', count($events), array(':events' => '<var>' . count($events) . '</var>'));

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
		)), Widget::TOP);

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
		$this->page_subtitle  = HTML::time(Date('l ', $event->stamp_begin) . Date::format(Date::DMY_SHORT, $event->stamp_begin), $event->stamp_begin, true) . '. ';

		// Event performers and extra info
		Widget::add('main', View_Module::factory('events/event', array('event' => $event)));

		// Event flyers
		if (count($event->flyers) > 1) {
			$images = array();
			foreach ($event->flyers as $image) $images[] = $image;
			$classes = array();
			$event->flyer_front and $classes[$event->flyer_front->id] = 'front default active ';
			$event->flyer_back and $classes[$event->flyer_back->id] = 'back ';
			Widget::add('side', View_Module::factory('generic/image_slideshow', array(
				'images'  => array_reverse($images),
				'classes' => $classes,
			)));
		}

		if (count($event->flyers)) {
			Widget::add('side', $this->_get_mod_image($event));
		} else if ($event->flyer_front_url || $event->flyer_back_url) {

			// To be deprecated
			Widget::add('side', View_Module::factory('events/flyers', array(
				'event' => $event,
			)));

		}

		// Event quick info
		Widget::add('side', View_Module::factory('events/event_info', array(
			//'mod_title' => __('Event information'),
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

		if (isset($_REQUEST['front'])) {

			// Change front flyer
			/** @var  Model_Image  $image */
			$image = Jelly::select('image')->load((int)$_REQUEST['front']);
			if (Security::csrf_valid() && $image->loaded() && $event->has('flyers', $image)) {
				$event->flyer_front = $image;
				$event->flyer_front_url = $image->get_url();
				$event->save();
			}
			$cancel = true;

		} else if (isset($_REQUEST['back'])) {

			// Change back flyer
			/** @var  Model_Image  $image */
			$image = Jelly::select('image')->load((int)$_REQUEST['back']);
			if (Security::csrf_valid() && $image->loaded() && $event->has('flyers', $image)) {
				$event->flyer_back = $image;
				$event->flyer_back_url = $image->get_url();
				$event->save();
			}
			$cancel = true;

		} else if (isset($_REQUEST['delete'])) {

			// Delete existing
			$image = Jelly::select('image')->load((int)$_REQUEST['delete']);
			if (Security::csrf_valid() && $image->loaded() && $event->has('flyers', $image)) {
				$event->remove('flyers', $image);
				if ($image->id == $event->flyer_front->id) {
					$event->flyer_front = null;
					$event->flyer_front_url = null;
				} else if ($image->id == $event->flyer_back->id) {
					$event->flyer_back = null;
					$event->flyer_back_url = null;
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
				//$event->add_flyer($image);
				$event->add('flyers', $image);
				if ($event->flyer_front->id) {
					if (!$event->flyer_back->id) {

						// Back flyer not set, set it
						$event->flyer_back = $image;
						$event->flyer_back_url = $image->get_url();

					}
				} else {

					// Front flyer not set, set it
					$event->flyer_front = $image;
					$event->flyer_front_url = $image->get_url();

				}
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
			//$this->page_subtitle = __2(':events event', ':events events', count($events), array(':events' => '<var>' . count($events) . '</var>'));

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
		)), Widget::TOP);

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
			//$this->page_subtitle = __2(':events event', ':events events', count($events), array(':events' => '<var>' . count($events) . '</var>'));

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
		)), Widget::TOP);

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

			// Old version
			/*
			if (!count($event->tags) && $event->music) {
				$tag_group = Jelly::select('tag_group')->where('name', '=', 'Music')->limit(1)->execute();
				if ($tag_group->loaded() && count($tag_group->tags)) {
					$tags = array();
					foreach ($tag_group->tags as $tag) {
						$tags[$tag->name()] = $tag->id();
					}
				}
				$musics = explode(',', $event->music);
				foreach ($musics as $music) {
					$music = trim($music);
					if ($tags[$music]) {
						$event->add('tag', $tags[$music]);
					}
				}
			}
			 */

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
		if ($_POST && Security::csrf_valid()) {

			// Handle venue
			if ($venue_id = (int)Arr::get_once($_POST, 'venue')) {

				// Old venue
				$venue = Jelly::select('venue')->load($venue_id);

			} else if ($venue_name = Arr::get($_POST, 'venue_name')) {

				// New venue
				if ($city_id = (int)Arr::get_once($_POST, 'city_id')) {
					$city = Geo::find_city($city_id);
				}
				if ($foursquare_id = (int)Arr::get_once($_POST, 'foursquare_id')) {

					// Foursquare venue
					// @todo: Refetch data using id?
					$venue = Model_Venue::find_by_foursquare($foursquare_id);
					if (!$venue->loaded()) {
						$venue = Jelly::factory('venue')->set(array(
							'foursquare_id'          => $foursquare_id,
							'foursquare_category_id' => Arr::get_once($_POST, 'foursquare_category_id')
						));
					}

				} else {

					// Check for duplicate venue
					$venues = Model_Venue::find_by_name($venue_name);
					if ($venues->count()) {
						$address   = strtolower(trim(Arr::get($_POST, 'address')));
						$city_name = strtolower(isset($city) ? $city->name : Arr::get($_POST, 'city_name'));
						foreach ($venues as $venue_old) {
							if (strtolower($venue_old->city_name) == $city_name && (empty($address) || empty($venue_old->address) || levenshtein(strtolower($venue_old->address), $address) < 4)) {

								// Venue in same town with almost same address, assume same
								$venue = $venue_old;
								break;

							}
						}
					}

				}

				// Fill rest of the venue info if not found
				!isset($venue) and $venue = Jelly::factory('venue');
				if (!$venue->loaded()) {
					$venue->name       = Arr::get($_POST, 'venue_name');
					$venue->address    = Arr::get($_POST, 'address');
					$venue->latitude   = Arr::get($_POST, 'latitude');
					$venue->longitude  = Arr::get($_POST, 'longitude');
					$venue->event_host = true;
					$venue->author     = self::$user;
					if (isset($city) && $city->loaded()) {
						$venue->city = $city;
						$venue->city_name = $city->name;
						$venue->country = $city->country;
					} else {
						$venue->city_name = Arr::get($_POST, 'city_name');
					}
					try {
						$venue->save();
					} catch (Validate_Exception $venue_validation) {}
				}

			}

			$post = Arr::extract($_POST, Model_Event::$editable_fields);
			if (isset($post['stamp_begin']['date']) && isset($post['stamp_end']['time'])) {
				$post['stamp_end']['date'] = $post['stamp_begin']['date'];
			}
			$event->set($post);

			// Set venue to event if venue is saved
			if (isset($venue)) {
				$event->venue = $venue;
			}
			if (isset($city)) {
				$event->city = $city;
				$event->city_name = $city->name;
				$event->country = $city->country;
			}

			// Old version
			if (count($event->tags)) {
				$music = array();
				foreach ($event->tags as $tag) {
					$music[] = $tag->name;
				}
				$event->music = implode(', ', $music);
			}

			// Validate event
			try {
				$event->validate();
			} catch (Validate_Exception $event_validation) {}

			// If no errors found, save
			if (!isset($venue_validation) && !isset($event_validation)) {

				// Make sure end time is after start time, i.e. the next day
				if ($event->stamp_end < $event->stamp_begin) {
					$event->stamp_end += 60 * 60 * 24;
				}

				$event->save();

				$edit ? NewsfeedItem_Events::event_edit(self::$user, $event) : NewsfeedItem_Events::event(self::$user, $event);

				$this->request->redirect(Route::model($event));
			}
		}

		// Tags
		$tags = array();
		$tag_group = Jelly::select('tag_group')->where('name', '=', 'Music')->limit(1)->execute();
		if ($tag_group->loaded() && count($tag_group->tags)) {
			foreach ($tag_group->tags as $tag) {
				$tags[$tag->id()] = $tag->name();
			}
		}

		// Venue
		/*
		$venues = Jelly::select('venue')->with('city')->event_hosts()->order_by('name', 'ASC')->execute();
		$hosts = array();
		if (count($venues)) {
			foreach ($venues as $v) {
				$hosts[] = array(
					'value'     => $v->id,
					'label'     => HTML::chars($v->name),
					'address'   => HTML::chars($v->address),
					'city'      => HTML::chars($v->city->name),
					'city_id'   => $v->city->id,
					'latitude'  => $v->latitude,
					'longitude' => $v->longitude,
				);
			}
		}
		 */

		Widget::add('wide', View_Module::factory('events/edit', array(
			'event'  => $event,
			'event_errors' => isset($event_validation) ? $event_validation->array->errors('validation') : null,
			'tags'   => $tags,

			'venue'  => isset($venue) ? $venue : $event->venue,
			'venue_errors' => isset($venue_validation) ? $venue_validation->array->errors('validation') : null,
			// 'venues' => $hosts,

			'city'   => $event->city->loaded() ? $event->city : (isset($venue) && $venue->city->loaded() ? $venue->city : null),
			'cancel' => $cancel,
		)));
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
		} else if (count($event->flyers)) {
			$image = $event->flyers[0];
		} else {
			$image = null;
		}

		if (Permission::has($event, Model_User::PERMISSION_UPDATE, self::$user)) {
			$actions = array();
			$actions[] = array('link' => Route::model($event, 'image'), 'text' => __('Add flyer'), 'class' => 'image-add ajaxify');
			if ($image) {
				$actions[] = array('link' => Route::model($event, 'image') . '?token=' . Security::csrf() . '&front=' . $image->id,  'text' => __('As front'), 'class' => 'image-change' . ($event->flyer_front->id == $image->id ? ' disabled' : ''), 'data-change' => 'front');
				$actions[] = array('link' => Route::model($event, 'image') . '?token=' . Security::csrf() . '&back=' . $image->id,   'text' => __('As back'), 'class' => 'image-change' . ($event->flyer_back->id == $image->id ? ' disabled' : ''), 'data-change' => 'back');
				$actions[] = array('link' => Route::model($event, 'image') . '?token=' . Security::csrf() . '&delete=' . $image->id, 'text' => __('Delete'), 'class' => 'image-delete');
			}
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
				'events'    =>  Model_Event::find_hot(20),
			))),
			'active' => array('href' => '#events-new', 'title' => __('New events'), 'tab' => View_Module::factory('events/event_list', array(
				'mod_id'    => 'events-new',
				'mod_class' => 'cut tab events',
				'title'     => __('New Events'),
				'events'    =>  Model_Event::find_new(20),
			))),
			'latest' => array('href' => '#events-updated', 'title' => __('Updated events'), 'tab' => View_Module::factory('events/event_list', array(
				'mod_id'    => 'events-updated',
				'mod_class' => 'cut tab events',
				'title'     => __('Updated Events'),
				'events'    => Model_Event::find_modified(20),
			))),
		);

		Widget::add('side', View::factory('generic/tabs_side', array('id' => 'events-tab', 'tabs' => $tabs)));
	}

}
