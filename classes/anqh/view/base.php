<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View element base class
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
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
	 * @var  Model_User  Current authenticated user, if any
	 */
	protected static $_user = null;

	/**
	 * @var  integer  Current authenticated user id, if any
	 */
	protected static $_user_id = null;


	/**
	 * Create new View class.
	 */
	public function __construct() {

		// Initialize static variables
		if (!self::$_initialized) {

			// Viewing user
			if (self::$_user = Visitor::instance()->get_user()) {
				self::$_user_id = self::$_user->id;
			}

			self::$_initialized = true;
		}

	}


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
	 * Return View Model as string.
	 *
	 * @return  string
	 * @uses    Anhq_View_Model::render()
	 */
	public function __toString() {
		try {
			return $this->render();
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
