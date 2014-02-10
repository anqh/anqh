<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Page controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Controller_Page extends Controller {

	/**
	 * Construct controller.
	 */
	public function before() {
		parent::before();

		$this->auto_render = ($this->_request_type === Controller::REQUEST_INITIAL);
		$this->history     = $this->history && !$this->ajax;

		// Initialize Ads
		Ads::init();

		// Load the template
		if ($this->auto_render === true) {
			$this->view = View_Page::factory();
		}

	}


	/**
	 * Destroy controller.
	 */
	public function after() {
		if ($this->_request_type !== Controller::REQUEST_INITIAL) {

			// AJAX and HMVC requests
			$this->response->body((string)$this->response->body());

		} else if ($this->auto_render) {

			// Normal requests

			// Footer
			$section = new View_Events_List();
			$section->class .= ' col-sm-4';
			$section->title  = __('New events');
			$section->events = Model_Event::factory()->find_new(10);
			$this->view->add(View_Page::COLUMN_FOOTER, $section);

			$section = new View_Topics_List();
			$section->class .= ' col-sm-4';
			$section->topics = Model_Forum_Topic::factory()->find_by_latest_post(10);
			$this->view->add(View_Page::COLUMN_FOOTER, $section);

			$section = new View_Blogs_List();
			$section->class       .= ' col-sm-4';
			$section->title        = __('New blogs');
			$section->blog_entries = Model_Blog_Entry::factory()->find_new(10);
			$this->view->add(View_Page::COLUMN_FOOTER, $section);


			// Ads
/*			$ads = Kohana::$config->load('site.ads');
			if ($ads && $ads['enabled']) {
				foreach ($ads['slots'] as $ad => $slot) {
					if ($slot == 'side') {
						$this->view->add(View_Page::COLUMN_SIDE, View::factory('ads/' . $ad));
					} else {
						Widget::add($slot, View::factory('ads/' . $ad), Widget::MIDDLE);
					}
				}
			}*/


			// Do some CSS magic to page class
			$page_class = array_merge(array(
				'theme-mixed',
				self::$user ? 'authenticated' : 'unauthenticated',
			), explode(' ' , $this->page_class));
			$page_class = implode(' ', array_unique($page_class));


			// Set the generic page variables
			$this->view->language = $this->language;
			$this->view->id       = $this->page_id;
			$this->view->class    = $page_class;
			if ($this->page_actions) {
				$this->view->tabs = $this->page_actions;
			}
			if ($this->page_breadcrumbs) {
				$this->view->breadcrumbs = $this->page_breadcrumbs;
			}


			// And finally the profiler stats
			if (self::$user && self::$user->has_role('admin')) {
				Widget::add('foot', new View_Generic_Debug());
				Widget::add('foot', View::factory('profiler/stats'));
			}

		}

		parent::after();
	}


	/**
	 * Get share.
	 *
	 * @param   string  $url
	 * @param   string  $title
	 * @return  View_Generic_Share
	 */
	public function section_share($url = null, $title = null) {
		return new View_Generic_Share($url, $title);
	}

}
