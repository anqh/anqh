<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Users page.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_View_Users_Index extends View_Layout {

	/**
	 * Var method for birthdays.
	 *
	 * @return  View_Users_Birthdays
	 */
	public function view_birthdays() {
		$view_birthdays = new View_Users_Birthdays();
		$view_birthdays->limit = 0;

		return $view_birthdays;
	}


	/**
	 * Var method for view_online.
	 *
	 * @return  View_Users_Online
	 */
	public function view_online() {
		return New View_Users_Online();
	}


	/**
	 * Var method for new users.
	 *
	 * @return  View_Users_New
	 */
	public function view_newusers() {
		$view_newusers = new View_Users_New();
		$view_newusers->role  = 'main';

		return $view_newusers;
	}

}
