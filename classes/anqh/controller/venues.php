<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Venues controller
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Venues extends Controller_Template {

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
			return $this->_edit_venue((int)$this->request->param('id'));
		}
	}


	/**
	 * Action: add category
	 */
	public function action_addcategory() {
		return $this->_edit_category();
	}


	/**
	 * Action: category
	 */
	public function action_category() {
		$category_id = (int)$this->request->param('id');

		$category = Jelly::select('venue_category')->load($category_id);
		if (!$category->loaded()) {
			throw new Model_Exception($category, $category_id);
		}

		// Set actions
		if (Permission::has($category, Model_Venue_Category::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($category, 'editcategory'), 'text' => __('Edit category'), 'class' => 'category-edit');
		}
		if (Permission::has($category, Model_Venue_Category::PERMISSION_VENUE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($category, 'add'), 'text' => __('Add venue'), 'class' => 'venue-add');
		}

		// Set title
		$this->page_title   .= ': ' . HTML::chars($category->name);
		$this->page_subtitle = HTML::chars($category->description);

		Widget::add('main', View_Module::factory('venues/venues', array(
			'mod_class' => 'venues articles',
			'venues'    => $category->find_venues_by_city(),
		)));

		$this->_tabs();
	}


	/**
	 * Action: combine
	 */
	public function action_combine() {
		$this->history = false;

		// Load original venue
		$venue_id = (int)$this->request->param('id');
		$venue = Jelly::select('venue')->load($venue_id);
		if (!$venue->loaded()) {
			throw new Model_Exception($venue, $venue_id);
		}

		// Load duplicate venue
		$duplicate_id = (int)$this->request->param('param');
		$duplicate = Jelly::select('venue')->load($duplicate_id);
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
		$venue = Jelly::select('venue')->load($venue_id);
		if (!$venue->loaded()) {
			throw new Model_Exception($venue, $venue_id);
		}

		Permission::required($venue, Model_Venue::PERMISSION_DELETE, self::$user);

		if (!Security::csrf_valid()) {
			$this->request->redirect(Route::model($venue));
		}

		$category = $venue->category;
		$venue->delete();

		$this->request->redirect(Route::model($category));
	}


	/**
	 * Action: delete category
	 */
	public function action_deletecategory() {
		$this->history = false;

		// Load category
		$category_id = (int)$this->request->param('id');
		$category = Jelly::select('venue_category')->load($category_id);
		if (!$category->loaded()) {
			throw new Model_Exception($category, $category_id);
		}

		Permission::required($category, Model_Venue_Category::PERMISSION_DELETE, self::$user);

		if (!Security::csrf_valid()) {
			$this->request->redirect(Route::model($category));
		}

		$category->delete();

		$this->request->redirect(Route::get('venues')->uri());
	}


	/**
	 * Action: edit venue
	 */
	public function action_edit() {
		$this->_edit_venue(null, (int)$this->request->param('id'));
	}


	/**
	 * Action: edit category
	 */
	public function action_editcategory() {
		$this->_edit_category((int)$this->request->param('id'));
	}


	/**
	 * Action: image
	 */
	public function action_image() {
		$this->history = false;

		// Load venue
		$venue_id = (int)$this->request->param('id');
		$venue = Jelly::select('venue')->load($venue_id);
		if (!$venue->loaded()) {
			throw new Model_Exception($venue, $venue_id);
		}
		Permission::required($venue, Model_Venue::PERMISSION_UPDATE, self::$user);

		if (!$this->ajax) {
			$this->page_title    = HTML::chars($venue->name);
			$this->page_subtitle = __('Category :category', array(
				':category' => HTML::anchor(Route::model($venue->category), $venue->category->name, array('title' => $venue->category->description))
			));
		}

		// Change existing
		if (isset($_REQUEST['default'])) {
			$image = Jelly::select('image')->load((int)$_REQUEST['default']);
			if (Security::csrf_valid() && $image->loaded() && $venue->has('images', $image)) {
				$venue->default_image = $image;
				$venue->save();
			}
			$cancel = true;
		}

		// Delete existing
		if (isset($_REQUEST['delete'])) {
			$image = Jelly::select('image')->load((int)$_REQUEST['delete']);
			if (Security::csrf_valid() && $image->loaded() && $image->id != $venue->default_image->id && $venue->has('images', $image)) {
				$venue->remove('images', $image);
				$venue->save();
				$image->delete();
			}
			$cancel = true;
		}

		// Cancel change
		if (isset($cancel) || isset($_REQUEST['cancel'])) {
			if ($this->ajax) {
				echo $this->_get_mod_image($venue);
				return;
			}

			$this->request->redirect(Route::model($venue));
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

				// Set the image as venue image
				$venue->add('images', $image);
				$venue->default_image = $image;
				$venue->save();

				if ($this->ajax) {
					echo $this->_get_mod_image($venue);
					return;
				}

				$this->request->redirect(Route::model($venue));

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
			'cancel'     => $this->ajax ? Route::model($venue, 'image') . '?cancel' : Route::model($venue),
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

		// Set actions
		if (Permission::has(new Model_Venue_Category, Model_Venue_Category::PERMISSION_CREATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::get('venue_category_add')->uri(), 'text' => __('Add category'), 'class' => 'category-add');
		}

		Widget::add('main', View_Module::factory('venues/cities', array(
			'venues' => Model_Venue::find_all(),
		)));

		$this->_tabs();
	}


	/**
	 * Action: venue
	 */
	public function action_venue() {
		$venue_id =(int)$this->request->param('id');

		// Load venue
		$venue = Jelly::select('venue')->load($venue_id);
		if (!$venue->loaded()) {
			throw new Model_Exception($venue, $venue_id);
		}
		$this->page_title    = HTML::chars($venue->name);
		$this->page_subtitle = __('Category :category', array(
			':category' => HTML::anchor(Route::model($venue->category), $venue->category->name, array('title' => $venue->category->description))
		));

		// Set actions
		if (Permission::has($venue, Model_Venue::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array('link' => Route::model($venue, 'edit'), 'text' => __('Edit venue'), 'class' => 'venue-edit');
		}

		// Events
		$events = $venue->get('events')->upcoming()->limit(10)->execute();
		if (count($events)) {
			Widget::add('main', View_Module::factory('events/event_list', array(
				'mod_id'    => 'venue-upcoming-events',
				'mod_title' => __('Upcoming events'),
				'events'    => $events,
			)));
		}

		$events = $venue->get('events')->past()->limit(10)->execute();
		if (count($events)) {
			Widget::add('main', View_Module::factory('events/event_list', array(
				'mod_id'    => 'venue-past-events',
				'mod_title' => __('Past events'),
				'events'    => $events,
			)));
		}

		// Similar venues
		$similar = Model_Venue::find_by_name($venue->name);
		if (count($similar) > 1) {
			Widget::add('main', View_Module::factory('venues/similar', array(
				'mod_title' => __('Similar venues'),
				'venue'     => $venue,
				'venues'    => $similar,
				'admin'     => Permission::has(new Model_Venue, Model_Venue::PERMISSION_COMBINE, self::$user)
			)));
		}

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
		Widget::add('side', $this->_get_mod_image($venue));

		// Venue info
		Widget::add('side', View_Module::factory('venues/info', array(
			'venue' => $venue,
		)));
	}


	/**
	 * Edit category
	 *
	 * @param  integer  $category_id
	 */
	protected function _edit_category($category_id = null) {
		$this->history = false;

		if ($category_id) {

			// Editing old
			$category = Jelly::select('venue_category')->load($category_id);
			if (!$category->loaded()) {
				throw new Model_Exception($category, $category_id);
			}
			Permission::required($category, Model_Venue_Category::PERMISSION_UPDATE, self::$user);
			$cancel = Route::model($category);

			// Set actions
			if (Permission::has($category, Model_Forum_Topic::PERMISSION_DELETE, self::$user)) {
				$this->page_actions[] = array('link' => Route::model($category, 'deletecategory'), 'text' => __('Delete category'), 'class' => 'category-delete');
			}

		} else {

			// Creating new
			$category = Jelly::factory('venue_category');
			Permission::required($category, Model_Venue_Category::PERMISSION_CREATE, self::$user);
			$cancel = Route::get('venues')->uri();

		}

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			$category->set($_POST);
			try {
				$category->save();
				$this->request->redirect(Route::model($category));
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		// Build form
		$form = array(
			'values' => $category,
			'errors' => $errors,
			'cancel' => $cancel,
			'groups' => array(
				array(
					'fields' => array(
						'name'        => array(),
						'description' => array(),
						'tag_group'   => array(),
					),
				),
			)
		);

		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}


	/**
	 * Edit venue
	 *
	 * @param  integer  $category_id
	 * @param  integer  $venue_id
	 */
	protected function _edit_venue($category_id = null, $venue_id = null) {
		$this->history = false;

		if ($venue_id) {

			// Editing old
			$venue = Jelly::select('venue')->load($venue_id);
			if (!$venue->loaded()) {
				throw new Model_Exception($venue, $venue_id);
			}
			Permission::required($venue, Model_Venue::PERMISSION_UPDATE, self::$user);
			$cancel = Route::model($venue);
			$category = $venue->category;

			// Set actions
			if (Permission::has($venue, Model_Venue::PERMISSION_DELETE, self::$user)) {
				$this->page_actions[] = array('link' => Route::model($venue, 'delete') . '?token=' . Security::csrf(), 'text' => __('Delete venue'), 'class' => 'venue-delete');
			}

		} else {

			// Creating new
			$category = Jelly::select('venue_category')->load($category_id);
			if (!$category->loaded()) {
				throw new Model_Exception($category, $category_id);
			}
			Permission::required($category, Model_Venue_Category::PERMISSION_VENUE, self::$user);

			$venue = Jelly::factory('venue')->set(array(
				'category' => $category,
				'author'   => self::$user,
			));
			$cancel = Route::model($category);

		}

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			$venue->set(Arr::extract($_POST, Model_Venue::$editable_fields));

			// GeoNames
			if ($_POST['city_id'] && $city = Geo::find_city((int)$_POST['city_id'])) {
				$venue->city = $city;
			}

			try {
				$venue->save();
				$this->request->redirect(Route::model($venue));
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		// Build form
		$form = array(
			'values' => $venue,
			'errors' => $errors,
			'cancel' => $cancel,
			'hidden' => array(
				'city_id'   => $venue->city ? $venue->city->id : 0,
				'latitude'  => $venue->latitude,
				'longitude' => $venue->longitude,
			),
			'groups' => array(
				'basic' => array(
					'header' => __('Basic information'),
					'fields' => array(
						'category'    => array(),
						'name'        => array(),
						'homepage'    => array(),
						'description' => array(),
						'event_host'  => array(),
					),
				),
				'contact' => array(
					'header' => __('Contact information'),
					'fields' => array(
						'address'   => array(),
						//'zip'       => array(),
						'city_name' => array(),
					)
				),
				'details' => array(
					'header' => __('Detailed information'),
					'fields' => array(
						//'logo' => array(),
						'hours' => array(),
						'info'  => array(),
					)
				)
			)
		);
		if ($category->tag_group && count($category->tag_group->tags)) {
			$tags = array();
			foreach ($category->tag_group->tags as $tag) {
				$tags[$tag->id()] = $tag->name();
			}
			$form['groups']['details']['fields']['tags'] = array(
				'class'  => 'pills',
				'values' => $tags,
			);
		}

		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));

		// Autocomplete
		$this->autocomplete_city('city_name', 'city_id');

		// Maps
		Widget::add('foot', HTML::script_source('
$(function() {
	$("#fields-contact ul").append("<li><div id=\"map\">' . __('Loading map..') . '</div></li>");

	$("#map").googleMap(' . ($venue->latitude ? json_encode(array('marker' => true, 'lat' => $venue->latitude, 'long' => $venue->longitude)) : '') . ');

	$("input[name=address], input[name=city_name]").blur(function(event) {
		var address = $("input[name=address]").val();
		var city = $("input[name=city_name]").val();
		if (address != "" && city != "") {
			var geocode = address + ", " + city;
			geocoder.geocode({ address: geocode }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK && results.length) {
				  map.setCenter(results[0].geometry.location);
				  $("input[name=latitude]").val(results[0].geometry.location.lat());
				  $("input[name=longitude]").val(results[0].geometry.location.lng());
				  var marker = new google.maps.Marker({
				    position: results[0].geometry.location,
				    map: map
				  });
				}
			});
		}
	});

});
'));
	}


	/**
	 * Edit venue data in dialog
	 */
	protected function _edit_venue_dialog() {
		echo View_Module::factory('venues/edit_dialog', array(

		));
	}


	/**
	 * Get image mod
	 *
	 * @param   Model_Venue  $venue
	 * @return  View_Module
	 */
	protected function _get_mod_image(Model_Venue $venue) {
		return View_Module::factory('generic/side_image', array(
			'mod_actions2' => Permission::has($venue, Model_Venue::PERMISSION_UPDATE, self::$user)
				? array(
						array('link' => Route::model($venue, 'image') . '?token=' . Security::csrf() . '&delete', 'text' => __('Delete'), 'class' => 'image-delete disabled'),
						array('link' => Route::model($venue, 'image') . '?token=' . Security::csrf() . '&default', 'text' => __('Set as default'), 'class' => 'image-default disabled'),
						array('link' => Route::model($venue, 'image'), 'text' => __('Add image'), 'class' => 'image-add ajaxify')
					)
				: null,
			'image' => $venue->default_image->id ? $venue->default_image : null,
		));
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
				'venues'    => Model_Venue::find_new(20),
			))),
			'updated' => array('href' => '#venues-updated', 'title' => __('Updated venues'), 'tab' => View_Module::factory('venues/list', array(
				'mod_id'    => 'venues-updated',
				'mod_class' => 'cut tab venues',
				'title'     => __('Updated Venues'),
				'venues'    => Model_Venue::find_updated(20),
			))),
		);

		Widget::add('side', View::factory('generic/tabs_side', array('id' => 'venues-tab', 'tabs' => $tabs)));
	}


}
