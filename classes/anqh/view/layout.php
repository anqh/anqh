<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh page view class.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_Layout extends Kostache_Layout {

	/**
	 * @var  array  Page actions
	 */
	public $actions;

	/**
	 * @var  string  Page charset
	 */
	public $charset;

	/**
	 * @var  string  Page class
	 */
	public $class;

	/**
	 * @var  boolean  Footer visible
	 */
	public $has_footer = true;

	/**
	 * @var  boolean  Header visible
	 */
	public $has_header = true;

	/**
	 * @var  boolean  Title visible
	 */
	public $has_title = true;

	/**
	 * @var  string  Page id
	 */
	public $id;

	/**
	 * @var  string  Page language
	 */
	public $language;

	/**
	 * @var  array  Partials
	 */
	protected $_partials = array(
		'header_menu' => 'header/menu',
		'footer_menu' => 'footer/menu',
	);

	/**
	 * @var  string  Page subtitle
	 */
	public $subtitle;

	/**
	 * @var  string  Selected tab
	 */
	public $tab_id;

	/**
	 * @var  array  Tabs navigation
	 */
	public $tabs;

	/**
	 * @var  string  Page title
	 */
	public $title;

	/**
	 * @var  string  Content template
	 */
	// protected $_template = 'content';

	/**
	 * @var  string  Anqh version
	 */
	public $version_anqh = Anqh::VERSION;

	/**
	 * @var  string  Kohana version
	 */
	public $version_kohana = Kohana::VERSION;


	/**
	 * Initialize view.
	 */
	public function _initialize() {
		$this->charset = Kohana::$charset;
		$this->title   = Kohana::config('site.site_name');

		// Ads
		$ads = Kohana::config('site.ads');
		if ($ads && Arr::get($ads, 'enabled')) {
			foreach ($ads['slots'] as $ad => $slot) {
				Widget::add($slot, View::factory('ads/' . $ad), Widget::MIDDLE);
			}
		}

	}


	/**
	 * Var method for page actions.
	 *
	 * @return  string
	 */
	public function actions() {
		return View::factory('generic/actions', array('actions' => $this->actions));
	}


	/**
	 * Var method for base url.
	 *
	 * @return  string
	 */
	public function base() {
		return URL::base(!Request::current()->is_initial());
	}


	/**
	 * Var method for page class.
	 *
	 * @return  string
	 */
	public function _class() {

		// Custom class(es)
		$class = explode(' ', $this->class);

		// Site language
		$class[] = $this->language;

		// Controller method
		$class[] = Request::current()->action();

		return trim(implode(' ', array_unique($class)));
	}


	/**
	 * Var method for copyright.
	 *
	 * @return  array
	 */
	public function copyright() {
		return array(
			'from' => 2000,
			'to'   => date('Y'),
			'site' => Kohana::config('site.site_name'),
		);
	}


	/**
	 * Var method for dock.
	 *
	 * @return  string
	 */
	public function dock() {
		$classes = array();
		foreach ((array)Kohana::config('site.skins') as $skin_name => $skin_config) {
			$classes[] = HTML::anchor(
				Route::url('setting', array('action' => 'skin', 'value' => $skin_name)),
				$skin_config['name'],
				array(
					'class' => 'theme',
					'rel'   => $skin_name,
				));
		}

		$dock  = __('Theme') . ': ' . implode(', ', $classes);
		$dock .= ' | ' . View::factory('generic/clock', array(
			'user' => self::$user,
		));
		$dock .= ' | ' . HTML::anchor('#pin', '&#9650;', array('title' => __('Lock menu'), 'class' => 'icon unlock', 'onclick' => '$("#header").toggleClass("pinned"); return false;'));

		return $dock;
	}


	/**
	 * Var method for form csrf.
	 *
	 * @return  string
	 */
	public function form_csrf() {
		return Form::csrf();
	}


	/**
	 * Var method for GeoNames configuration
	 *
	 * @return  array
	 */
	public function geo() {
		return array(
			'url'  => Kohana::config('geo.base_url'),
			'user' => Kohana::config('geo.username'),
		);
	}


	/**
	 * Var method for head content.
	 *
	 * @return  string
	 */
	public function head() {
		$head = Widget::get('head');

		// Google Analytics
		if ($google_analytics = Kohana::config('site.google_analytics')) {
			$head .= HTML::script_source("
var tracker;
head.js(
	{ 'google-analytics': 'http://www.google-analytics.com/ga.js' },
	function() {
		tracker = _gat._getTracker('" . $google_analytics . "');
		tracker._trackPageview();
	}
);
");
		}

		// Open Graph
		$og = array();
		foreach ((array)Anqh::open_graph() as $key => $value) {
			$og[] = '<meta property="' . $key . '" content="' . HTML::chars($value) . '" />';
		}
		if (!empty($og)) {
			$head .= implode("\n", $og);
		}

		return $head;
	}


	/**
	 * Var method for foot content.
	 *
	 * @return  string
	 */
	public function foot() {
		return Widget::get('foot');
	}


	/**
	 * Var method for side content.
	 *
	 * @return  string
	 */
	public function footer() {
		return View_Module::factory('events/event_list', array(
			'mod_id'    => 'footer-events-new',
			'mod_class' => 'article grid4 first cut events',
			'mod_title' => __('New events'),
			'events'    => Model_Event::factory()->find_new(10)
		)) . View_Module::factory('forum/topiclist', array(
			'mod_id'    => 'footer-topics-active',
			'mod_class' => 'article grid4 cut topics',
			'mod_title' => __('New posts'),
			'topics'    => Model_Forum_Topic::factory()->find_by_latest_post(10)
		)) . View_Module::factory('blog/entry_list', array(
			'mod_id'    => 'footer-blog-entries',
			'mod_class' => 'article grid4 cut blogentries',
			'mod_title' => __('New blogs'),
			'entries'   => Model_Blog_Entry::factory()->find_new(10),
		));
	}


	/**
	 * Var method for page id.
	 *
	 * @return  string
	 */
	public function id() {
		return $this->id ? $this->id : Request::current()->controller();
	}


	/**
	 * Var method for main content.
	 *
	 * @return  string
	 */
	public function main() {
		return Widget::get('main');
	}


	/**
	 * Var method for menu.
	 *
	 * @return  array
	 */
	public function menu() {
		$menu = array();
		foreach (Kohana::config('site.menu') as $id => $item) {
			$menu[] = array(
				'id'       => $id,
				'href'     => $item['url'],
				'text'     => $item['text'],
				'selected' => $this->id == $id ? 'selected' : '',
			);
		}

		return $menu;
	}


	/**
	 * Var method for profiler.
	 *
	 * @return  string
	 */
	public function profiler() {
		if (self::$user && self::$user->has_role('admin')) {
			return View::factory('generic/debug')->render() . View::factory('profiler/stats')->render();
		}

		return null;
	}


	/**
	 * Var method for side content.
	 *
	 * @return  string
	 */
	public function side() {
		return Widget::get('side');
	}


	/**
	 * Var method for skins.
	 *
	 * @return  array
	 */
	public function skins() {

		// Selected skin
		$selected_skin = Session::instance()->get('skin', 'dusk');

		// Less files needed to build a skin
		$less_imports = array(
			'ui/color.less',
			'ui/mixin.less',
			'ui/anqh.less',
		);

		$skins = array();
		foreach (array('dawn', 'day', 'dusk', 'night') as $skin) {
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

		return $skins;
	}


	/**
	 * Var method for page statistics.
	 *
	 * @return  string
	 */
	public function statistics() {

		// Count DB queries
		$queries = 0;
		if (Kohana::$profiling) {
			foreach (Profiler::groups() as $group => $benchmarks) {
				if (strpos($group, 'database') === 0) {
					$queries += count($benchmarks);
				}
			}
		}

		return __('Page rendered in :execution_time seconds, using :memory_usage of memory, :database_queries database queries and :included_files files', array(
			':memory_usage'     => number_format((memory_get_peak_usage() - KOHANA_START_MEMORY) / 1024, 2) . 'KB',
			':execution_time'   => number_format(microtime(true) - KOHANA_START_TIME, 5),
			':database_queries' => $queries,
			':included_files'   => count(get_included_files()),
		));
	}


	/**
	 * Var method for stylesheets.
	 *
	 * @return  array
	 */
	public function styles() {

		// Less files needed to build a skin
		$less_imports = array(
			'ui/mixin.less',
			'ui/palette.less',
//			'ui/grid.less',
//			'ui/layout.less',
//			'ui/widget.less',
//			'ui/custom.less'
		);

		return array(

			// Base
			array('style' => Less::style('ui/anqh.less', null, false, $less_imports)),
/*
			// Tablet landscape
			array('style' => Less::style('ui/tablet.less', array('media' => 'only screen and (min-width: 768px) and (max-width: 991px)'))),

			// Mobile
			array('style' => Less::style('ui/mobile.less', array('media' => 'only screen and (max-width: 767px)'))),

			// Mobile landscape
			array('style' => Less::style('ui/mobile-wide.less', array('media' => 'only screen and (min-width: 480px) and (max-width: 767px)'))),

			// Mobile portrait
			array('style' => Less::style('ui/mobile-wide.less', array('media' => 'only screen and (min-width: 320px) and (max-width: 480px)'))),
*/
//			array('style' => HTML::style('ui/boot.css')),      // Reset
//			array('style' => HTML::style('ui/typo.css')),      // Typography
//			array('style' => HTML::style('ui/base.css')),      // Deprecated
			HTML::style('ui/jquery-ui.css'), // Deprecated
			HTML::style('http://fonts.googleapis.com/css?family=Nobile:regular,bold'),
		);
	}


	/**
	 * Var method for page tabs.
	 *
	 * @return  View|null
	 */
	public function tabs() {
		if ($this->tabs) {
			return View::factory('generic/navigation', array(
				'items'    => $this->tabs,
				'selected' => $this->tab_id,
			));
		}

		return null;
	}


	/**
	 * Var method for view_share.
	 *
	 * @return  string
	 */
	public function view_share() {
		return new View_Generic_Share();
	}


	/**
	 * Var method for visitor card.
	 *
	 * @return  View_Header_Visitor
	 */
	public function view_visitor() {
		return new View_Header_Visitor();
	}


	/**
	 * Var method for main slot.
	 *
	 * @return  array
	 */
	public function views_main() {}


	/**
	 * Var method for side slot.
	 *
	 * @return  array
	 */
	public function views_side() {}


	/**
	 * Var method for top slot.
	 *
	 * @return  array
	 */
	public function views_top() {}


	/**
	 * Var method for wide content.
	 *
	 * @return  string
	 */
	public function wide() {
		return Widget::get('wide');
	}

}
