<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Venues controller
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Venues extends Controller_Page {

	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->page_title = __('Venues');
	}


	/**
	 * Action: add venue
	 */
	public function action_add() {
		return $this->_edit_venue();
	}


	/**
	 * Action: combine
	 */
	public function action_combine() {
		$this->history = false;

		// Load original venue
		$venue_id = (int)$this->request->param('id');
		$venue    = Model_Venue::factory($venue_id);
		if (!$venue->loaded()) {
			throw new Model_Exception($venue, $venue_id);
		}

		Permission::required($venue, Model_Venue::PERMISSION_COMBINE);

		// Build page
		$this->view         = new View_Page($venue->name);
		$this->view->tab    = 'venue';
		$this->view->tabs[] = array(
			'link'  => Route::url('venues'),
			'text'  => '&laquo; ' . __('Back to Venues'),
		);
		$this->view->tabs['venue'] = array(
			'link'  => Route::model($venue),
			'text'  => __('Venue'),
		);

		// Load duplicate venue
		$duplicate_id = (int)$this->request->param('param');
		if ($duplicate_id) {
			$duplicate = Model_Venue::factory($duplicate_id);
			if (!$duplicate->loaded()) {
				throw new Model_Exception($duplicate, $duplicate_id);
			}

			if (Security::csrf_valid()) {

				// Combine

				// Update events
				Model_Event::merge_venues($venue_id, $duplicate_id);

				// Copy info from duplicate
				$new_data = false;
				foreach (array(
					'description', 'url', 'hours', 'info', 'address', 'zip', 'city_name',
					'latitude', 'longitude', 'foursquare_id', 'foursquare_category_id'
				) as $data) {
					if ($duplicate[$data] && !$venue[$data]) {
						$venue[$data] = $duplicate[$data];
						$new_data     = true;
					}
				}
				if ($new_data) {
					$venue->save();
				}

				// Remove duplicate
				$duplicate->delete();

				$this->request->redirect(Route::model($venue));

			} else {

				// Confirm
				$this->view->add(View_Page::COLUMN_CENTER, $this->section_venue_combine($venue, $duplicate));

			}

		} else {

			// Select parent
			$this->view->add(View_Page::COLUMN_CENTER, $this->section_venue_combine($venue));

		}
	}


	/**
	 * Action: delete venue
	 */
	public function action_delete() {
		$this->history = false;

		// Load venue
		$venue_id = (int)$this->request->param('id');
		$venue = Model_Venue::factory($venue_id);
		if (!$venue->loaded()) {
			throw new Model_Exception($venue, $venue_id);
		}

		Permission::required($venue, Model_Venue::PERMISSION_DELETE);

		if (!Security::csrf_valid()) {
			$this->request->redirect(Route::model($venue));
		}

		$venue->delete();

		$this->request->redirect(Route::get('venues')->uri());
	}


	/**
	 * Action: edit venue
	 */
	public function action_edit() {
		$this->_edit_venue((int)$this->request->param('id'));
	}


	/**
	 * Action: foursquare
	 */
	public function action_foursquare() {
		$this->history = false;

		// Load venue
		$venue_id = (int)$this->request->param('id');
		$venue = Model_Venue::factory($venue_id);
		if (!$venue->loaded()) {
			throw new Model_Exception($venue, $venue_id);
		}

		Permission::required($venue, Model_Venue::PERMISSION_UPDATE);

		if (Security::csrf_valid() && isset($_POST['foursquare_id'])) {
			try {
				$venue->set_fields(Arr::intersect($_POST, array(
					'foursquare_id', 'foursquare_category_id', 'latitude', 'longitude', 'address'
				)));
				$venue->save();

				NewsfeedItem_Venues::venue_edit(Visitor::$user, $venue);
			} catch (Validation_Exception $e) {

			}
		}

		$this->request->redirect(Route::model($venue));
	}


	/**
	 * Action: image
	 */
	public function action_image() {
		$this->history = false;

		// Load venue
		$venue_id = (int)$this->request->param('id');
		$venue    = Model_Venue::factory($venue_id);
		if (!$venue->loaded()) {
			throw new Model_Exception($venue, $venue_id);
		}
		Permission::required($venue, Model_Venue::PERMISSION_UPDATE);

		// Change existing
		if ($image_id = (int)Arr::get($_REQUEST, 'default')) {
			$image = Model_Image::factory($image_id);
			if (Security::csrf_valid() && $image->loaded() && $venue->has('images', $image->id)) {
				$venue->default_image_id = $image->id;
				$venue->save();
			}
			$cancel = true;
		}

		// Delete existing
		if ($image_id = (int)Arr::get($_REQUEST, 'delete')) {
			$image = Model_Image::factory($image_id);
			if (Security::csrf_valid() && $image->loaded() && $venue->has('images', $image->id)) {
				if ($venue->default_image_id == $image->id) {
					$venue->default_image_id = null;
				}
				$venue->remove('image', $image->id);
				$venue->save();
				$image->delete();
			}
			$cancel = true;
		}

		// Cancel change
		if (isset($cancel) || isset($_REQUEST['cancel'])) {
			if ($this->_request_type === Controller::REQUEST_AJAX) {
				$this->response->body($this->section_venue_image($venue));

				return;
			}

			$this->request->redirect(Route::model($venue));
		}

		// Handle post
		$errors = array();
		if ($_POST && $_FILES && Security::csrf_valid()) {
			$image = new Model_Image();
			$image->author_id = Visitor::$user->id;
			$image->file      = Arr::get($_FILES, 'file');
			try {
				$image->save();

				// Add exif, silently continue if failed - not critical
				try {
					$exif = new Model_Image_Exif();
					$exif->image_id = $image->id;
					$exif->save();
				} catch (Kohana_Exception $e) { }

				// Set the image as venue image
				$venue->relate('images', array($image->id));
				$venue->default_image_id = $image->id;
				$venue->save();

				if ($this->_request_type === Controller::REQUEST_AJAX) {
					$this->response->body($this->section_venue_image($venue));

					return;
				}

				$this->request->redirect(Route::model($venue));

			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			} catch (Kohana_Exception $e) {
				$errors = array('file' => __('Failed with image'));
			}
		}

		$view = $this->section_image_upload($this->_request_type === Controller::REQUEST_AJAX ? Route::model($venue, 'image') . '?cancel' : Route::model($venue), $errors);
		if ($this->_request_type === Controller::REQUEST_AJAX) {
			$this->response->body($view);

			return;
		}

		$this->view = new View_Page($venue->name);
		$this->view->add(View_Page::COLUMN_CENTER, $view);
	}


	/**
	 * Controller default action
	 */
	public function action_index() {

		// Build page
		$this->view = new View_Page(__('Venues'));

		// Set actions
		if (Permission::has(new Model_Venue, Model_Venue::PERMISSION_CREATE)) {
			$this->view->actions[] = array(
				'link'  => Route::get('venue_add')->uri(),
				'text'  => '<i class="icon-plus-sign icon-white"></i> ' . __('Add venue'),
				'class' => 'btn btn-primary venue-add'
			);
		}

		$this->view->add(View_Page::COLUMN_CENTER, $this->section_venues());

		$this->_side_views();
	}


	/**
	 * Action: venue
	 */
	public function action_venue() {
		$venue_id =(int)$this->request->param('id');

		// Load venue
		/** @var  Model_Venue  $venue */
		$venue = Model_Venue::factory($venue_id);
		if (!$venue->loaded()) {
			throw new Model_Exception($venue, $venue_id);
		}


		// Build page
		$this->view         = new View_Page($venue->name);
		$this->view->tab    = 'venue';
		$this->view->tabs[] = array(
			'link'  => Route::url('venues'),
			'text'  => '&laquo; ' . __('Back to Venues'),
		);
		$this->view->tabs['venue'] = array(
			'link'  => Route::model($venue),
			'text'  => __('Venue'),
		);

		// Set actions
		if (Permission::has($venue, Model_Venue::PERMISSION_UPDATE)) {
			$this->view->actions[] = array(
				'link'  => Route::model($venue, 'edit'),
				'text'  => '<i class="icon-edit icon-white"></i> ' . __('Edit venue'),
			);
		}
		if (Permission::has($venue, Model_Venue::PERMISSION_COMBINE)) {
			$this->view->actions[] = array(
				'link'  => Route::model($venue, 'combine'),
				'text'  => '<i class="icon-filter icon-white"></i> ' . __('Combine duplicate'),
			);
		}

		// Events
		$has_events = false;

		$events = $venue->find_events_upcoming(25);
		if (count($events)) {
			$has_events = true;
			$section = $this->section_events_list($events);
			$section->title = __('Upcoming events');
			$this->view->add(View_Page::COLUMN_CENTER, $section);
		}

		$events = $venue->find_events_past(10);
		if (count($events)) {
			$has_events = true;
			$section = $this->section_events_list($events);
			$section->title = __('Past events');
			$this->view->add(View_Page::COLUMN_CENTER, $section);
		}

		if (!$has_events) {
			$this->view->add(View_Page::COLUMN_CENTER, new View_Alert(__('Nothing has happened here yet.'), null, View_Alert::INFO));
		}

		// Similar venues
		if (Permission::has($venue, Model_Venue::PERMISSION_COMBINE)) {
			$similar = $venue->find_similar(65);

			if ($similar) {
				$this->view->add(View_Page::COLUMN_CENTER, $this->section_venue_similar($venue, $similar));
			}
		}

		// Default image
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_venue_image($venue));

		// Venue info
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_venue_info($venue));

		/* @todo Needs a decent OAuth2 module
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_venue_foursquare($venue));
		 */

	}


	/**
	 * Edit venue
	 *
	 * @param  integer  $venue_id
	 */
	protected function _edit_venue($venue_id = null) {
		$this->history = false;
		$edit = true;

		if ($venue_id) {

			// Editing old
			$venue = Model_Venue::factory($venue_id);
			if (!$venue->loaded()) {
				throw new Model_Exception($venue, $venue_id);
			}
			Permission::required($venue, Model_Venue::PERMISSION_UPDATE);
			$cancel = Route::model($venue);

			$this->view = View_Page::factory($venue->name);

			// Modified timestamp
			$venue->modified = time();

			// Set actions
			if (Permission::has($venue, Model_Venue::PERMISSION_DELETE)) {
				$this->view->actions[] = array(
					'link'  => Route::model($venue, 'delete') . '?' . Security::csrf_query(),
					'text'  => '<i class="icon-trash icon-white"></i> ' . __('Delete venue'),
					'class' => 'btn btn-danger venue-delete'
				);
			}

		} else {

			// Creating new
			$edit = false;
			$venue = Model_Venue::factory();
			$venue->author_id = Visitor::$user->id;
			$cancel = Route::url('venues');

			$this->view = View_Page::factory(__('New venue'));

		}

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			$venue->set_fields(Arr::intersect($_POST, Model_Venue::$editable_fields));

			try {
				$venue->save();

				$edit ? NewsfeedItem_Venues::venue_edit(Visitor::$user, $venue) : NewsfeedItem_Venues::venue(Visitor::$user, $venue);

				$this->request->redirect(Route::model($venue));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		$section = $this->section_venue_edit($venue);
		$section->errors = $errors;
		$section->cancel = $cancel;
		$this->view->add(View_Page::COLUMN_TOP, $section);
	}


	/**
	 * Get events.
	 *
	 * @param   Model_Event[]  $events
	 * @return  View_Events_List
	 */
	public function section_events_list($events) {
		return new View_Events_List($events);
	}


	/**
	 * Get image upload.
	 *
	 * @param   string  $cancel  URL
	 * @param   array   $errors
	 * @return  View_Generic_Upload
	 */
	public function section_image_upload($cancel = null, $errors = null) {
		$section = new View_Generic_Upload();
		$section->title  = __('Add image');
		$section->cancel = $cancel;
		$section->errors = $errors;

		return $section;
	}


	/**
	 * Get venue image.
	 *
	 * @param   Model_Venue  $venue
	 * @return  View_Generic_SideImage
	 */
	public function section_venue_image($venue) {
		$section = new View_Generic_SideImage($venue->default_image_id ? Model_Image::factory($venue->default_image_id) : null);

		if (Permission::has($venue, Model_Venue::PERMISSION_UPDATE)) {
			$uri = Route::model($venue, 'image');
			$actions = array(
				HTML::anchor($uri, '<i class="icon-plus-sign icon-white"></i> ' .__('Add image'), array('class' => 'btn btn-mini btn-primary image-add ajaxify')),
			);

			if ($venue->default_image_id) {
				$actions[] = HTML::anchor(
					$uri . '?' . Security::csrf_query() . '&delete=' . $venue->default_image_id,
					'<i class="icon-trash"></i> ' .__('Delete'),
					array('class' => 'btn btn-mini image-delete')
				);
			}


			$section->actions = $actions;
		}

		return $section;
	}


	/**
	 * Get combine duplicate view.
	 *
	 * @param   Model_Venue  $venue
	 * @param   Model_Venue  $duplicate
	 * @return  View_Venue_Combine
	 */
	public function section_venue_combine(Model_Venue $venue, Model_Venue $duplicate = null) {
		return new View_Venue_Combine($venue, $duplicate);
	}


	/**
	 * Get venue edit form.
	 *
	 * @param   Model_Venue  $venue
	 * @return  View_Venue_Edit
	 */
	public function section_venue_edit($venue) {
		return new View_Venue_Edit($venue);
	}


	/**
	 * Get venue Foursquare info.
	 *
	 * @param   Model_Venue  $venue
	 * @return  View_Venue_Foursquare
	 */
	public function section_venue_foursquare($venue) {
		return new View_Venue_Foursquare($venue);
	}


	/**
	 * Get venue info.
	 *
	 * @param   Model_Venue  $venue
	 * @return  View_Venue_Info
	 */
	public function section_venue_info($venue) {
		return new View_Venue_Info($venue);
	}


	/**
	 * Get simple venue list.
	 *
	 * @param  Model_Venue[]  $venues
	 */
	public function section_venue_list($venues) {
		$section = new View_Venues_List($venues);
		$section->aside = true;

		return $section;
	}


	/**
	 * Get similar venues list.
	 *
	 * @param   Model_Venue  $venue
	 * @param   array        $venues
	 * @return  View_Venues_Similar
	 */
	public function section_venue_similar(Model_Venue $venue, array $venues) {
		return new View_Venues_Similar($venue, $venues);
	}


	/**
	 * Get venues listing.
	 *
	 * @return  View_Venues_Index
	 */
	public function section_venues() {
		return new View_Venues_Index(Model_Venue::factory()->find_all());
	}


	/**
	 * Add side views.
	 */
	protected function _side_views() {

		// New venues
		$section = $this->section_venue_list(Model_Venue::factory()->find_new(10));
		$section->title = __('New venues');
		$this->view->add(View_Page::COLUMN_RIGHT, $section);

		// Updated venues
		$section = $this->section_venue_list(Model_Venue::factory()->find_updated(10));
		$section->title = __('Updated venues');
		$this->view->add(View_Page::COLUMN_RIGHT, $section);

	}

}
