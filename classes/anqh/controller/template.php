<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Abstract Anqh Template controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Controller_Template extends Kohana_Controller_Template {

	/**
	 * AJAX-like request?
	 *
	 * @var  bool
	 */
	protected $ajax = false;

	/**
	 * Add current page to history
	 *
	 * @var  bool
	 */
	protected $history = true;

	/**
	 * Actions for current page
	 *
	 * @var  array
	 */
	protected $page_actions = array();

	/**
	 * Current page class
	 *
	 * @var  string
	 */
	protected $page_class;

	/**
	 * Current page id, defaults to controller name
	 *
	 * @var  string
	 */
	protected $page_id;

	/**
	 * Page main content position
	 *
	 * @var  string
	 */
	protected $page_main = 'left';

	/**
	 * Current page subtitle
	 *
	 * @var  string
	 */
	protected $page_subtitle = '';

	/**
	 * Current page title
	 *
	 * @var  string
	 */
	protected $page_title = '';

	/**
	 * Page width setting, 'fixed' or 'liquid'
	 *
	 * @var  string
	 */
	protected $page_width = 'fixed';

	/**
	 * Skin for the site
	 *
	 * @var  string
	 */
	protected $skin;

	/**
	 * Skin files imported in skin, check against file modification time for LESS
	 *
	 * @var  array
	 */
	protected $skin_imports;

	/**
	 * Selected tab
	 *
	 * @var  string
	 */
	protected $tab_id;

	/**
	 * Tabs navigation
	 *
	 * @var  array
	 */
	protected $tabs;


	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		// Controller name as the default page id if none set
		empty($this->page_id) && $this->page_id = $this->request->controller;

		$this->ajax = (Request::$is_ajax || $this->request !== Request::instance());
	}


	/**
	 * Destroy controller
	 */
	public function after() {
		if ($this->ajax) {

		} else if ($this->auto_render) {

			// Skin
			$skin_path = 'ui/' . Kohana::config('site.skin') . '/';
			$skin = $skin_path . 'skin.less';
			$skin_imports = array(
				'ui/layout.less',
				'ui/widget.less',
				'ui/jquery-ui.css',
				'ui/site.css',
				$skin_path . 'jquery-ui.css',
			);

			// Do some CSS magic to page class
			$page_class = explode(' ',
				$this->language . ' ' .        // Language
				$this->page_width . ' ' .      // Fixed/liquid layout
				$this->page_main . ' ' .       // Left/right aligned layout
				$this->request->action . ' ' . // Controller method
				$this->page_class);
			$page_class = implode(' ', array_unique(array_map('trim', $page_class)));

			// Bind the generic page variables
			$this->template
				->set('skin',          $skin)
				->set('skin_imports',  $skin_imports)
				->set('language',      $this->language)
				->set('page_id',       $this->page_id)
				->set('page_class',    $page_class)
				->set('page_title',    $this->page_title)
				->set('page_subtitle', $this->page_subtitle);

			// Generic views
			Widget::add('actions',    View::factory('generic/actions')->set('actions', $this->page_actions));
			Widget::add('navigation', View::factory('generic/navigation')->set('items', Kohana::config('site.menu'))->set('selected', $this->page_id));
			Widget::add('tabs',       View::factory('generic/tabs_top')->set('tabs', $this->tabs)->set('selected', $this->tab_id));

			// Dock
			$classes = array(
				HTML::anchor('set/width/narrow', __('Narrow'), array('onclick' => '$("body").addClass("fixed").removeClass("liquid"); $.get(this.href); return false;')),
				HTML::anchor('set/width/wide',   __('Wide'),   array('onclick' => '$("body").addClass("liquid").removeClass("narrow"); $.get(this.href); return false;')),
				HTML::anchor('set/main/left',    __('Left'),   array('onclick' => '$("body").addClass("left").removeClass("right"); $.get(this.href); return false;')),
				HTML::anchor('set/main/right',   __('Right'),  array('onclick' => '$("body").addClass("right").removeClass("left"); $.get(this.href); return false;')),
			);
			Widget::add('dock2', __('Layout: ') . implode(', ', $classes));

			// Language selection
			$available_languages  = Kohana::config('locale.languages');
			if (count($available_languages)) {
				$languages = array();
				foreach ($available_languages as $lang => $locale) {
					$languages[] = HTML::anchor('set/lang/' . $lang, HTML::chars($locale[2]));
				}
				Widget::add('dock2', ' | ' . __('Language: ') . implode(', ', $languages));
			}

			// End
			Widget::add('end', View::factory('generic/end'));

			// And finally the profiler stats
			if (in_array(Kohana::$environment, array(Kohana::DEVELOPMENT, Kohana::TESTING))) {
				Widget::add('foot', View::factory('profiler/stats'));
			}

		}

		parent::after();
	}

}
