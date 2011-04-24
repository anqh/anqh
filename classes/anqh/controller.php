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
	 * @var  View_Page  Page class
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

			// Set response
			$this->response->body($this->view);

		}
	}

}
