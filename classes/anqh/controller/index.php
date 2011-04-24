<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Index controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Index extends Controller_Page {

	/**
	 * @var  string  Index id is home
	 */
	protected $page_id = 'home';


	/**
	 * Controller default action
	 */
	public function action_index() {

		// Newsfeed
		if (isset($_GET['newsfeed']) && $this->_request_type === Controller::REQUEST_AJAX) {
			echo $this->section_newsfeed();
			exit;
		}

		// Build page
		$this->view = View_Page::factory(__('Welcome to :site', array(':site' => Kohana::config('site.site_name'))));

		// Newsfeed
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_newsfeed());

		// Birthdays
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_birthdays());

		// Shouts
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_shouts());

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

		return $section;
	}


	/**
	 * Get newsfeed.
	 *
	 * @return  View_Newsfeed
	 */
	public function section_newsfeed() {
		$section = new View_Newsfeed();
		$section->type = Arr::get($_REQUEST, 'newsfeed', View_Newsfeed::TYPE_ALL);

		return $section;
	}


	/**
	 * Get online users.
	 *
	 * @return  View_Users_Online
	 */
	public function section_online() {
		$section = new View_Users_Online();

		return $section;
	}


	/**
	 * Get shouts.
	 *
	 * @return  View_Shouts
	 */
	public function section_shouts() {
		$section = new View_Index_Shouts();

		return $section;
	}

}
