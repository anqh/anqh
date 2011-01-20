<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Abstract Anqh Template controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Controller_Template extends Controller {

	/**
	 * @var  boolean  auto render template
	 **/
	public $auto_render = true;

	/**
	 * @var  array  Bookmarks / navigation history
	 */
	protected $breadcrumb = array();

	/**
	 * @var  boolean  Add current page to history
	 */
	protected $history = true;

	/**
	 * @var  array  Actions for current page
	 */
	protected $page_actions = array();

	/**
	 * @var  string  Current page class
	 */
	protected $page_class;

	/**
	 * @var  string  Current page id, defaults to controller name
	 */
	protected $page_id;

	/**
	 * @var  string  Page main content position
	 */
	protected $page_main = 'left';

	/**
	 * @var  string  Current page subtitle
	 */
	protected $page_subtitle = '';

	/**
	 * @var  string  Current page title
	 */
	protected $page_title = '';

	/**
	 * @var  string  Page width setting, 'fixed' or 'liquid'
	 */
	protected $page_width = 'fixed';

	/**
	 * @var  string  Skin for the site
	 */
	protected $skin;

	/**
	 * @var  array  Skin files imported in skin, check against file modification time for LESS
	 */
	protected $skin_imports;

	/**
	 * @var  string  Selected tab
	 */
	protected $tab_id;

	/**
	 * @var  array  Tabs navigation
	 */
	protected $tabs;

	/**
	 * @var  string  page template
	 */
	public $template = 'template';


	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		$this->auto_render = !$this->internal;
		$this->breadcrumb = Session::instance()->get('breadcrumb', array());
		$this->history = $this->history && !$this->ajax;

		// Open outside links to other tab
		HTML::$windowed_urls = true;

		// Load the template
		if ($this->auto_render === true) {
			$this->template = View::factory($this->template);
		}

		// Online users
		if (!$this->internal) {
			$online = Model_User_Online::factory(Session::instance()->id());
			if (!$online->loaded()) {
				$online->id = Session::instance()->id();
			}
			$online->user = self::$user;
			try {
				$online->save();
			} catch (Validate_Exception $e) {}
		}

	}


	/**
	 * Destroy controller
	 */
	public function after() {
		if ($this->ajax || $this->internal) {
			$this->request->response .= '';
		} else if ($this->auto_render) {

			$session = Session::instance();

			// Save current URI
			if (!$this->ajax && !$this->internal && $this->history && $this->request->status < 400) {
				$uri = $this->request->uri;
				unset($this->breadcrumb[$uri]);
				$this->breadcrumb = array_slice($this->breadcrumb, -9, 9, true);
				$this->breadcrumb[$uri] = $this->page_title;
				$session
					->set('history', $uri . ($_GET ? URL::query($_GET) : ''))
					->set('breadcrumb', $this->breadcrumb);
			}

			// Controller name as the default page id if none set
			empty($this->page_id) && $this->page_id = $this->request->controller;

			// Generic views
			Widget::add('breadcrumb', View::factory('generic/breadcrumb', array('breadcrumb' => $this->breadcrumb, 'last' => !$this->history)));
			Widget::add('actions',    View::factory('generic/actions',    array('actions' => $this->page_actions)));
			Widget::add('navigation', View::factory('generic/navigation', array(
				'items'    => Kohana::config('site.menu'),
				'selected' => $this->page_id,
			)));
			if (!empty($this->tabs)) {
				Widget::add('subnavigation', View::factory('generic/navigation', array(
					'items'    => $this->tabs,
					'selected' => $this->tab_id,
				)));
			}
			Widget::add('tabs', View::factory('generic/tabs_top',   array('tabs' => $this->tabs, 'selected' => $this->tab_id)));

			// Footer
			Widget::add('footer', View_Module::factory('events/event_list', array(
				'mod_id'    => 'footer-events-new',
				'mod_class' => 'article grid4 first cut events',
				'mod_title' => __('New events'),
				'events'    => Model_Event::find_new(10)
			)));
			Widget::add('footer', View_Module::factory('forum/topiclist', array(
				'mod_id'    => 'footer-topics-active',
				'mod_class' => 'article grid4 cut topics',
				'mod_title' => __('New posts'),
				'topics'    => Model_Forum_Topic::find_by_latest_post(10)
			)));
			Widget::add('footer', View_Module::factory('blog/entry_list', array(
				'mod_id'    => 'footer-blog-entries',
				'mod_class' => 'article grid4 cut blogentries',
				'mod_title' => __('New blogs'),
				'entries'   => Model_Blog_Entry::find_new(10),
			)));


			// Skin
			$skins = Kohana::config('site.skins');
			$skin = $session->get('skin', 'dark');
			$skin_imports = array(
				'ui/mixin.less',
				'ui/grid.less',
				'ui/layout.less',
				'ui/widget.less',
				'ui/custom.less'
			);

			// Dock
			$classes = array(
//				HTML::anchor(Route::get('setting')->uri(array('action' => 'width', 'value' => 'narrow')), __('Narrow'), array('onclick' => '$("body").toggleClass("fixed", true).toggleClass("liquid", false); $.get(this.href); return false;')),
//				HTML::anchor(Route::get('setting')->uri(array('action' => 'width', 'value' => 'wide')),   __('Wide'),   array('onclick' => '$("body").toggleClass("liquid", true).toggleClass("narrow", false); $.get(this.href); return false;')),
//				HTML::anchor(Route::get('setting')->uri(array('action' => 'main',  'value' => 'left')),   __('Left'),   array('onclick' => '$("body").toggleClass("left", true).toggleClass("right", false); $.get(this.href); return false;')),
//				HTML::anchor(Route::get('setting')->uri(array('action' => 'main',  'value' => 'right')),  __('Right'),  array('onclick' => '$("body").toggleClass("right", true).toggleClass("left", false); $.get(this.href); return false;')),
			);
			foreach ($skins as $skin_name => &$skin_config) {
				$skin_config['path'] = 'ui/' . $skin_name . '/skin.less';
				$classes[] = HTML::anchor(
					Route::get('setting')->uri(array('action' => 'skin', 'value' => $skin_name)),
					$skin_config['name'],
					array(
						'class' => 'theme',
						'rel'   => $skin_name,
					));
			}

			Widget::add('dock', __('Layout: ') . implode(', ', $classes));

			// Language selection
			$available_languages  = Kohana::config('locale.languages');
			if (count($available_languages)) {
				$languages = array();
				foreach ($available_languages as $lang => $locale) {
					$languages[] = HTML::anchor('set/lang/' . $lang, HTML::chars($locale[2]));
				}
//				Widget::add('dock', ' | ' . __('Language: ') . implode(', ', $languages));
			}

			// Search
			/*
			Widget::add('search', View_Module::factory('generic/search', array(
				'mod_id' => 'search'
			)));
			 */

			// Visitor card
			Widget::add('visitor', View::factory('generic/visitor', array(
				'user'      => self::$user,
			)));

			// Online
			Widget::add('sidebar', View_Module::factory('user/online', array(
				'mod_id'    => 'online-users',
				'mod_title' => __('Online'),
				'viewer'    => self::$user,
			)));

			// Time & weather
			Widget::add('sidebar', View_Module::factory('generic/clock', array(
				'mod_id' => 'clock',
				'user'   => self::$user,
			)));

			// Admin functions
			if (self::$user && self::$user->has_role('admin')) {
				Widget::add('dock', ' | ' . __('Admin: ')
					. HTML::anchor(Route::get('roles')->uri(), __('Roles')) . ', '
					. HTML::anchor(Route::get('tags')->uri(), __('Tags')) . ', '
					. HTML::anchor('#debug', __('Profiler'), array('onclick' => '$("div.kohana").toggle();'))
				);
			}

			// Pin
			Widget::add('dock', ' | ' . HTML::anchor('#pin', __('Pin'), array('class' => 'pin', 'onclick' => '$("#dock").toggleClass("pinned"); return false;')));

			// End
			Widget::add('end', View::factory('generic/end'));

			// Analytics
			if ($google_analytics = Kohana::config('site.google_analytics')) {
				Widget::add('head', HTML::script_source("
var _gaq = _gaq || [];
_gaq.push(['_setAccount', '" . $google_analytics . "']);
_gaq.push(['_trackPageview']);

(function() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
})();
"));
			}

			// Open Graph
			$og = array();
			foreach ((array)Anqh::open_graph() as $key => $value) {
				$og[] = '<meta property="' . $key . '" content="' . HTML::chars($value) . '" />';
			}
			if (!empty($og)) {
				Widget::add('head', implode("\n", $og));
			}

			// Share
			if (Anqh::share()) {
				if ($share = Kohana::config('site.share')) {

					// 3rd party share
					Widget::add('share', View_Module::factory('share/share', array('mod_class' => 'like', 'id' => $share)));
					Widget::add('foot', View::factory('share/foot', array('id' => $share)));

				} else if ($facebook = Kohana::config('site.facebook')) {

					// Facebook Like
					Widget::add('share', View_Module::factory('facebook/like'));
					Widget::add('ad_top', View::factory('facebook/connect', array('id' => $facebook)));

				}
			}


			// Ads
			$ads = Kohana::config('site.ads');
			if ($ads && $ads['enabled']) {
				foreach ($ads['slots'] as $ad => $slot) {
					Widget::add($slot, View::factory('ads/' . $ad), Widget::MIDDLE);
				}
			}

			// And finally the profiler stats
			if (self::$user && self::$user->has_role('admin')) { //in_array(Kohana::$environment, array(Kohana::DEVELOPMENT, Kohana::TESTING))) {
				Widget::add('foot', View::factory('generic/debug'));
				Widget::add('foot', View::factory('profiler/stats'));
			}

			// Do some CSS magic to page class
			$page_class = explode(' ',
				$this->language . ' ' .                      // Language
				$session->get('page_width', 'fixed') . ' ' . // Fixed/liquid layout
				$session->get('page_main', 'left') . ' ' .   // Left/right aligned layout
				$this->request->action . ' ' .               // Controller method
				$this->page_class);                          // Controller set classes
			$page_class = implode(' ', array_unique(array_map('trim', $page_class)));

			// Bind the generic page variables
			$this->template
				->set('skin',          $skin)
				->set('skins',         $skins)
				->set('skin_imports',  $skin_imports)
				->set('language',      $this->language)
				->set('page_id',       $this->page_id)
				->set('page_class',    $page_class)
				->set('page_title',    $this->page_title)
				->set('page_subtitle', $this->page_subtitle);

			if ($this->auto_render === true) {
				$this->request->response = $this->template;
			}

		}

		return parent::after();
	}


	/**
	 * Autocomplete for city
	 *
	 * @param  string  $name  Form input name for city name
	 * @param  string  $id    Form input name for city id
	 */
	public function autocomplete_city($name = 'city_name', $id = 'city_id') {
		Widget::add('foot', HTML::script_source('
$(function() {
	$("input[name=' . $name . ']")
		.autocomplete({
			source: function(request, response) {
				$.ajax({
					url: "http://ws.geonames.org/searchJSON",
					dataType: "jsonp",
					data: {
						lang: "' . $this->language . '",
						featureClass: "P",
						countryBias: "FI",
						style: "full",
						maxRows: 10,
						name_startsWith: request.term
					},
					success: function(data) {
						response($.map(data.geonames, function(item) {
							return {
								id: item.geonameId,
								label: item.name + (item.adminName1 ? ", " + item.adminName1 : "") + ", " + item.countryName,
								value: item.name,
								lat: item.lat,
								long: item.lng
							}
						}))
					}
				})
			},
			minLength: 2,
			select: function(event, ui) {
				$("input[name=' . $id . ']").val(ui.item.id);
				if ($("input[name=address]") && $("input[name=address]").val() == "") {
					$("input[name=latitude]").val(ui.item.lat);
					$("input[name=longitude]").val(ui.item.long);
					map && map.setCenter(new google.maps.LatLng(ui.item.lat, ui.item.long));
				}
			},
		});
});
		'));
	}


	/**
	 * Print an error message
	 *
	 * @param  string  $message
	 */
	public function error($message = null) {
		!$message && $message = __('Error occured.');

		Widget::add('error', View_Module::factory('generic/error', array('message' => $message)));
	}

}
