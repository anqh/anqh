<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Users controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Users extends Controller_Page {

	/**
	 * @var  string
	 */
	protected $page_id = 'members';


	/**
	 * Controller default action
	 */
	public function action_index() {

		// Build page
		$this->view = View_Page::factory(__('New members'));

		// New users
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_new_users());

		// Birthdays
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_birthdays());

		// Online
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_online());

	}


	/**
	 * Get birthdays.
	 *
	 * @return  View_Users_Birthdays
	 */
	public function section_birthdays() {
		$section = new View_Users_Birthdays();
		$section->limit = 0;

		return $section;
	}


	/**
	 * Get new users.
	 *
	 * @return  View_Users_New
	 */
	public function section_new_users() {
		return new View_Users_New();
	}


	/**
	 * Get online users.
	 *
	 * @return  View_Users_Online
	 */
	public function section_online() {
		return new View_Users_Online();
	}

}
