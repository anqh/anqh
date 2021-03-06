<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Events controller.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
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

		if ($this->_request_type !== Controller::REQUEST_AJAX) {
			$this->view->search = View_Page::SEARCH_EVENTS;
		}
	}


	/**
	 * Action: add
	 */
	public function action_add() {
		$this->_edit_event();
	}


	/**
	 * Action: delete event
	 */
	public function action_delete() {
		$this->history = false;

		// Load event
		$event_id = (int)$this->request->param('id');
		$event = Model_Event::factory($event_id);
		if (!$event->loaded()) {
			throw new Model_Exception($event, $event_id);
		}

		Permission::required($event, Model_Event::PERMISSION_DELETE);

		if (!Security::csrf_valid()) {
			$this->request->redirect(Route::model($event));
		}

		// Delete flyers
		if ($flyers = $event->flyers()) {
			foreach ($flyers as $flyer) {
				$flyer->delete();
			}
		}

		$date = $event->stamp_begin;
		$event->delete();

		$this->request->redirect(Route::url('events_ymd', array('year' => date('Y', $date), 'month' => date('m', $date), 'day' => date('d', $date))));
	}


	/**
	 * Action: edit event
	 */
	public function action_edit() {
		$this->_edit_event((int)$this->request->param('id'));
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
		Permission::required($event, Model_Event::PERMISSION_READ);

		// Build page
		$this->view->title    = $event->name;
		$this->view->subtitle = self::_event_subtitle($event);

		// Set actions
		if (Permission::has($event, Model_Event::PERMISSION_UPDATE)) {
			$this->view->actions[] = array(
				'link' => Route::model($event, 'edit'),
				'text' => '<i class="fa fa-edit"></i> ' . __('Edit event'),
			);
			$this->view->actions[] = array(
				'link'  => Route::model($event, 'flyer'),
				'text'  => '<i class="fa fa-upload"></i> ' . __('Upload flyer'),
				'class' => !count($event->flyers()) ? 'btn btn-primary' : null
			);
		}
		if (Permission::has($event, Model_Event::PERMISSION_FAVORITE)) {
			if ($event->is_favorite(Visitor::$user)) {
				$this->view->actions[] = array(
					'link'  => Route::model($event, 'unfavorite') . '?token=' . Security::csrf(),
					'text'  => '<i class="fa fa-heart"></i> ' . __('Remove favorite'),
				);
			} else {
				$this->view->actions[] = array(
					'link'  => Route::model($event, 'favorite') . '?token=' . Security::csrf(),
					'text'  => '<i class="fa fa-heart"></i> ' . __('Add to favorites'),
					'class' => 'btn-lovely favorite-add',
				);
			}
		}

		// Set tabs
		$this->view->tab = 'event';
		$this->view->tabs['event'] = array(
			'link' => Route::model($event),
			'text' => __('Event'),
		);
		if ($event->author_id) {
			$this->view->tabs['organizer'] = array(
				'link' => URL::user($event->author_id),
				'text' => __('Organizer') . ' &raquo;',
			);
		}
		if ($event->stamp_begin < time()) {

				// Link to gallery only after the event has begun
			$this->view->tabs[] = array(
				'link' => Route::get('gallery_event')->uri(array('id' => $event->id)),
				'text' => __('Gallery') . ' &raquo;',
			);

		}
		$this->view->tabs[] = array(
			'link' => Route::get('forum_event')->uri(array('id' => $event->id)),
			'text' => __('Forum') . ' &raquo;',
		);


		// Share
		Anqh::page_meta('type', 'activity');
		Anqh::page_meta('title', $event->name);
		Anqh::page_meta('url', URL::site(Route::get('event')->uri(array('id' => $event->id, 'action' => '')), true));
		Anqh::page_meta('description', date('l ', $event->stamp_begin) . Date::format(Date::DMY_SHORT, $event->stamp_begin) . ' @ ' . $event->venue_name);
		if ($flyer = $event->flyer()) {
			Anqh::page_meta('image', $flyer->image_url(Model_Image::SIZE_THUMBNAIL));
		}
		Anqh::share(true);

		// Event main info
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_event_main($event));

		// Flyers
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_carousel($event));

		// Event side info
		//$this->view->add(View_Page::COLUMN_SIDE, $this->section_event_info($event));

		// Favorites
		if ($event->favorite_count) {
			$this->view->add(View_Page::COLUMN_RIGHT, $this->section_event_favorites($event));
		}

	}


	/**
	 * Action: add to favorites
	 */
	public function action_favorite() {
		$this->history = false;

		// Load event
		$event_id = (int)$this->request->param('id');
		$event    = Model_Event::factory($event_id);
		if (!$event->loaded()) {
			throw new Model_Exception($event, $event_id);
		}
		Permission::required($event, Model_Event::PERMISSION_FAVORITE);

		if (Security::csrf_valid()) {
			$event->add_favorite(Visitor::$user);

			NewsfeedItem_Events::favorite(Visitor::$user, $event);
		}

		// Ajax requests show event day
		if ($this->_request_type === Controller::REQUEST_AJAX) {
			$this->response->body(new View_Event_Day($event));

			return;
		}

		$this->request->redirect(Route::model($event));
	}


	/**
	 * Action: flyer
	 */
	public function action_flyer() {
		$this->history = false;

		// Load event
		/** @var  Model_Event  $event */
		$event_id = (int)$this->request->param('id');
		$event    = Model_Event::factory($event_id);
		if (!$event->loaded()) {
			throw new Model_Exception($event, $event_id);
		}
		Permission::required($event, Model_Event::PERMISSION_UPDATE);

		if ($this->_request_type !== Controller::REQUEST_AJAX) {
			$this->page_title = HTML::chars($event->name);
		}

		if (isset($_REQUEST['default'])) {

			// Change front flyer
			/** @var  Model_Flyer  $flyer */
			$flyer = new Model_Flyer((int)$_REQUEST['default']);
			if (Security::csrf_valid() && $flyer->loaded() && $flyer->event_id == $event->id) {
				if ($event->set_flyer($flyer)) {
					$event->save();
				}
			}
			$cancel = true;

		} else if (isset($_REQUEST['delete'])) {

			// Delete existing
			/** @var  Model_Flyer  $flyer */
			$flyer_id = (int)$_REQUEST['delete'];
			$flyer    = new Model_Flyer($flyer_id);
			if (Security::csrf_valid() && $flyer->loaded() && $flyer->event_id == $event->id) {
				$flyer->delete();

				// Set new default?
				if ($flyer_id == $event->flyer_id) {
					$event->flyer_id = null;
					if ($event->set_flyer()) {
						$event->save();
					}
				}

			}
			$cancel = true;

		}

		// Cancel change
		if (isset($cancel) || isset($_REQUEST['cancel'])) {
			if ($this->_request_type === Controller::REQUEST_AJAX) {
				$this->response->body($this->section_carousel($event));

				return;
			}

			$this->request->redirect(Route::model($event));
		}

		// Handle post
		$errors = array();
		if ($_POST && $_FILES) {
			$image = Model_Image::factory();
			$image->author_id   = Visitor::$user->id;
			$image->created     = time();
			$image->file        = Arr::get($_FILES, 'file');
			$image->description = $event->get_forum_topic();
			try {
				$image->save();

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

					// Set as default?
					if (!$event->flyer_id && $event->set_flyer($flyer)) {
						$event->save();
					}
				} catch (Kohana_Exception $e) {}

				if ($this->_request_type === Controller::REQUEST_AJAX) {
					$this->response->body($this->section_carousel($event));

					return;
				}

				$this->request->redirect(Route::model($event));

			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			} catch (Kohana_Exception $e) {
				$errors = array('file' => __('Failed with image'));
			}
		}

		$view = $this->section_flyer_upload(
			Route::model($event, 'flyer'),
			$this->_request_type === Controller::REQUEST_AJAX ? Route::model($event, 'flyer') . '?cancel' : Route::model($event),
			$errors
		);
		if ($this->_request_type === Controller::REQUEST_AJAX) {
			$this->response->body($view);

			return;
		}

		// Build page
		$this->view = View_Page::factory($event->name);

		$this->view->add(View_Page::COLUMN_CENTER, $view);
	}


	/**
	 * Action: hover card
	 */
	public function action_hover() {
		$this->history = false;

		// Hover card works only with ajax
		if ($this->_request_type !== Controller::REQUEST_AJAX) {
			$this->action_event();

			return;
		}

		$event = Model_Event::factory((int)$this->request->param('id'));
		if ($event->loaded()) {
			$this->response->body(new View_Event_HoverCard($event));
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

		// Initial view shows todays events and current week
		if (!$day && !$month && !$week) {

			// Create todays events
//			$this->stamp_begin = strtotime('today');
//			$this->stamp_end   = strtotime('tomorrow -1 second');
//			if ($events = $this->_events()) {
//				$section_today = $this->sections_events($events);
//			}

			$week = date('W');
		}

		$year  = $year ? $year : date('Y');
		$month = $month ? $month : date('n');
		if ($week) {

			// Show week
			$this->stamp_begin    = strtotime($year . '-W' . Num::pad($week));
			$this->stamp_end      = strtotime('next week', $this->stamp_begin);
			$this->stamp_next     = $this->stamp_end;
			$this->stamp_previous = strtotime('last week', $this->stamp_begin);
			$section_pagination   = $this->section_pagination('week');
			$this->view->title    = __('Events') . ' ' . Date::format(Date::DM_SHORT, $this->stamp_begin) . ' - ' . Date::format(Date::DMY_SHORT, $this->stamp_end);

		} else if ($day) {

			// Show day
			$this->stamp_begin    = mktime(0, 0, 0, $month, $day, $year);
			$this->stamp_end      = strtotime('tomorrow -1 second', $this->stamp_begin);
			$this->stamp_next     = $this->stamp_end;
			$this->stamp_previous = strtotime('yesterday', $this->stamp_begin);
			$section_pagination   = $this->section_pagination('day');
			$this->view->title    = __('Events') . ' ' . Date::format(Date::DMY_SHORT, $this->stamp_begin);

		} else {

			// Show month
			$this->stamp_begin    = mktime(0, 0, 0, $month, 1, $year);
			$this->stamp_end      = strtotime('next month', $this->stamp_begin);
			$this->stamp_next     = $this->stamp_end;
			$this->stamp_previous = strtotime('last month', $this->stamp_begin);
			$section_pagination   = $this->section_pagination('month');
			$this->view->title    = __('Events') . ' ' . Date::format(Date::MY_LONG, $this->stamp_begin);

		}
		$events = $this->_events();



		// Today
		if (isset($section_today)) {
			$this->view->add(View_Page::COLUMN_CENTER, $section_today);
		}

		// Filters
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_filters($events));

		// Pagination
		$this->view->add(View_Page::COLUMN_CENTER, $section_pagination);

		// Event list
		$this->view->add(View_Page::COLUMN_CENTER, $this->sections_events($events));

		// Pagination
		$this->view->add(View_Page::COLUMN_CENTER, $section_pagination);

		// Calendar
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_calendar());

		// Hot events
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_events_hot());

		// New events
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_events_new());

		// Updated events
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_events_updated());

		// Set actions
		if (Permission::has(new Model_Event, Model_Event::PERMISSION_CREATE)) {
			$this->view->actions[] = array(
				'link'  => Route::get('events')->uri(array('action' => 'add')),
				'text'  => '<i class="fa fa-plus-circle"></i> ' . __('Create event'),
				'class' => 'btn-primary',
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
		Permission::required($event, Model_Event::PERMISSION_FAVORITE);

		if (Security::csrf_valid()) {
			$event->delete_favorite(Visitor::$user);
		}

		// Ajax requests show event day
		if ($this->_request_type === Controller::REQUEST_AJAX) {
			$this->response->body(new View_Event_Day($event));

			return;
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
			Permission::required($event, Model_Event::PERMISSION_UPDATE);
			$cancel = Request::back(Route::model($event), true);

			$this->view = View_Page::factory(HTML::chars($event->name));

			// Set actions
			if (Permission::has($event, Model_Event::PERMISSION_DELETE)) {
				$this->view->actions[] = array(
					'link'  => Route::model($event, 'delete') . '?token=' . Security::csrf(),
					'text'  => '<i class="fa fa-trash-o"></i> ' . __('Delete event'),
					'class' => 'btn-danger event-delete'
				);
			}
			$edit = true;
			$event->update_count++;
			$event->modified = time();

		} else {

			// Creating new
			$event = new Model_Event();
			Permission::required($event, Model_Event::PERMISSION_CREATE);
			$cancel = Request::back(Route::get('events')->uri(), true);

			$this->view = View_Page::factory(__('New event'));

			$event->author_id = Visitor::$user->id;
			$event->created   = time();
			$edit = false;

		}

		// Handle post
		if ($_POST && Security::csrf_valid()) {
			$preview = isset($_POST['preview']);

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
			if (isset($post['stamp_begin']['date']) && isset($post['stamp_end']['time']) && !isset($post['stamp_end']['date'])) {
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
				$venue->name          = Arr::get($_POST, 'venue_name');
				$venue->address       = Arr::get($_POST, 'address');
				$venue->latitude      = Arr::get($_POST, 'latitude');
				$venue->longitude     = Arr::get($_POST, 'longitude');
				$venue->foursquare_id = Arr::get($_POST, 'foursquare_id');
				$venue->event_host    = true;
				$venue->author_id     = Visitor::$user->id;
				$venue->city_name     = $event->city_name;

				if (!$preview) {
					try {
						$venue->save();
						$event->venue_id = $venue->id;
					} catch (Validation_Exception $venue_validation) {}
				}

			}

			// Validate event
			try {
				$event->is_valid();
			} catch (Validation_Exception $event_validation) {}

			// Handle preview request
			if ($preview) {
				if ($this->ajax) {
					$preview  = '<p>' . self::_event_subtitle($event) . '</p>';
					$preview .= '<div id="main" class="col-md-8">';
					$preview .= $this->section_event_main($event);
					$preview .= '<hr></div>';

					$this->response->body($preview);
				}

				return;
			}

			// Flyer
			if ($flyer_url = Arr::get($_POST, 'flyer')) {
				$event->flyer_url = $flyer_url;

				$image = new Model_Image();
				$image->remote    = $flyer_url;
				$image->created   = time();
				$image->author_id = Visitor::$user->id;

				try {
					$image->save();

					try {
						$flyer = new Model_Flyer();
						$flyer->set_fields(array(
							'image_id'    => $image->id,
							'name'        => $event->name,
							'stamp_begin' => $event->stamp_begin,
						));
						$flyer->save();
					} catch (Validation_Exception $flyer_validation) {
						$flyer_error = print_r($flyer_validation->array->errors('validation'), true);
					}
				} catch (Validation_Exception $image_validation) {
					$flyer_error = print_r($image_validation->array->errors('validation'), true);
				} catch (Kohana_Exception $e) {
					$flyer_error = $e->getMessage();
				}
			}


			// If no errors found, save
			if (!isset($venue_validation) && !isset($event_validation) && !isset($flyer_error)) {

				// Make sure end time is after start time, i.e. the next day
				if ($event->stamp_end < $event->stamp_begin) {
					$event->stamp_end += Date::DAY;
				}

				$event->save();

				// Handle flyer
				if (isset($image) && isset($flyer) && $flyer->loaded()) {
					$flyer->event_id = $event->id;
					$flyer->save();

					$event->set_flyer($flyer);
					$event->save();
				}

				// Set tags
				$event->set_tags(Arr::get($_POST, 'tag'));

				if ($edit) {

					// Don't flood edits right after save
					if (time() - $event->created > 60 * 30) {
						NewsfeedItem_Events::event_edit(Visitor::$user, $event);
					}

				} else {
					NewsfeedItem_Events::event(Visitor::$user, $event);

					// Add to favorites
					$event->add_favorite(Visitor::$user);

					// Create forum topic
					if ($event->add_forum_topic()) {
						Visitor::$user->post_count++;
						Visitor::$user->save();
					}
				}

				$this->request->redirect(Route::model($event));
			}
		}

		// Remove orphan flyer on all errors
		if (isset($flyer)) {
			$flyer->delete();
		} else if (isset($image)) {
			$image->delete();
		}

		// Tags
		$tags = array();
		$tag_group = new Model_Tag_Group('Music');
		if ($tag_group->loaded() && count($tag_group->tags())) {
			foreach ($tag_group->tags() as $tag) {
				$tags[$tag->id()] = $tag->name();
			}
		}

		// Form
		$section = $this->section_event_edit($event);
		$section->event_errors = isset($event_validation) ? $event_validation->array->errors('validation') : null;
		$section->flyer_error  = isset($flyer_error) ? $flyer_error : null;
		$section->venue        = isset($venue) ? $venue : $event->venue;
		$section->venue_errors = isset($venue_validation) ? $venue_validation->array->errors('validation') : null;
		$section->cancel       = $cancel;
		$this->view->add(View_Page::COLUMN_TOP, $section);
	}


	/**
	 * Add event subtitle.
	 *
	 * @param   Model_Event  $event
	 * @return  string
	 */
	public static function _event_subtitle(Model_Event $event) {
		$subtitle = array();

		// Date
		if ($event->stamp_end - $event->stamp_begin > Date::DAY) {

			// Multi day event
			$subtitle[] = '<i class="fa fa-calendar"></i> ' . HTML::time(Date('l', $event->stamp_begin) . ', <strong>' . Date::format(Date::DM_LONG, $event->stamp_begin) . ' &ndash; ' . Date::format(Date::DMY_LONG, $event->stamp_end) . '</strong>', $event->stamp_begin, true);

		} else {

			// Single day event
			$subtitle[] = '<i class="fa fa-calendar"></i> ' . HTML::time(Date('l', $event->stamp_begin) . ', <strong>' . Date::format(Date::DMY_LONG, $event->stamp_begin) . '</strong>', $event->stamp_begin, true);

		}

		// Time
		if ($event->stamp_begin != $event->stamp_end) {
			$subtitle[] = $event->stamp_end ?
				'<i class="fa fa-clock-o"></i> ' . __('From :from until :to', array(
					':from' => '<strong>' . HTML::time(Date::format('HHMM', $event->stamp_begin), $event->stamp_begin) . '</strong>',
					':to'   => '<strong>' . HTML::time(Date::format('HHMM', $event->stamp_end), $event->stamp_end) . '</strong>'
				)) :
				'<i class="fa fa-clock-o"></i> ' . __('From :from onwards', array(
					':from' => HTML::time(Date::format('HHMM', $event->stamp_begin), $event->stamp_begin),
				));
		}

		// Tickets
		$tickets = '';
		if ($event->price === 0 || $event->price > 0 || $event->ticket_url) {
			$tickets = '<i class="fa fa-ticket"></i> ';
		}
		if ($event->price === 0) {
			$tickets .= '<strong>' . __('Free entry') . '</strong> ';
		} else if ($event->price > 0) {
			$tickets .= __('Tickets :price', array(':price' => '<strong>' . Num::currency($event->price, $event->stamp_begin) . '</strong>')) . ' ';
		}
		if ($event->ticket_url) {
			$tickets .= HTML::anchor($event->ticket_url, __('Buy tickets'), array('target' => '_blank'));
		}
		if ($tickets) {
			$subtitle[] = $tickets;
		}

		// Age limit
		if ($event->age > 0) {
			$subtitle[] = '<i class="fa fa-user"></i> ' . __('Age limit') . ': <strong>' . $event->age . '</strong>';
		}

		// Homepage
		if (!empty($event->url)) {
			$subtitle[] = '<i class="fa fa-link"></i> ' . HTML::anchor($event->url, Text::limit_url($event->url, 25));
		}

		// Venue
		if ($_venue = $event->venue()) {

			// Venue found from db
			$venue   = HTML::anchor(Route::model($_venue), HTML::chars($_venue->name));
			$address = HTML::chars($_venue->city_name);

			if ($_venue->latitude) {
				$map = array(
					'marker'     => HTML::chars($_venue->name),
					'infowindow' => HTML::chars($_venue->address) . '<br />' . HTML::chars($_venue->city_name),
					'lat'        => $_venue->latitude,
					'long'       => $_venue->longitude
				);
				Widget::add('foot', HTML::script_source('
head.ready("anqh", function() {
	$("a[href=#map]").on("click", function toggleMap(event) {
		$("#map").toggle("fast", function openMap() {
			$("#map").googleMap(' .  json_encode($map) . ');
		});

		return false;
	});
});
'));
			}

		} else if ($event->venue_name) {

			// No venue in db
			$venue   = $event->venue_url
				? HTML::anchor($event->venue_url, HTML::chars($event->venue_name))
				: HTML::chars($event->venue_name);
			$address = HTML::chars($event->city_name);

		} else {

			// Venue not set
			$venue   = $event->venue_hidden ? __('Underground') : __('(Unknown)');
			$address = HTML::chars($event->city_name);

		}
		$subtitle[] = '<br /><i class="fa fa-map-marker"></i> <strong>' . $venue . '</strong>' . ($address ? ', ' . $address : '');
		if (isset($map)) {
			$subtitle[] = HTML::anchor('#map', __('Show map'));
		}

		// Tags
		if ($tags = $event->tags()) {
			$subtitle[] = '<br /><i class="fa fa-music"></i> <em>' . implode(', ', $tags) . '</em>';
		} else if (!empty($event->music)) {
			$subtitle[] = '<br /><i class="fa fa-music"></i> <em>' . $event->music . '</em>';
		}

		return implode(' &nbsp; ', $subtitle)
			. (isset($map) ? '<div id="map" style="display: none">' . __('Map loading') . '</div>' : '');
	}


	/**
	 * Load events.
	 *
	 * @return  array
	 */
	private function _events() {
		return Model_Event::factory()->find_grouped_between($this->stamp_begin, $this->stamp_end, 'ASC');
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
	 * Get image slideshow.
	 *
	 * @param   Model_Event  $event
	 * @return  View_Event_Carousel
	 */
	public function section_carousel(Model_Event $event) {
		return new View_Event_Carousel($event);
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
		$section->title = __('Favorites') . ' <small><i class="fa fa-heart"></i> ' . count($favorites) . '</small>';

		return $section;
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
		$section->aside  = true;
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
		$section->aside  = true;
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
		$section->aside  = true;
		$section->title  = __('Updated events');
		$section->events = Model_Event::factory()->find_modified(10);

		return $section;
	}


	/**
	 * Get filters.
	 *
	 * @param   array  $events
	 * @return  View_Generic_Filters
	 */
	public function section_filters(array $events = null) {
		$section = new View_Generic_Filters();
		$section->filters = $this->_filters($events);

		return $section;
	}


	/**
	 * Get flyer upload.
	 *
	 * @param   string  $action
	 * @param   string  $cancel  URL
	 * @param   array   $errors
	 * @return  View_Generic_Upload
	 */
	public function section_flyer_upload($action = null, $cancel = null, $errors = null) {
		$section = new View_Generic_Upload();
		$section->title  = __('Upload flyer');
		$section->action = $action;
		$section->cancel = $cancel;
		$section->errors = $errors;

		return $section;
	}


	/**
	 * Get pagination.
	 *
	 * @param   string  $period  day|week|month
	 * @return  View_Generic_Pagination
	 */
	public function section_pagination($period = 'week') {
		switch ($period) {

			case 'day':
				return new View_Generic_Pagination(array(
				  'current_page'  => __('Show week'),
					'current_url'   => Route::url('events_yw', array(
							'year' => date('Y', strtotime('this Thursday', $this->stamp_begin)),
							'week' => date('W', $this->stamp_begin))),
					'previous_text' => '&lsaquo; ' . __('Previous day'),
					'next_text'     => __('Next day') . ' &rsaquo;',
					'previous_url'  => Route::url('events_ymd', array(
							'year'  => date('Y', $this->stamp_previous),
							'month' => date('m', $this->stamp_previous),
							'day'   => date('d', $this->stamp_previous))),
					'next_url'      => Route::url('events_ymd', array(
							'year'  => date('Y', $this->stamp_next),
							'month' => date('m', $this->stamp_next),
							'day'   => date('d', $this->stamp_next))),
				));

			case 'week':
				return new View_Generic_Pagination(array(
//				  'current_page'  => Date::format(Date::DM_SHORT, $this->stamp_begin) . ' &ndash; ' . Date::format(Date::DMY_SHORT, $this->stamp_end),
					'previous_text' => '&lsaquo; ' . __('Previous week'),
					'next_text'     => __('Next week') . ' &rsaquo;',
					'previous_url'  => Route::url('events_yw', array(
							'year' => date('Y', strtotime('this Thursday', $this->stamp_previous)),
							'week' => date('W', $this->stamp_previous))),
					'next_url'      => Route::url('events_yw', array(
							'year' => date('Y', strtotime('this Thursday', $this->stamp_next)),
							'week' => date('W', $this->stamp_next))),
				));

			case 'month':
				return new View_Generic_Pagination(array(
//				  'current_page'  => Date::format(Date::DM_SHORT, $this->stamp_begin) . ' &ndash; ' . Date::format(Date::DMY_SHORT, $this->stamp_end),
					'previous_text' => '&lsaquo; ' . __('Previous month'),
					'next_text'     => __('Next month') . ' &rsaquo;',
					'previous_url'  => Route::url('events_ymd', array(
							'year'  => date('Y', $this->stamp_previous),
							'month' => date('m', $this->stamp_previous))),
					'next_url'      => Route::url('events_ymd', array(
							'year'  => date('Y', $this->stamp_next),
							'month' => date('m', $this->stamp_next))),
				));

		}
	}


	/**
	 * Get events.
	 *
	 * @param   array  $events
	 * @return  View_Events_Day[]
	 */
	public function sections_events(array $events = null) {
		if (!$events) {
			return new View_Alert(__('There be no events for selected period, period.'), null, View_Alert::INFO);
		}

		$days = array();
		foreach ($events as $date => $cities) {
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
