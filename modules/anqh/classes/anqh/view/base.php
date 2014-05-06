<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View element base class.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_View_Base {

	/**
	 * @var  string  DOM element class
	 */
	public $class;

	/**
	 * @var  string  DOM element id
	 */
	public $id;

	/**
	 * @var  boolean  Is the view initialized, e.g. static variables
	 */
	private static $_initialized = false;

	/**
	 * @var  string  Render result
	 */
	protected $_render = '';

	/**
	 * @var  integer  Request type
	 */
	protected static $_request_type = Controller::REQUEST_INITIAL;


	/**
	 * Create new View class.
	 */
	public function __construct() {

		// Initialize static variables
		if (!self::$_initialized) {

			// Request type
			if (Request::current()->is_ajax()) {
				self::$_request_type = Controller::REQUEST_AJAX;
			} else if (!Request::current()->is_initial()) {
				self::$_request_type = Controller::REQUEST_INTERNAL;
			}

			self::$_initialized = true;
		}

	}


	/**
	 * Executed before rendering.
	 */
	public function before() {}


	/**
	 * Executed after rendering.
	 *
	 * @see  _render
	 */
	public function after() {}


	/**
	 * Factory method.
	 *
	 * @return  Anqh_View_Base
	 */
	public static function factory() {
		$view = get_called_class();

		return new $view;
	}


	/**
	 * Render View Model.
	 *
	 * @return  string
	 */
	abstract public function render();


	/**
	 * Return view class as string.
	 *
	 * @return  string
	 * @uses    Anhq_View_Model::render()
	 */
	public function __toString() {
		try {

			// Render view
			$this->before();
			$this->_render = $this->render();
			$this->after();

			return $this->_render;

		} catch (Exception $e) {

			// Display the exception message only if not in production
			ob_start();
			Anqh_Exception::handler($e);

			if (Kohana::$environment === Kohana::PRODUCTION) {
				ob_end_clean();
				return __('An error occured and has been logged.');
			} else {
				return ob_get_clean();
			}

		}
	}

}
