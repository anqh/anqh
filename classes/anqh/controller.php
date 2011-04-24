<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Abstract Anqh controller
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Controller extends Kohana_Controller {

	/** Output format HTML */
	const FORMAT_HTML = 'text/html';

	/** Output format JSON */
	const FORMAT_JSON = 'application/json';

	/** Output format XML */
	const FORMAT_XML  = 'application/xml';

	/** Initial request */
	const REQUEST_INITIAL  = 1;

	/** Internal request, e.g. HMVC */
	const REQUEST_INTERNAL = 2;

	/** Ajax request */
	const REQUEST_AJAX     = 3;

	/**
	 * @var  array  Supported output formats
	 */
	protected $_accept_formats = array(
		self::FORMAT_HTML => '',
		self::FORMAT_JSON => 'json',
		self::FORMAT_XML  => 'xml',
	);

	/**
	 * @var  array  Actio to view maps for overriding default
	 */
	protected $_action_views = array();

	/**
	 * @var  boolean  AJAX-like request
	 */
	protected $ajax = false;

	/**v
	 * @var  boolean  Auto render view
	 **/
	protected $auto_render = true;

	/**
	 * @var  array  Bookmarks / navigation history
	 */
	protected $breadcrumb = array();

	/**
	 * @var  boolean  Add current page to history
	 */
	protected $history = true;

	/**
	 * @var  boolean  Internal request?
	 */
	protected $internal = false;

	/**
	 * @var  string  Current language
	 */
	protected $language = 'en';

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
	 * @var  string  Current page subtitle
	 */
	protected $page_subtitle = '';

	/**
	 * @var  string  Current page title
	 */
	protected $page_title = '';

	/**
	 * @var  integer  Current request type
	 * @see  REQUEST_*
	 */
	protected $_request_type;

	/**
	 * @var  string  Response format for request
	 * @see  $_accept_formats
	 */
	protected $_response_format;

	/**
	 * @var  Session  Current session
	 */
	public $session;

	/**
	 * @var  string  Selected tab
	 */
	protected $tab_id;

	/**
	 * @var  array  Tabs navigation
	 */
	protected $tabs;

	/**
	 * @var  Model_User  Current user
	 */
	protected static $user = false;

	/**
<<<<<<< HEAD
	 * @var  View_Page  Page class
=======
	 * @var  View_Layout|View_Page  Page class
>>>>>>> 5b2899d... View class bases views to replace Kostache and Kohana templates
	 */
	protected $view;


	/**
	 * Construct controller.
	 */
	public function before() {
		if ($this->request->is_ajax()) {
			$this->_request_type = self::REQUEST_AJAX;
		} else if ($this->request->is_initial()) {
			$this->_request_type = self::REQUEST_INITIAL;
		} else {
			$this->_request_type = self::REQUEST_INTERNAL;
		}

		// Check if this was an internal request or direct
		$this->internal = $this->_request_type === self::REQUEST_INTERNAL;

		// Ajax request?
		$this->ajax = $this->_request_type === self::REQUEST_AJAX;

		// Update history (and breadcrumbs)?
		$this->history = $this->history && !$this->ajax;

		// Initialize session
		$this->session = Session::instance();

		// Load current user, null if none
		if (self::$user === false) {
			Controller::$user = Visitor::instance()->get_user();
		}

		// Update current online user for initial and ajax requests
		if ($this->_request_type !== self::REQUEST_INTERNAL) {
			Model_User_Online::update(self::$user);
			$this->breadcrumb = $this->session->get('breadcrumb', array());
		}

		// Open outside links to a new tab/window
		HTML::$windowed_urls = true;

		// Load template
		if ($this->auto_render) {
			$this->page_id = $this->page_id ? $this->page_id : $this->request->controller();

			// Figure out what format the client wants
			$accept_types = Request::accept_type();
			if (isset($accept_types['*/*'])) {

				// All formats accepted
				$accept_types = $this->_accept_formats;

			} else {

				// Only some formats accepted
				$accept_types = Arr::extract($accept_types, array_keys($this->_accept_formats));
				if (!$accept_types = array_filter($accept_types)) {
					throw new HTTP_Exception_415('Unsupported accept type');
				}

			}
			$this->_response_format = key($accept_types);
<<<<<<< HEAD
=======

			// Try to autoload Kostache view layout, one view model for each controller
			/** @deprecated */
			if (!$this instanceof Controller_Template) {
				$directory = $this->request->directory() ? $this->request->directory() . '/' : '';
				$view_path = $directory . $this->request->controller() . '/' . Arr::get($this->_action_views, $this->request->action(), $this->request->action());
				$view_path = strtolower($view_path);

				$this->view = $this->_prepare_view($view_path, $this->_response_format);
			} else {
				$this->view = null;
			}

>>>>>>> 5b2899d... View class bases views to replace Kostache and Kohana templates
		}

	}


	/**
	 * Destroy controller.
	 */
	public function after() {
		if ($this->history && $this->response->status() < 400) {

			// Update breadcrumbs and history
			$uri = $this->request->current_uri();
			unset($this->breadcrumb[$uri]);

			// Limit to 10 items
			$this->breadcrumb = array_slice($this->breadcrumb, -9, 9, true);
			$this->breadcrumb[$uri] = $this->view->title;
			$this->session
				->set('history', $uri . ($_GET ? URL::query($_GET) : ''))
				->set('breadcrumb', $this->breadcrumb);
		}

		if ($this->auto_render) {

			// Require a view
			/*
			if ($this->view === null) {
				throw new HTTP_Exception_404('Page not found');
			}
			*/

			// Set headers
			$this->response->headers('Content-Type', $this->_response_format);

<<<<<<< HEAD
=======
			// Kostache
			/** @deprecated */
			if ($this->view instanceof Kostache) {
				if ($this->_request_type !== self::REQUEST_INITIAL) {

					// Render full layout only with initial request
					$this->view->render_layout = false;

				} else {

					// Set template values from controller, to be deprecated mostly
					$this->view->set('actions',  $this->page_actions);
					$this->view->set('class',    $this->page_class);
					$this->view->set('id',       $this->page_id);
					$this->view->set('language', $this->language);
					$this->view->set('subtitle', $this->page_subtitle);
					$this->view->set('tabs',     $this->tabs);
					$this->view->set('tab_id',   $this->tab_id);
					$this->view->set('title',    $this->page_title);

				}
			}

>>>>>>> 5b2899d... View class bases views to replace Kostache and Kohana templates
			// Set response
			$this->response->body($this->view);

		}
	}

<<<<<<< HEAD
=======

	/**
	 * Load proper template view based on requested format.
	 *
	 * @deprecated
	 * @param   string  $view_path
	 * @param   string  $response_format
	 * @return  Kostache|string|null
	 */
	protected function _prepare_view($view_path, $response_format = self::FORMAT_HTML) {

		// Validate response format
		if (($format_path = Arr::get($this->_accept_formats, $response_format)) === null) {
			return null;
		}

		// Include format path
		$full_view_path = trim($view_path . '/' . $format_path, '/');

		// Load template
		try {
			return Kostache::factory($full_view_path);
		} catch (Kohana_Exception $e) {

			// If no View class found, return static template
			if ($file = Kohana::find_file('templates', $full_view_path, 'mustache')) {
				return file_get_contents($file);
			}

			// If still not found, return template supporting deprecated views with Widgets
			return Kostache::factory('deprecated');

		}
	}

>>>>>>> 5b2899d... View class bases views to replace Kostache and Kohana templates
}
