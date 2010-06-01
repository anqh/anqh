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
	 * @var  boolean
	 */
	protected $ajax = false;

	/**
	 * Add current page to history
	 *
	 * @var  boolean
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
		$this->auto_render = !$this->internal;
		$this->ajax = Request::$is_ajax;

		parent::before();
	}


	/**
	 * Destroy controller
	 */
	public function after() {

		// Save current URI
		if (!$this->ajax && !$this->internal && $this->history && $this->request->status < 400) {
			Session::instance()->set('history', $this->request->uri());
		}

		if ($this->ajax || $this->internal) {
			$this->request->response .= '';
		} else if ($this->auto_render) {

			// Controller name as the default page id if none set
			empty($this->page_id) && $this->page_id = $this->request->controller;

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

			if ($this->user) {

				// Authenticated view
				Widget::add('dock', __('[#:id] :user', array(':id' => $this->user->id, ':user' => HTML::user($this->user))));

				$new_messages = array();
				if ($this->user->newcomments) {
					$new_messages[] = HTML::anchor(
						URL::user($this->user),
						__(':commentsC', array(':comments' => $this->user->newcomments)),
						array('title' => __('New comments'), 'class' => 'new-comments')
					);
				}
				if (!empty($new_messages)) {
					Widget::add('dock', ' - ' . __('New messages: ') . implode(' ', $new_messages));
				}

				// Logout also from Facebook
				/*
				if (FB::enabled() && Visitor::instance()->get_provider()) {
					Widget::add('dock', ' - ' . HTML::anchor('sign/out', FB::icon() . __('Sign out'), array('onclick' => "FB.Connect.logoutAndRedirect('/sign/out'); return false;")));
				} else {
				*/
					Widget::add('dock', ' - ' . HTML::anchor('sign/out', __('Sign out')));
				//}


				if (Kohana::config('site.inviteonly')) {
	//				widget::add('dock', ' | ' . html::anchor('sign/up', __('Send invite')));
				}

				// Admin functions
				if ($this->user->has_role('admin')) {
					Widget::add('dock2', ' | ' . __('Admin: ')
						. HTML::anchor(Route::get('roles')->uri(), __('Roles')) . ', '
						. HTML::anchor('tags', __('Tags')) . ', '
						. HTML::anchor('#kohana-profiler', __('Profiler'), array('onclick' => '$("#kohana-profiler").toggle();'))
					);
				}

			} else {

				// Non-authenticated view
				$form =  Form::open(Route::get('sign')->uri(array('action' => 'in')));
				$form .= Form::input('username', null, array('title' => __('Username')));
				$form .= Form::password('password', null, array('title' => __('Password')));
				$form .= Form::submit('signin', __('Sign in'));
				$form .= Form::close();
				$form .= html::anchor(Route::get('sign')->uri(array('action' => 'up')), __('Sign up'));
				/*
				if (FB::enabled()) {
					$form .= ' | ' . FB::fbml_login();
				}
				*/
				Widget::add('dock', $form);

			}

			// End
			Widget::add('end', View::factory('generic/end'));

			// Analytics
			$google_analytics = Kohana::config('site.google_analytics');
			if ($google_analytics) {
				Widget::add('head', HTML::script_source("
	var _gaq = _gaq || []; _gaq.push(['_setAccount', '" . $google_analytics . "']); _gaq.push(['_trackPageview']);
	(function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(ga);
	})();
	"));
			}

			// Ads
			$ads = Kohana::config('site.ads');
			if ($ads && $ads['enabled']) {
				foreach ($ads['slots'] as $ad => $slot) {
					Widget::add($slot, View::factory('ads/' . $ad));
				}
			}

			// And finally the profiler stats
			if (in_array(Kohana::$environment, array(Kohana::DEVELOPMENT, Kohana::TESTING))) {
				Widget::add('foot', View::factory('profiler/stats'));
			}

			parent::after();
		}

	}

}
