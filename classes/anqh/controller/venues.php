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
		return $this->_edit_venue((int)$this->request->param('id'));
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
		if (Permission::has($category, Model_Venue_Category::PERMISSION_UPDATE, $this->user)) {
			$this->page_actions[] = array('link' => Route::model($category, 'editcategory'), 'text' => __('Edit category'), 'class' => 'category-edit');
		}
		if (Permission::has($category, Model_Venue_Category::PERMISSION_VENUE, $this->user)) {
			$this->page_actions[] = array('link' => Route::model($category, 'add'), 'text' => __('Add venue'), 'class' => 'venue-add');
		}

		// Set title
		$this->page_title   .= ': ' . HTML::chars($category->name);
		$this->page_subtitle = HTML::chars($category->description);

		// Organize by city


		Widget::add('main', View_Module::factory('venues/venues', array(
			'mod_class' => 'venues articles',
			'venues'    => $category->find_venues_by_city(),
		)));
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

		Permission::required($venue, Model_Venue::PERMISSION_DELETE, $this->user);

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

		Permission::required($category, Model_Venue_Category::PERMISSION_DELETE, $this->user);

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
	 * Controller default action
	 */
	public function action_index() {

		// Set actions
		if (Permission::has(new Model_Venue_Category, Model_Venue_Category::PERMISSION_CREATE, $this->user)) {
			$this->page_actions[] = array('link' => Route::get('venue_category_add')->uri(), 'text' => __('Add category'), 'class' => 'category-add');
		}

		Widget::add('main', View_Module::factory('venues/categories', array(
			'categories' => Jelly::select('venue_category')->execute(),
		)));
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
		if (Permission::has($venue, Model_Venue::PERMISSION_UPDATE, $this->user)) {
			$this->page_actions[] = array('link' => Route::model($venue, 'edit'), 'text' => __('Edit venue'), 'class' => 'venue-edit');
		}

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
			Permission::required($category, Model_Venue_Category::PERMISSION_UPDATE, $this->user);
			$cancel = Route::model($category);

			// Set actions
			if (Permission::has($category, Model_Forum_Topic::PERMISSION_DELETE, $this->user)) {
				$this->page_actions[] = array('link' => Route::model($category, 'deletecategory'), 'text' => __('Delete category'), 'class' => 'category-delete');
			}

		} else {

			// Creating new
			$category = Jelly::factory('venue_category');
			Permission::required($category, Model_Venue_Category::PERMISSION_CREATE, $this->user);
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
			Permission::required($venue, Model_Venue::PERMISSION_UPDATE, $this->user);
			$cancel = Route::model($venue);
			$category = $venue->category;

			// Set actions
			if (Permission::has($venue, Model_Venue::PERMISSION_DELETE, $this->user)) {
				$this->page_actions[] = array('link' => Route::model($venue, 'delete') . '?token=' . Security::csrf(), 'text' => __('Delete venue'), 'class' => 'venue-delete');
			}

		} else {

			// Creating new
			$category = Jelly::select('venue_category')->load($category_id);
			if (!$category->loaded()) {
				throw new Model_Exception($category, $category_id);
			}
			Permission::required($category, Model_Venue_Category::PERMISSION_VENUE, $this->user);

			$venue = Jelly::factory('venue')->set(array(
				'category' => $category,
				'author'   => $this->user,
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
				'city_id' => ($venue->city ? $venue->city->id : 0),
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
						'zip'       => array(),
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
		$this->autocomplete_city('city_name', 'city_id');
	}

}
