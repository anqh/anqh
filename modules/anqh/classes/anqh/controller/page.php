<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Page controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
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
				'ui/jquery-ui.css', // Deprecated
//				'http://fonts.googleapis.com/css?family=Terminal+Dosis'
			);

			$skins = array(
				HTML::style('static/css/bootstrap.css'),
				HTML::style('static/css/bootstrap-responsive.css'),
				HTML::style('//netdna.bootstrapcdn.com/font-awesome/3.1.1/css/font-awesome.css'),
			);

			// Footer
			$section = new View_Events_List();
			$section->class .= ' span4';
			$section->title  = __('New events');
			$section->events = Model_Event::factory()->find_new(10);
			Widget::add('footer', $section);

			$section = new View_Topics_List();
			$section->class .= ' span4';
			$section->title  = __('New posts');
			$section->topics = Model_Forum_Topic::factory()->find_by_latest_post(10);
			Widget::add('footer', $section);

			$section = new View_Blogs_List();
			$section->class       .= ' span4';
			$section->title        = __('New blogs');
			$section->blog_entries = Model_Blog_Entry::factory()->find_new(10);
			Widget::add('footer', $section);


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
				$this->language,
				$this->request->action(),
				self::$user ? 'authenticated' : 'unauthenticated',
			), explode(' ' , $this->page_class));
			$page_class = implode(' ', array_unique($page_class));


			// Set the generic page variables
			$this->view->styles   = $styles;
			$this->view->skins    = $skins;
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
			if (self::$user && self::$user->has_role('admin')) { //in_array(Kohana::$environment, array(Kohana::DEVELOPMENT, Kohana::TESTING))) {
				Widget::add('foot', View::factory('generic/debug'));
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
