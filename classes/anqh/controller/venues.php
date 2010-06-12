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
			$this->page_actions[] = array('link' => Route::model($category, 'edit'), 'text' => __('Edit category'), 'class' => 'category-edit');
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
			$cancel = Request::back(Route::model($category), true);

		} else {

			// Creating new
			$category = Jelly::factory('venue_category');
			Permission::required($category, Model_Venue_Category::PERMISSION_CREATE, $this->user);
			$cancel = Request::back(Route::get('venues')->uri(), true);

		}

		// Handle post
		$errors = array();
		if ($_POST) {
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
						'description' => array()
					),
				),
			)
		);

		Widget::add('main', View_Module::factory('form/anqh', array('form' => $form)));
	}

}
