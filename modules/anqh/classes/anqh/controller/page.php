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

			// Stylesheets
			$styles = array(
				'//cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.css',
				'//cdnjs.cloudflare.com/ajax/libs/semantic-ui/0.12.0/css/semantic.min.css',
				'static/css/anqh.css?_=' . filemtime('static/css/anqh.css')
			);

			// Footer
			$section = new View_Events_List();
			$section->title  = __('New events');
			$section->events = Model_Event::factory()->find_new(10);
			$this->view->add(View_Page::COLUMN_FOOTER, '<div class="column">' . $section . '</div>');

			$section = new View_Topics_List();
			$section->topics = Model_Forum_Topic::factory()->find_by_latest_post(10);
			$this->view->add(View_Page::COLUMN_FOOTER, '<div class="column">' . $section . '</div>');

			$section = new View_Blogs_List();
			$section->title        = __('New blogs');
			$section->blog_entries = Model_Blog_Entry::factory()->find_new(10);
			$this->view->add(View_Page::COLUMN_FOOTER, '<div class="column">' . $section . '</div>');


			// Open Graph
			$meta = array();
			foreach ((array)Anqh::page_meta() as $key => $value) {
				$meta[] = strpos($key, 'twitter:') === 0
					? '<meta name="' . $key . '" content="' . HTML::chars($value) . '" />'
					: '<meta property="' . $key . '" content="' . HTML::chars($value) . '" />';
			}
			if (!empty($meta)) {
				Widget::add('head', implode("\n", $meta));
			}


			// Analytics
			if ($google_analytics = Kohana::$config->load('site.google_analytics')) {
				Widget::add('head', HTML::script_source("
var tracker;
head.js(
	{ 'google-analytics': 'http://www.google-analytics.com/ga.js' },
	function() {
		tracker = _gat._getTracker('" . $google_analytics . "');
		tracker._trackPageview();
	}
);
"));
			}


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
				'mixed theme',
				self::$user ? 'authenticated' : 'unauthenticated',
			), explode(' ' , $this->page_class));
			$page_class = implode(' ', array_unique($page_class));


			// Set the generic page variables
			$this->view->styles   = $styles;
			$this->view->language = $this->language;
			$this->view->id       = $this->page_id;
			$this->view->class    = $page_class;
			if ($this->page_title) {
				$this->view->title = $this->page_title;
			}
			if ($this->page_actions) {
				$this->view->tabs = $this->page_actions;
			}
			if ($this->page_breadcrumbs) {
				$this->view->breadcrumbs = $this->page_breadcrumbs;
			}


			// And finally the profiler stats
			if (false && self::$user && self::$user->has_role('admin')) {
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
