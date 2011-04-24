<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View model for Shouts.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_Shouts_Index extends View_Layout {

	/**
	 * Var method for page title.
	 *
	 * @return  string
	 */
	public function title() {
		return __('Welcome to :site', array(':site' => Kohana::config('site.site_name')));
	}


	/**
	 * Var method for shouts.
	 *
	 * @return  View_Index_Shouts
	 */
	public function view_shouts() {
		$view = new View_Shouts_Shouts();
		$view->limit = 50;
		$view->role  = 'main';

		return $view;
	}

}
