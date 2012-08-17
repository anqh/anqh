<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Events controller
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Events extends Controller_Page {

	/**
	 * @var  array  Override default action views
	 */
	protected $_action_views = array(
		'add' => 'edit',
	);

	/**
	 * @var  DateTime
	 */
	public $date;

	/**
	 * @var  integer  Browse from
	 */
	public $stamp_begin = null;

	/**
	 * @var  integer  Browse to
	 */
	public $stamp_end = null;

	/**
	 * @var  integer  Next page
	 */
	public $stamp_next;

	/**
	 * @var  integer  Previous page
	 */
	public $stamp_previous;


	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->date = new DateTime;
	}


	/**
	 * Action: add
	 */
	public function action_add() {
		return $this->_edit_event();
	}


	/**
	 * Action: browse calendar
	 */
	public function action_browse() {
		$this->page_title = __('Events');
		$this->tab_id     = 'browse';



/*
			Widget::add('main', View_Module::factory('generic/filters', array(
				'filters' => $this->_filters($events),
			)));
*/
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

		$this->request->redirect(Route::url('events_ymd', array('year' => date('Y', $date), 'month' => date('m', $date), 'day' => date('d', $date))));
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

		// Build page
		$this->view = View_Page::factory($event->name);

		// Set actions
		if (Permission::has($event, Model_Event::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array(
				'link'  => Route::model($event, 'edit'),
				'text'  => '<i class="icon-edit"></i> ' . __('Edit event'),
				'class' => 'event-edit',
			);
		}
		if (Permission::has($event, Model_Event::PERMISSION_FAVORITE, self::$user)) {
			if ($event->is_favorite(self::$user)) {
				$this->page_actions[] = array(
					'link'  => Route::model($event, 'unfavorite') . '?token=' . Security::csrf(),
					'text'  => '<i class="icon-heart"></i> ' . __('Remove favorite'),
					'class' => 'favorite-delete',
				);
			} else {
				$this->page_actions[] = array(
					'link'  => Route::model($event, 'favorite') . '?token=' . Security::csrf(),
					'text'  => '<i class="icon-heart icon-white"></i> ' . __('Add to favorites'),
					'class' => 'btn-primary favorite-add',
				);
			}
		}
		$this->page_actions[] = array(
			'link'  => Route::get('gallery_event')->uri(array('id' => $event->id)),
			'text'  => '<i class="icon-camera"></i> ' . __('Gallery') . ' &raquo;',
		);
		$this->page_actions[] = array(
			'link'  => Route::get('forum_event')->uri(array('id' => $event->id)),
			'text'  => '<i class="icon-comment"></i> ' . __('Forum') . ' &raquo;',
		);


		// Share
		if (Kohana::config('site.facebook')) {
			Anqh::open_graph('type', 'activity');
			Anqh::open_graph('title', $event->name);
			Anqh::open_graph('url', URL::site(Route::get('event')->uri(array('id' => $event->id, 'action' => '')), true));
			Anqh::open_graph('description', date('l ', $event->stamp_begin) . Date::format(Date::DMY_SHORT, $event->stamp_begin) . ' @ ' . $event->venue_name);
			if ($event->flyer_front()) {
				Anqh::open_graph('image', $event->flyer_front()->get_url('thumbnail'));
			}
		}
		Anqh::share(true);

		// Event main info
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_event_main($event));

		$this->view->add(View_Page::COLUMN_SIDE, $this->section_share());

		// Event flyers
		if (count($flyers = $event->flyers()) > 1) {
			$images = array();
			foreach ($flyers as $flyer) {
				$images[] = $flyer->image();
			}

			$classes = array();
			$event->flyer_front_image_id and $classes[$event->flyer_front_image_id] = 'front default active ';
			$event->flyer_back_image_id and $classes[$event->flyer_back_image_id] = 'back ';
			$this->view->add(View_Page::COLUMN_SIDE, View_Module::factory('generic/image_slideshow', array(
				'images'  => array_reverse($images),
				'classes' => $classes,
			)));
		}

		$this->view->add(View_Page::COLUMN_SIDE, $this->section_event_image($event));

		// Event side info
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_event_info($event));

		// Favorites
		if ($event->favorite_count) {
			$this->view->add(View_Page::COLUMN_SIDE, $this->section_event_favorites($event));
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
						$image->remote  = $url;
						$image->created = time();
						if ($event->author_id) {
							$image->author_id = $event->author_id;
						}
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

							// Save event, skipping validation
							$event->save(false);

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
						$event->save(false);
					} else if ($clear == 'back' && $event->flyer_back_url) {
						$event->flyer_back_url = null;
						$event->save(false);
					} else if ($clear == 'both') {
						$event->flyer_front_url = null;
						$event->flyer_back_url = null;
						$event->save(false);
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
		if ($this->_request_type !== Controller::REQUEST_AJAX) {
			return $this->action_event();
		}

		$event = Model_Event::factory((int)$this->request->param('id'));
		if ($event->loaded())	{
			$this->response->body(new View_Event_HoverCard($event));
		}
	}


	/**
	 * Action: image
	 */
	public function action_image() {
		$this->history = false;

		// Load event
		/** @var  Model_Event  $event */
		$event_id = (int)$this->request->param('id');
		$event    = Model_Event::factory($event_id);
		if (!$event->loaded()) {
			throw new Model_Exception($event, $event_id);
		}
		Permission::required($event, Model_Event::PERMISSION_UPDATE, self::$user);

		if ($this->_request_type !== Controller::REQUEST_AJAX) {
			$this->page_title = HTML::chars($event->name);
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
			if ($this->_request_type === Controller::REQUEST_AJAX) {
				$this->response->body($this->section_event_image($event));

				return;
			}

			$this->request->redirect(Route::model($event));
		}

		// Handle post
		$errors = array();
		if ($_POST && $_FILES) {
			$image = Model_Image::factory();
			$image->author_id   = self::$user->id;
			$image->created     = time();
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

				if ($this->_request_type === Controller::REQUEST_AJAX) {
					$this->response->body($this->section_event_image($event));

					return;
				}

				$this->request->redirect(Route::model($event));

			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			} catch (Kohana_Exception $e) {
				$errors = array('file' => __('Failed with image'));
			}
		}

		$this->view->errors = &$errors;
		$this->view->event  = &$event;

		$view = $this->section_flyer_upload($this->_request_type === Controller::REQUEST_AJAX ? Route::model($event, 'image') . '?cancel' : Route::model($event), $errors);
		if ($this->_request_type === Controller::REQUEST_AJAX) {
			$this->response->body($view);
		}
	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$year  = (int)$this->request->param('year');
		$month = (int)$this->request->param('month');
		$day   = (int)$this->request->param('day');
		$week  = (int)$this->request->param('week');

		// Default to upcoming events for current week
		if (!$day && !$month && !$year && !$week) {
			$this->stamp_begin = strtotime('today');
			$this->stamp_end   = strtotime('next monday', $this->stamp_begin);

			// Add one week if less than 3 days to next monday
			if ($this->stamp_end - $this->stamp_begin <= Date::DAY * 3) {
				$this->stamp_end = strtotime('+1 week', $this->stamp_end);
			}

			$this->stamp_previous = strtotime('last monday', $this->stamp_begin);
			$this->stamp_next     = $this->stamp_end;
		} else {
			$year = $year ? $year : date('Y');
			if ($week) {
				$this->stamp_begin = strtotime($year . '-W' . ($week < 10 ? '0' . $week : $week));
			} else {
				$day   = $day ? $day : date('j');
				$month = $month ? $month : date('n');
				$this->stamp_begin = mktime(0, 0, 0, $month, $day, $year);
			}
			$this->stamp_end      = strtotime('+1 week', $this->stamp_begin);
			$this->stamp_previous = strtotime('-1 week', $this->stamp_begin);
			$this->stamp_next     = $this->stamp_end;
		}


		// Build page
		$this->view = View_Page::factory(__('Events'));

		// Pagination
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_pagination());

		// Filters
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_filters());

		// Event list
		$this->view->add(View_Page::COLUMN_MAIN, $this->sections_events());

		// Calendar
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_calendar());

		// Hot events
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_events_hot());

		// New events
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_events_new());

		// Updated events
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_events_updated());

		// Set actions
		if (Permission::has(new Model_Flyer, Model_Flyer::PERMISSION_IMPORT, self::$user)) {
			$this->page_actions[] = array(
				'link' => Route::url('events', array('action' => 'flyers')),
				'text' => '<i class="icon-download-alt"></i> ' . __('Import flyers'),
			);
		}
		if (Permission::has(new Model_Event, Model_Event::PERMISSION_CREATE, self::$user)) {
			$this->page_actions[] = array(
				'link'  => Route::get('events')->uri(array('action' => 'add')),
				'text'  => '<i class="icon-plus-sign icon-white"></i> ' . __('Add event'),
				'class' => 'btn-primary event-add',
			);
		}

		// Load events
		$this->view->stamp = $this->stamp_begin;
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
	 * Edit event
	 *
	 * @param  integer  $event_id
	 */
	protected function _edit_event($event_id = null) {
		$this->history = false;

		if ($event_id) {

			// Editing old
			$event = Model_Event::factory($event_id);
			if (!$event->loaded()) {
				throw new Model_Exception($event, $event_id);
			}
			Permission::required($event, Model_Event::PERMISSION_UPDATE, self::$user);
			$cancel = Request::back(Route::model($event), true);

			$this->view = View_Page::factory(HTML::chars($event->name));

			// Set actions
			if (Permission::has($event, Model_Event::PERMISSION_DELETE, self::$user)) {
				$this->page_actions[] = array(
					'link' => Route::model($event, 'delete') . '?token=' . Security::csrf(),
					'text' => '<i class="icon-trash icon-white"></i> ' . __('Delete event'),
					'class' => 'btn-danger event-delete'
				);
			}
			$edit = true;


		} else {

			// Creating new
			$event = new Model_Event();
			Permission::required($event, Model_Event::PERMISSION_CREATE, self::$user);
			$cancel = Request::back(Route::get('events')->uri(), true);

			$this->view = View_Page::factory(__('New event'));

			$event->author_id = self::$user->id;
			$event->created   = time();
			$edit = false;

		}

		// Handle post
		if ($_POST && Security::csrf_valid()) {

			// Handle venue
			if ($venue_hidden = Arr::get($_POST, 'venue_hidden')) {

				// Hidden events require only city

			} else if ($venue_id = (int)Arr::get_once($_POST, 'venue_id')) {

				// Old venue
				$venue = Model_Venue::factory($venue_id);

			} else if ($venue_name = Arr::get($_POST, 'venue_name')) {

				// Check for duplicate venue
				$venues = Model_Venue::factory()->find_by_name($venue_name);
				if ($venues->count()) {
					$city_name = strtolower(Arr::get($_POST, 'city_name'));
					foreach ($venues as $venue_old) {
						if (strtolower($venue_old->city_name) == $city_name) {
							$venue = $venue_old;
							break;
						}
					}
				}

			}

			$post = Arr::intersect($_POST, Model_Event::$editable_fields);
			if (isset($post['stamp_begin']['date']) && isset($post['stamp_end']['time'])) {
				$post['stamp_end']['date'] = $post['stamp_begin']['date'];
			}
			$event->set_fields($post);
			if (Arr::get($_POST, 'free')) {
				$event->price = 0;
			}

			// Venue/location
			$event->venue_hidden = (bool)$venue_hidden;
			if ($venue_hidden) {

				// Hidden events don't have a venue
				$event->venue_id    = null;
				$event->venue_name  = null;

			} else if (isset($venue)) {

				// Venue loaded
				$event->venue_id  = $venue->id;
				$event->city_name = $venue->city_name;

			} else if (!empty($venue_name)) {

				// Create new venue
				$venue = Model_Venue::factory();
				$venue->name       = Arr::get($_POST, 'venue_name');
				$venue->address    = Arr::get($_POST, 'address');
				$venue->latitude   = Arr::get($_POST, 'latitude');
				$venue->longitude  = Arr::get($_POST, 'longitude');
				$venue->event_host = true;
				$venue->author_id  = self::$user->id;
				$venue->city_name  = $event->city_name;
				try {
					$venue->save();
					$event->venue_id = $venue->id;
				} catch (Validation_Exception $venue_validation) {}

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

				// Set tags
				$event->set_tags(Arr::get($_POST, 'tag'));

				$edit ? NewsfeedItem_Events::event_edit(self::$user, $event) : NewsfeedItem_Events::event(self::$user, $event);

				$this->request->redirect(Route::model($event));
			}
		}

		// Fill the required information to view
		$this->view->event = $event;
		$this->view->event_errors = isset($event_validation) ? $event_validation->array->errors('validation') : null;
		$this->view->venue = isset($venue) ? $venue : null;
		$this->view->venue_errors = isset($venue_validation) ? $venue_validation->array->errors('validation') : null;

		// Tags
		$tags = array();
		$tag_group = new Model_Tag_Group('Music');
		if ($tag_group->loaded() && count($tag_group->tags())) {
			foreach ($tag_group->tags() as $tag) {
				$tags[$tag->id()] = $tag->name();
			}
		}

		// Form
		Form::$bootsrap = true;
		$section = $this->section_event_edit($event);
		$section->event_errors = isset($event_validation) ? $event_validation->array->errors('validation') : null;
		$section->venue        = isset($venue) ? $venue : $event->venue;
		$section->venue_errors = isset($venue_validation) ? $venue_validation->array->errors('validation') : null;
		$section->cancel       = $cancel;
		$this->view->add(View_Page::COLUMN_TOP, $section);
	}


	/**
	 * Load events.
	 *
	 * @return  array
	 */
	private function _events() {
		static $events = null;

		if (is_null($events)) {
			$events = Model_Event::factory()->find_grouped_between($this->stamp_begin, $this->stamp_end, 'ASC');
		}

		return $events;
	}


	/**
	 * Get calendar.
	 *
	 * @return  View_Calendar
	 */
	public function section_calendar() {
		$section = new View_Calendar();
		$section->date      = $this->stamp_begin;
		$section->url_day   = Route::url('events') . '/:year/:month/:day';
		$section->url_week  = Route::url('events') . '/:year/week/:week';
		$section->url_month = Route::url('events') . '/:year/:month';

		return $section;
	}


	/**
	 * Get event edit form.
	 *
	 * @param   Model_Event  $event
	 * @return  View_Event_Edit
	 */
	public function section_event_edit(Model_Event $event) {
		return new View_Event_Edit($event);
	}


	/**
	 * Get event favorites.
	 *
	 * @param   Model_Event  $event
	 * @return  View_Users_List
	 */
	public function section_event_favorites(Model_Event $event) {
		$section = new View_Users_List($favorites = $event->find_favorites());
		$section->title = __('Favorites') . ' <small><i class="icon-heart"></i> ' . count($favorites) . '</small>';

		return $section;
	}


	/**
	 * Get side image.
	 *
	 * @param   Model_Event  $event
	 * @return  View_Generic_SideImage
	 */
	protected function section_event_image(Model_Event $event) {

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
			$uri     = Route::model($event, 'image');
			$actions = array();
			$actions[] = HTML::anchor($uri, '<i class="icon-plus-sign icon-white"></i> ' . __('Add flyer'), array('class' => 'btn btn-small btn-primary image-add ajaxify'));
			if ($image) {
				$actions[] = HTML::anchor($uri . '?token=' . Security::csrf() . '&front=' . $image->id,  __('As front'), array('class' => 'btn btn-small image-change' . ($event->flyer_front_image_id == $image->id ? ' disabled' : ''), 'data-change' => 'front'));
				$actions[] = HTML::anchor($uri . '?token=' . Security::csrf() . '&back=' . $image->id,   __('As back'),  array('class' => 'btn btn-small image-change' . ($event->flyer_back_image_id == $image->id ? ' disabled' : ''), 'data-change' => 'back'));
				$actions[] = HTML::anchor($uri . '?token=' . Security::csrf() . '&delete=' . $image->id, '<i class="icon-trash"></i> ' . __('Delete'), array('class' => 'btn btn-small image-delete'));
			}
		} else {
			$actions = null;
		}

		$section = new View_Generic_SideImage($image, $link);
		$section->actions = $actions;

		return $section;
	}


	/**
	 * Get event side info.
	 *
	 * @param   Model_Event  $event
	 * @return  View_Event_Info
	 */
	public function section_event_info(Model_Event $event) {
		return new View_Event_Info($event);
	}


	/**
	 * Get event main info.
	 *
	 * @param   Model_Event  $event
	 * @return  View_Event_Main
	 */
	public function section_event_main(Model_Event $event) {
		if ($event->dj || $event->info) {
			return new View_Event_Main($event);
		} else {
			return new View_Alert('No performer info available.', null, View_Alert::INFO);
		}
	}


	/**
	 * Get events.
	 *
	 * @return  View_Events_List
	 */
	public function section_events_hot() {
		$section = new View_Events_List();
		$section->title  = __('Hot events');
		$section->events = Model_Event::factory()->find_hot(20);

		return $section;
	}


	/**
	 * Get events.
	 *
	 * @return  View_Events_List
	 */
	public function section_events_new() {
		$section = new View_Events_List();
		$section->title  = __('New events');
		$section->events = Model_Event::factory()->find_new(15);

		return $section;
	}


	/**
	 * Get events.
	 *
	 * @return  View_Events_List
	 */
	public function section_events_updated() {
		$section = new View_Events_List();
		$section->title  = __('Updated events');
		$section->events = Model_Event::factory()->find_modified(10);

		return $section;
	}


	/**
	 * Get filters.
	 *
	 * @return  View_Generic_Filters
	 */
	public function section_filters() {
		$section = new View_Generic_Filters();
		$section->filters = $this->_filters($this->_events());

		return $section;
	}


	/**
	 * Get flyer upload.
	 *
	 * @param   string  $cancel  URL
	 * @param   array   $errors
	 * @return  View_Generic_Upload
	 */
	public function section_flyer_upload($cancel = null, $errors = null) {
		$section = new View_Generic_Upload();
		$section->title  = __('Add flyer');
		$section->cancel = $cancel;
		$section->errors = $errors;

		return $section;
	}


	/**
	 * Get pagination.
	 *
	 * @return  View_Generic_Pagination
	 */
	public function section_pagination() {
		return new View_Generic_Pagination(array(
			'previous_text' => '&laquo; ' . __('Previous events'),
			'next_text'     => __('Next events') . ' &raquo;',
			'previous_url'  => Route::url('events_ymd', array(
					'year'  => date('Y', $this->stamp_previous),
					'month' => date('m', $this->stamp_previous),
					'day'   => date('d', $this->stamp_previous))),
			'next_url'      => Route::url('events_ymd', array(
					'year'  => date('Y', $this->stamp_next),
					'month' => date('m', $this->stamp_next),
					'day'   => date('d', $this->stamp_next))),
		));
	}


	/**
	 * Get events.
	 *
	 * @return  View_Events_Day[]
	 */
	public function sections_events() {
		if (!$this->_events()) {
			return new View_Alert(__('There be no events for selected period, period.'), null, View_Alert::INFO);
		}

		$days = array();
		foreach ($this->_events() as $date => $cities) {
			$section = new View_Events_Day();
			$section->date   = $date;
			$section->events = $cities;

			$days[] = $section;
		}

		return $days;
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
