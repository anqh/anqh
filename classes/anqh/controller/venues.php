<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Venues controller
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2012 Antti Qvickström
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
		if (!$this->request->param('id') && $this->ajax) {
			return $this->_edit_venue_dialog();
		} else {
			return $this->_edit_venue();
		}
	}


	/**
	 * Action: combine
	 */
	public function action_combine() {
		$this->history = false;

		// Load original venue
		$venue_id = (int)$this->request->param('id');
		$venue = Model_Venue::factory($venue_id);
		if (!$venue->loaded()) {
			throw new Model_Exception($venue, $venue_id);
		}

		// Load duplicate venue
		$duplicate_id = (int)$this->request->param('param');
		$duplicate = Model_Venue::factory($duplicate_id);
		if (!$duplicate->loaded()) {
			throw new Model_Exception($duplicate, $duplicate_id);
		}

		Permission::required($venue, Model_Venue::PERMISSION_COMBINE, self::$user);

		if (Security::csrf_valid()) {

			// Update events
			Model_Event::merge_venues($venue_id, $duplicate_id);

			// Remove duplicate
			$duplicate->delete();

		}

		$this->request->redirect(Route::model($venue));
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

		Permission::required($venue, Model_Venue::PERMISSION_DELETE, self::$user);

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

		Permission::required($venue, Model_Venue::PERMISSION_UPDATE, self::$user);

		if (Security::csrf_valid() && isset($_POST['foursquare_id'])) {
			try {
				$venue->set_fields(Arr::intersect($_POST, array(
					'foursquare_id', 'foursquare_category_id', 'latitude', 'longitude', 'city_id', 'address'
				)));
				$venue->save();

				NewsfeedItem_Venues::venue_edit(self::$user, $venue);
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
		Permission::required($venue, Model_Venue::PERMISSION_UPDATE, self::$user);

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
			$image->author_id = self::$user->id;
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
		$this->view->add(View_Page::COLUMN_MAIN, $view);
	}


	/**
	 * Controller default action
	 */
	public function action_index() {

		// Set actions
		if (Permission::has(new Model_Venue, Model_Venue::PERMISSION_CREATE, self::$user)) {
			$this->page_actions[] = array(
				'link'  => Route::get('venue_add')->uri(),
				'text'  => '<i class="icon-plus-sign icon-white"></i> ' . __('Add venue'),
				'class' => 'btn btn-primary venue-add'
			);
		}


		// Build page
		$this->view = new View_Page(__('Venues'));

		$this->view->add(View_Page::COLUMN_MAIN, $this->section_venues());

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
		$this->view = new View_Page($venue->name);
		$this->page_actions[] = array(
			'link'  => Route::url('venues'),
			'text'  => '&laquo; ' . __('Back to Venues'),
			'class' => 'btn'
		);

		// Set actions
		if (Permission::has($venue, Model_Venue::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array(
				'link'  => Route::model($venue, 'edit'),
				'text'  => '<i class="icon-edit"></i> ' . __('Edit venue'),
				'class' => 'btn venue-edit');
		}

		// Events
		$has_events = false;

		$events = $venue->find_events_upcoming(10);
		if (count($events)) {
			$has_events = true;
			$section = $this->section_events_list($events);
			$section->title = __('Upcoming events');
			$this->view->add(View_Page::COLUMN_MAIN, $section);
		}

		$events = $venue->find_events_past(10);
		if (count($events)) {
			$has_events = true;
			$section = $this->section_events_list($events);
			$section->title = __('Past events');
			$this->view->add(View_Page::COLUMN_MAIN, $section);
		}

		if (!$has_events) {
			$this->view->add(View_Page::COLUMN_MAIN, new View_Alert(__('Nothing has happened here yet.'), null, View_Alert::INFO));
		}

		// Similar venues
		/* @todo  Better UI
		$similar = Model_Venue::factory()->find_by_name($venue->name);
		if (count($similar) > 1) {
			Widget::add('main', View_Module::factory('venues/similar', array(
				'mod_title' => __('Similar venues'),
				'venue'     => $venue,
				'venues'    => $similar,
				'admin'     => Permission::has($venue, Model_Venue::PERMISSION_COMBINE, self::$user)
			)));
		}
		*/

		// Slideshow
		if (count($venue->images) > 1) {
			$images = array();
			foreach ($venue->images as $image) $images[] = $image;
			Widget::add('side', View_Module::factory('generic/image_slideshow', array(
				'images'     => array_reverse($images),
				'default_id' => $venue->default_image->id,
			)));
		}

		// Default image
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_venue_image($venue));

		// Venue info
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_venue_info($venue));

		/* @todo Needs a decent OAuth2 module
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_venue_foursquare($venue));
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
			Permission::required($venue, Model_Venue::PERMISSION_UPDATE, self::$user);
			$cancel = Route::model($venue);

			$this->view = View_Page::factory($venue->name);

			// Set actions
			if (Permission::has($venue, Model_Venue::PERMISSION_DELETE, self::$user)) {
				$this->page_actions[] = array(
					'link'  => Route::model($venue, 'delete') . '?' . Security::csrf_query(),
					'text'  => '<i class="icon-trash icon-white"></i> ' . __('Delete venue'),
					'class' => 'btn btn-danger venue-delete'
				);
			}

		} else {

			// Creating new
			$edit = false;
			$venue = Model_Venue::factory();
			$venue->author_id = self::$user->id;
			$cancel = Route::url('venues');

			$this->view = View_Page::factory(__('New venue'));

		}

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			$venue->set_fields(Arr::intersect($_POST, Model_Venue::$editable_fields));

			try {
				$venue->save();

				$edit ? NewsfeedItem_Venues::venue_edit(self::$user, $venue) : NewsfeedItem_Venues::venue(self::$user, $venue);

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
	 * Edit venue data in dialog
	 */
	protected function _edit_venue_dialog() {
		echo View_Module::factory('venues/edit_dialog', array(

		));
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

		if (Permission::has($venue, Model_Venue::PERMISSION_UPDATE, self::$user)) {
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
		return new View_Venues_List($venues);
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
		$this->view->add(View_Page::COLUMN_SIDE, $section);

		// Updated venues
		$section = $this->section_venue_list(Model_Venue::factory()->find_updated(10));
		$section->title = __('Updated venues');
		$this->view->add(View_Page::COLUMN_SIDE, $section);

	}


	/**
	 * New and updated venues
	 */
	protected function _tabs() {
		$tabs = array(
			'new' => array('href' => '#venues-new', 'title' => __('New venues'), 'tab' => View_Module::factory('venues/list', array(
				'mod_id'    => 'venues-new',
				'mod_class' => 'cut tab venues',
				'title'     => __('New Venues'),
				'venues'    => Model_Venue::factory()->find_new(20),
			))),
			'updated' => array('href' => '#venues-updated', 'title' => __('Updated venues'), 'tab' => View_Module::factory('venues/list', array(
				'mod_id'    => 'venues-updated',
				'mod_class' => 'cut tab venues',
				'title'     => __('Updated Venues'),
				'venues'    => Model_Venue::factory()->find_updated(20),
			))),
		);

		Widget::add('side', View::factory('generic/tabs_side', array('id' => 'venues-tab', 'tabs' => $tabs)));
	}


}
