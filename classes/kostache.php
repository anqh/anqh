<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Kostache
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Kostache extends Kohana_Kostache {

	/**
	 * @var  boolean  AJAX request
	 */
	public static $ajax = false;

	/**
	 * @var  array  View routes
	 */
	protected $_routes = array();

	/**
	 * @var  Model_User  Current user
	 */
	public static $user = false;


	/**
	 * Loads the template and partial paths.
	 *
	 * @param  string  $template  template path
	 * @param  array   $partials  partial paths
	 */
	public function __construct($template = null, array $partials = null) {
		parent::__construct($template, $partials);

		// Initialize view
		$this->_initialize();
	}


	/**
	 * Helper for easier initialization.
	 */
	protected function _initialize() {}


	/**
	 * Var method for internationalization.
	 *
	 * @return  closure
	 *
	 * @todo  Not supported by Kostache?
	 */
	public function i18n() {
		return function($string) {
			return __($string);
		};
	}


	/**
	 * Var method to check if a user is logged in.
	 *
	 * @return  boolean
	 */
	public function is_logged() {
		return (bool)self::$user;
	}


	/**
	 * Renders the template using the current view.
	 *
	 * @return  string
	 */
	public function render() {

		// Start benchmark
		if (Kohana::$profiling === true and class_exists('Profiler', false)) {
			$benchmark = Profiler::start('View', __METHOD__ . '(' . get_called_class() .')');
		}

		$render = parent::render();

		// Stop benchmark
		if (isset($benchmark)) {
			Profiler::stop($benchmark);
		}

		return $render;
	}


	/**
	 * Var method for routes.
	 *
	 * @return  array
	 */
	public function routes() {
		return $this->_routes;
	}

}
