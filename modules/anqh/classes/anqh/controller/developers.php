<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Developers controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Developers extends Controller_Page {

	/**
	 * Construct controller.
	 */
	public function before() {
		parent::before();

		$this->page_title = 'Developers';
	}


	/**
	 * Default action.
	 */
	public function action_index() {
		$this->view = new View_Page('Developers');

		// Docs
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_introduction());
		$this->view->add(View_Page::COLUMN_CENTER, $this->section_api());

		// Side bar
		$this->view->add(View_Page::COLUMN_RIGHT, $this->section_menu());

	}


	/**
	 * API docs.
	 *
	 * @return  View_Developers_API
	 */
	public function section_api() {
		return new View_Developers_API();
	}


	/**
	 * Introduction.
	 *
	 * @return  View_Developers_Introduction
	 */
	public function section_introduction() {
		return new View_Developers_Introduction();
	}


	/**
	 * Doc menu.
	 *
	 * @return  View_Developers_Menu
	 */
	public function section_menu() {
		$section = new View_Developers_Menu();
		$section->aside = true;

		return $section;
	}

}
