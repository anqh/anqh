<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Abstract Anqh controller
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Controller extends Kohana_Controller {

	/**
	 * @var  boolean  AJAX-like request
	 */
	protected $ajax = false;

	/**
	 * Internal request?
	 *
	 * @var  boolean
	 */
	protected $internal = false;

	/**
	 * Current language
	 *
	 * @var  string
	 */
	protected $language = 'en';

	/**
	 * User Model
	 *
	 * @var  User_Model
	 */
	protected static $user = false;


	/**
	 * Construct controller
	 */
	public function before() {
		parent::before();

		// Check if this was an interna request or direct
		$this->internal = $this->request !== Request::instance();

		// Ajax request?
		$this->ajax = Request::$is_ajax;

		// Load current user, null if none
		if (self::$user === false) {
			self::$user = Visitor::instance()->get_user();
		}

	}

}
