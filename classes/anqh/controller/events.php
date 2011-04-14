<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Events controller
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
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

		if (Permission::has(new Model_Flyer, Model_Flyer::PERMISSION_IMPORT, self::$user)) {
			$this->tabs['flyers'] = array('url' => Route::get('events')->uri(array('action' => 'flyers')),   'text' => __('Import flyers'));
		}
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
		$events = Model_Event::factory()->find_grouped_between($first, $last);
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
		$event = Model_Event::factory($event_id);
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
		/** @var  Model_Event  $event */
		$event = Model_Event::factory($event_id);
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
				$this->page_actions[] = array('link' => Route::model($event, 'favorite') . '?token=' . Security::csrf(), 'text' => __('Add to favorites'), 'class' => 'favorite-add');
			}
		}

		$this->page_title = HTML::chars($event->name);
		$this->page_subtitle  = HTML::time(date('l ', $event->stamp_begin) . Date::format(Date::DMY_SHORT, $event->stamp_begin), $event->stamp_begin, true);
		$this->page_subtitle .= ' | ' . HTML::anchor(Route::get('forum_event')->uri(array('id' => $event->id)), __('Go to discussion'));
		$this->page_subtitle .= ' | ' . HTML::anchor(Route::get('gallery_event')->uri(array('id' => $event->id)), __('Go to gallery'));

		// Facebook
		if (Kohana::config('site.facebook')) {
			Anqh::open_graph('type', 'activity');
			Anqh::open_graph('title', $this->page_title);
			Anqh::open_graph('url', URL::site(Route::get('event')->uri(array('id' => $event->id, 'action' => '')), true));
			Anqh::open_graph('description', date('l ', $event->stamp_begin) . Date::format(Date::DMY_SHORT, $event->stamp_begin) . ' @ ' . $event->venue_name);
			$event->flyer_front and Anqh::open_graph('image', URL::site($event->flyer_front->get_url('thumbnail'), true));
		}
		Anqh::share(true);

		// Event performers and extra info
		Widget::add('main', View_Module::factory('events/event', array('event' => $event)));

		// Event flyers
		if (count($flyers = $event->flyers()) > 1) {
			$images = array();
			foreach ($flyers as $flyer) {
				$images[] = $flyer->image();
			}

			$classes = array();
			$event->flyer_front_image_id and $classes[$event->flyer_front_image_id] = 'front default active ';
			$event->flyer_back_image_id and $classes[$event->flyer_back_image_id] = 'back ';
			Widget::add('side', View_Module::factory('generic/image_slideshow', array(
				'images'  => array_reverse($images),
				'classes' => $classes,
			)));
		}

		Widget::add('side', $this->_get_mod_image($event));

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
		$event = Model_Event::factory($event_id);
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
	 * Action: import flyers
	 */
	public function action_flyers() {
		$this->history = false;
		$this->tab_id = 'flyers';

		Permission::required(new Model_Flyer, Model_Flyer::PERMISSION_IMPORT, self::$user);

		// Action
		if ($event_ids = (array)Arr::get($_REQUEST, 'event_id')) {
			foreach ($event_ids as $event_id) {

				/** @var  Model_Event  $event */
				$event = Model_Event::factory($event_id);
				if (!$event->loaded()) {
					throw new Model_Exception($event, $event_id);
				}

				if ($import = Arr::get($_REQUEST, 'import')) {

					// Import flyer
					switch ($import) {
						case 'front': $urls = array('front' => $event->flyer_front_url); break;
						case 'back':  $urls = array('back' => $event->flyer_back_url); break;
						case 'both':  $urls = array('back' => $event->flyer_back_url, 'front' => $event->flyer_front_url); break;
						default: continue;
					}

					// Create flyers
					foreach ($urls as $side => $url) {
						if (!$url) continue;

						$image = new Model_Image();
						$image->remote = $url;
						$image->created = time();
						$event->author_id and $image->author_id = $event->author_id;
						try {
							$image->save();

							// Set the image as flyer
							try {
								$flyer = new Model_Flyer();
								$flyer->set_fields(array(
									'image_id'    => $image->id,
									'event_id'    => $event->id,
									'name'        => $event->name,
									'stamp_begin' => $event->stamp_begin,
								));
								$flyer->save();
							} catch (Kohana_Exception $e) {
								//$event->relate('flyers', array($image->id));
							}

							if ($side == 'front') {
								$event->flyer_front_image_id = $image->id;
								$event->flyer_front_url = $image->get_url();
							} else if ($side == 'back') {
								$event->flyer_back_image_id = $image->id;
								$event->flyer_back_url = $image->get_url();
							}
							$event->save();

							Widget::add('main', HTML::anchor(Route::model($event), HTML::image($image->get_url('thumbnail'))));

						} catch (Validation_Exception $e) {
							Widget::add('main', Debug::dump($e->array->errors('validation')));
						} catch (Kohana_Exception $e) {
							Widget::add('main', $e->getMessage() . '<br />');
						}
					}

				} else if ($clear = Arr::get($_REQUEST, 'clear')) {

					// Clear flyer
					if ($clear == 'front' && $event->flyer_front_url) {
						$event->flyer_front_url = null;
						$event->save();
					} else if ($clear == 'back' && $event->flyer_back_url) {
						$event->flyer_back_url = null;
						$event->save();
					} else if ($clear == 'both') {
						$event->flyer_front_url = null;
						$event->flyer_back_url = null;
						$event->save();
					}

				}
			}
		}


		// Load importable flyers
		$events = Model_Event::factory()->load(
			DB::select_array(Model_Event::factory()->fields())
				->where_open()
					->where(DB::expr('CHAR_LENGTH(flyer_front_url)'), '>', 4)
					->and_where(DB::expr('COALESCE(flyer_front_image_id, 0)'), '=', 0)
				->where_close()
				->or_where_open()
					->where(DB::expr('CHAR_LENGTH(flyer_back_url)'), '>', 4)
					->and_where(DB::expr('COALESCE(flyer_back_image_id, 0)'), '=', 0)
				->where_close()
				->order_by('id', 'ASC'),
			100
		);

		if (count($events)) {
			Widget::add('main', Form::open(null, array('method' => 'get')));
			foreach ($events as $event) {
				if ($event->flyer_front_url && !$event->flyer_front_image_id) {
					$front  = '<p style="overflow: hidden">';
					$front .= HTML::anchor($event->flyer_front_url, HTML::image($event->flyer_front_url, array('width' => '100')), array('target' => '_blank')) . ' ';
					$front .= HTML::anchor(Route::get('events')->uri(array('action' => 'flyers')) . '?event_id=' . $event->id . '&import=front', __('Import front')) . ': ' . $event->flyer_front_url . '<br />';
					$front .= HTML::anchor(Route::get('events')->uri(array('action' => 'flyers')) . '?event_id=' . $event->id . '&clear=front', __('Clear front'));
					$front .= '</p>';
				} else {
					$front = '';
				}
				if ($event->flyer_back_url && !$event->flyer_back_image_id) {
					$back  = '<p style="overflow: hidden">';
					$back .= HTML::anchor($event->flyer_back_url, HTML::image($event->flyer_back_url, array('width' => '100')), array('target' => '_blank')) . ' ';
					$back .= HTML::anchor(Route::get('events')->uri(array('action' => 'flyers')) . '?event_id=' . $event->id . '&import=back', __('Import back')) . ': ' . $event->flyer_back_url . '<br />';
					$back .= HTML::anchor(Route::get('events')->uri(array('action' => 'flyers')) . '?event_id=' . $event->id . '&clear=back', __('Clear back'));
					$back .= '</p>';
				} else {
					$back = '';
				}
				Widget::add('main', '<h4>' . Form::checkbox('event_id[]', $event->id) . ' ' . $event->id . ': ' . HTML::anchor(Route::model($event), HTML::chars($event->name)) . '</h4>' . $front . $back);
			}
			Widget::add('main',
				Form::checkbox('event_id_all', null, false, array('onchange' => '$("input[type=checkbox]").attr("checked", this.checked);')) . __('Choose all') . ' ' .
				Form::button('clear', __('Clear'), array('type' => 'submit', 'value' => 'both')) . ' ' .
				Form::button('import', __('Import'), array('type' => 'submit', 'value' => 'both')));
			Widget::add('main', Form::close());
		}
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

		$event = Model_Event::factory((int)$this->request->param('id'));
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
		/** @var  Model_Event  $event */
		$event = Model_Event::factory($event_id);
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
			/** @var  Model_Flyer  $flyer */
			$flyer = Model_Flyer::factory()->find_by_image((int)$_REQUEST['front']);
			if (Security::csrf_valid() && $flyer->loaded() && $flyer->event_id == $event->id) {
				$event->flyer_front_image_id = $flyer->image_id;
				$event->flyer_front_url      = $flyer->image()->get_url();
				$event->save();
			}
			$cancel = true;

		} else if (isset($_REQUEST['back'])) {

			// Change back flyer
			/** @var  Model_Flyer  $flyer */
			$flyer = Model_Flyer::factory()->find_by_image((int)$_REQUEST['back']);
			if (Security::csrf_valid() && $flyer->loaded() && $flyer->event_id == $event->id) {
				$event->flyer_back_image_id = $flyer->image_id;
				$event->flyer_back_url      = $flyer->image()->get_url();
				$event->save();
			}
			$cancel = true;

		} else if (isset($_REQUEST['delete'])) {

			// Delete existing
			/** @var  Model_Flyer  $flyer */
			$flyer = Model_Flyer::factory()->find_by_image((int)$_REQUEST['delete']);
			if (Security::csrf_valid() && $flyer->loaded() && $flyer->event_id == $event->id) {
				if ($flyer->image_id == $event->flyer_front_image_id) {
					$event->flyer_front_image_id = null;
					$event->flyer_front_url      = null;
				} else if ($flyer->image_id == $event->flyer_back_image_id->id) {
					$event->flyer_back_image_id = null;
					$event->flyer_back_url      = null;
				}
				$event->save();
				$flyer->delete();
			}
			$cancel = true;

		}

		// Cancel change
		if (isset($cancel) || isset($_REQUEST['cancel'])) {
			if ($this->ajax) {
				$this->response->body($this->_get_mod_image($event));

				return;
			}

			$this->request->redirect(Route::model($event));
		}

		$image = Model_Image::factory();
		$image->author_id = self::$user->id;
		$image->created   = time();

		// Handle post
		$errors = array();
		if ($_POST && $_FILES) {
			$image->file        = Arr::get($_FILES, 'file');
			$image->description = $event->get_forum_topic();
			try {
				$image->save();

				// Add exif, silently continue if failed - not critical
				try {
					$exif = Model_Image_Exif::factory();
					$exif->image_id = $image->id;
					$exif->save();
				} catch (Kohana_Exception $e) { }

				// Add flyer
				try {
					$flyer = Model_Flyer::factory();
					$flyer->set_fields(array(
						'image_id'    => $image->id,
						'event_id'    => $event->id,
						'name'        => $event->name,
						'stamp_begin' => $event->stamp_begin,
					));
					$flyer->save();
				} catch (Kohana_Exception $e) {
//					$event->add('flyers', $image);
				}

				if ($event->flyer_front_image_id) {
					if (!$event->flyer_back_image_id) {

						// Back flyer not set, set it
						$event->flyer_back_image_id = $image->id;
						$event->flyer_back_url      = $image->get_url();

					}
				} else {

					// Front flyer not set, set it
					$event->flyer_front_image_id = $image->id;
					$event->flyer_front_url      = $image->get_url();

				}
				$event->save();

				NewsfeedItem_Events::event_edit(self::$user, $event);

				if ($this->ajax) {
					$this->response->body($this->_get_mod_image($event));
					return;
				}

				$this->request->redirect(Route::model($event));

			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			} catch (Kohana_Exception $e) {
				$errors = array('file' => __('Failed with image'));
			}
		}

		$view = View_Module::factory('events/flyer_upload', array(
			'mod_title' => __('Add flyer'),
			'ajaxify'   => $this->ajax,
			'errors'    => $errors,
			'cancel'    => $this->ajax ? Route::model($event, 'image') . '?cancel' : Route::model($event),
		));

		if ($this->ajax) {
			$this->response->body($view);

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
		$events = Model_Event::factory()->find_grouped_past(25);
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
		$event = Model_Event::factory($event_id);
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
		$events = Model_Event::factory()->find_grouped_upcoming(25);
		if (count($events)) {
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
			$event = Model_Event::factory($event_id);
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
			$event = Model_Event::factory();
			Permission::required($event, Model_Event::PERMISSION_CREATE, self::$user);
			$cancel = Request::back(Route::get('events')->uri(), true);

			$this->page_title = __('New event');

			$event->author_id = self::$user->id;
			$edit = false;

		}

		// Handle post
		if ($_POST && Security::csrf_valid()) {

			// Handle venue
			if ($venue_hidden = Arr::get($_POST, 'venue_hidden')) {

				// Hidden events require only city

			} else if ($venue_id = (int)Arr::get_once($_POST, 'venue')) {

				// Old venue
				$venue = Model_Venue::factory($venue_id);

			} else if ($venue_name = Arr::get($_POST, 'venue_name')) {

				// New venue
				if ($city_id = (int)Arr::get_once($_POST, 'city_id')) {
					$city = Geo::find_city($city_id);
				}

				if ($foursquare_id = (int)Arr::get_once($_POST, 'foursquare_id')) {

					// Foursquare venue
					// @todo: Refetch data using id?
					$venue = Model_Venue::factory()->find_by_foursquare($foursquare_id);
					if (!$venue->loaded()) {
						$venue = Model_Venue::factory();
						$venue->set_fields(array(
							'foursquare_id'          => $foursquare_id,
							'foursquare_category_id' => Arr::get_once($_POST, 'foursquare_category_id')
						));
					}

				} else {

					// Check for duplicate venue
					$venues = Model_Venue::factory()->find_by_name($venue_name);
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

				// Fill rest of the venue info if not found and not hidden
				if (!$venue_hidden) {
					!isset($venue) and $venue = Model_Venue::factory();
					if (!$venue->loaded()) {
						$venue->name       = Arr::get($_POST, 'venue_name');
						$venue->address    = Arr::get($_POST, 'address');
						$venue->latitude   = Arr::get($_POST, 'latitude');
						$venue->longitude  = Arr::get($_POST, 'longitude');
						$venue->event_host = true;
						$venue->author_id  = self::$user->id;
						if (isset($city) && $city->loaded()) {
							$venue->geo_city_id    = $city->id;
							$venue->city_name      = $city->name;
							$venue->geo_country_id = $city->geo_country_id;
						} else {
							$venue->city_name = Arr::get($_POST, 'city_name');
						}
						try {
							$venue->save();
						} catch (Validation_Exception $venue_validation) {}
					}
				}

			}

			$post = Arr::intersect($_POST, Model_Event::$editable_fields);
			if (isset($post['stamp_begin']['date']) && isset($post['stamp_end']['time'])) {
				$post['stamp_end']['date'] = $post['stamp_begin']['date'];
			}
			$event->set_fields($post);

			if ($venue_hidden) {

				// Hidden events don't have a venue
				$event->venue_id     = null;
				$event->venue_name   = null;
				$event->venue_hidden = true;

			} else {
				$event->venue_hidden = false;

				// Set venue to event if venue is saved
				if (isset($venue)) {
					$event->venue_id = $venue->id;
				}

			}
			if (isset($city)) {
				$event->geo_city_id    = $city->id;
				$event->city_name      = $city->name;
				$event->geo_country_id = $city->geo_country_id;
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
				$event->is_valid();
			} catch (Validation_Exception $event_validation) {}

			// If no errors found, save
			if (!isset($venue_validation) && !isset($event_validation)) {

				// Make sure end time is after start time, i.e. the next day
				if ($event->stamp_end < $event->stamp_begin) {
					$event->stamp_end += Date::DAY;
				}

				$event->save();

				$edit ? NewsfeedItem_Events::event_edit(self::$user, $event) : NewsfeedItem_Events::event(self::$user, $event);

				$this->request->redirect(Route::model($event));
			}
		}

		// Tags
		$tags = array();
		$tag_group = new Model_Tag_Group('Music');
		if ($tag_group->loaded() && count($tag_group->tags)) {
			foreach ($tag_group->tags as $tag) {
				$tags[$tag->id()] = $tag->name();
			}
		}

		Widget::add('wide', View_Module::factory('events/edit', array(
			'event'        => $event,
			'event_errors' => isset($event_validation) ? $event_validation->array->errors('validation') : null,
			'tags'         => $tags,

			'flyer_front'  => $event->flyer_front ? $event->flyer_front : null,
			'flyer_back'   => $event->flyer_back ? $event->flyer_back : null,

			'venue'        => isset($venue) ? $venue : $event->venue,
			'venue_errors' => isset($venue_validation) ? $venue_validation->array->errors('validation') : null,
			'venues'       => Model_Venue::factory()->find_all(),
			'city'         => $event->city() ? $event->city() : (isset($venue) && $venue->city() ? $venue->city() : null),

			'cancel'       => $cancel,
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
		if ($image = $event->flyer_front()) {
			$flyer = Model_Flyer::factory()->find_by_image($image->id);
			$link  = Route::model($flyer);
		} else if ($image = $event->flyer_back()) {
			$flyer = Model_Flyer::factory()->find_by_image($image->id);
			$link  = Route::model($flyer);
		} else if (count($flyers = $event->flyers())) {
			$flyer = $flyers[0];
			$image = $flyer->image();
			$link  = Route::model($flyer);
		} else {
			$image = null;
			$link  = null;
		}

		if (Permission::has($event, Model_User::PERMISSION_UPDATE, self::$user)) {
			$actions = array();
			$actions[] = array('link' => Route::model($event, 'image'), 'text' => __('Add flyer'), 'class' => 'image-add ajaxify');
			if ($image) {
				$actions[] = array('link' => Route::model($event, 'image') . '?token=' . Security::csrf() . '&front=' . $image->id,  'text' => __('As front'), 'class' => 'image-change' . ($event->flyer_front_image_id == $image->id ? ' disabled' : ''), 'data-change' => 'front');
				$actions[] = array('link' => Route::model($event, 'image') . '?token=' . Security::csrf() . '&back=' . $image->id,   'text' => __('As back'), 'class' => 'image-change' . ($event->flyer_back_image_id == $image->id ? ' disabled' : ''), 'data-change' => 'back');
				$actions[] = array('link' => Route::model($event, 'image') . '?token=' . Security::csrf() . '&delete=' . $image->id, 'text' => __('Delete'), 'class' => 'image-delete');
			}
		} else {
			$actions = null;
		}
		return View_Module::factory('generic/side_image', array(
			'mod_actions2' => $actions,
			'image'        => $image,
			'link'         => $link,
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
				'events'    =>  Model_Event::factory()->find_hot(20),
			))),
			'active' => array('href' => '#events-new', 'title' => __('New events'), 'tab' => View_Module::factory('events/event_list', array(
				'mod_id'    => 'events-new',
				'mod_class' => 'cut tab events',
				'title'     => __('New Events'),
				'events'    =>  Model_Event::factory()->find_new(20),
			))),
			'latest' => array('href' => '#events-updated', 'title' => __('Updated events'), 'tab' => View_Module::factory('events/event_list', array(
				'mod_id'    => 'events-updated',
				'mod_class' => 'cut tab events',
				'title'     => __('Updated Events'),
				'events'    => Model_Event::factory()->find_modified(20),
			))),
		);

		Widget::add('side', View::factory('generic/tabs_side', array('id' => 'events-tab', 'tabs' => $tabs)));
	}

}
