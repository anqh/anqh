<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Index controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
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
		Anqh::page_meta('type', 'website');

		// Newsfeed
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_newsfeed());

		// News
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_news());

		// Shouts
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_shouts());

		// Birthdays or friend suggestions
		if (self::$user && rand(0, 10) < 5) {
			$this->view->add(View_Page::COLUMN_RIGHT, $this->section_friend_suggestions());
		} else {
			$this->view->add(View_Page::COLUMN_RIGHT, $this->section_birthdays());
		}

		// Online
//		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_online());

	}


	/**
	 * Get birthdays.
	 *
	 * @return  View_Users_Birthdays
	 */
	public function section_birthdays() {
		$section = new View_Users_Birthdays();
		$section->aside = true;

		return $section;
	}


	/**
	 * Get friend suggestions.
	 *
	 * @return  View_Users_FriendSuggestions
	 */
	public function section_friend_suggestions() {
		$section = new View_Users_FriendSuggestions(self::$user, 5);
		$section->aside = true;

		return $section;
	}


	/**
	 * Get site news.
	 *
	 * @return  View_Topics_List
	 */
	public function section_news() {
		$section = new View_Topics_List(Model_Forum_Topic::factory()->find_news(5));
		$section->title = __('News');
		$section->aside = true;

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
		$section->title = __("What's happening");

		return $section;
	}


	/**
	 * Get online users.
	 *
	 * @return  View_Users_Online
	 */
	public function section_online() {
		$section = new View_Users_Online();
		$section->aside = true;

		return $section;
	}


	/**
	 * Get shouts.
	 *
	 * @return  View_Index_Shouts
	 */
	public function section_shouts() {
		$section = new View_Index_Shouts();
		$section->aside = true;

		return $section;
	}

}
