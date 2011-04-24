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

		$this->auto_render = ($this->_request_type !== Controller::REQUEST_INTERNAL);
		$this->breadcrumb  = Session::instance()->get('breadcrumb', array());
		$this->history     = $this->history && !$this->ajax;

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
				'http://fonts.googleapis.com/css?family=Terminal+Dosis'
			);


			// Skins
			$selected_skin = Session::instance()->get('skin', 'blue');

			// Less files needed to build a skin
			$less_imports = array(
				//'ui/blue.less',
				'ui/mixin.less',
				'ui/anqh.less',
			);

			$skins = array();
			foreach (array('blue') as $skin) {
				$skins[] = Less::style(
					'ui/' . $skin . '.less',
					array(
						'title' => $skin,
						'rel'   => $skin == $selected_skin ? 'stylesheet' : 'alternate stylesheet'
					),
					false,
					$less_imports
				);
			}


			// Footer
			/*
			$section = new View_Events_List();
			$section->class  = 'cut events';
			$section->title  = __('New events');
			$section->events = Model_Event::factory()->find_new(10);
			Widget::add('footer', $section);
			*/
			Widget::add('footer', View_Module::factory('events/event_list', array(
				'mod_id'    => 'footer-events-new',
				'mod_class' => 'article grid4 first cut events',
				'mod_title' => __('New events'),
				'events'    => Model_Event::factory()->find_new(10)
			)));

			Widget::add('footer', View_Module::factory('forum/topiclist', array(
				'mod_id'    => 'footer-topics-active',
				'mod_class' => 'article grid4 cut topics',
				'mod_title' => __('New posts'),
				'topics'    => Model_Forum_Topic::factory()->find_by_latest_post(10)
			)));
			Widget::add('footer', View_Module::factory('blog/entry_list', array(
				'mod_id'    => 'footer-blog-entries',
				'mod_class' => 'article grid4 cut blogentries',
				'mod_title' => __('New blogs'),
				'entries'   => Model_Blog_Entry::factory()->find_new(10),
			)));


			// Open Graph
			$og = array();
			foreach ((array)Anqh::open_graph() as $key => $value) {
				$og[] = '<meta property="' . $key . '" content="' . HTML::chars($value) . '" />';
			}
			if (!empty($og)) {
				Widget::add('head', implode("\n", $og));
			}


			// Analytics
			if ($google_analytics = Kohana::config('site.google_analytics')) {
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
			$ads = Kohana::config('site.ads');
			if ($ads && $ads['enabled']) {
				foreach ($ads['slots'] as $ad => $slot) {
					if ($slot == 'side') {
						$this->view->add(View_Page::COLUMN_SIDE, View::factory('ads/' . $ad));
					} else {
						Widget::add($slot, View::factory('ads/' . $ad), Widget::MIDDLE);
					}
				}
			}


			// Do some CSS magic to page class
			$page_class = explode(' ',
				$this->language . ' ' .          // Language
				$this->request->action() . ' ' . // Controller method
				$this->page_class);              // Controller set class
			$page_class = implode(' ', array_unique(array_map('trim', $page_class)));


			// Set the generic page variables
			$this->view->styles   = $styles;
			$this->view->skins    = $skins;
			$this->view->language = $this->language;
			$this->view->id       = $this->page_id;
			$this->view->class    = $page_class;
			$this->view->actions  = $this->page_actions;

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
