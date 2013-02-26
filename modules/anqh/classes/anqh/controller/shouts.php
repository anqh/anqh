<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Shouts controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Shouts extends Controller_Page {

	/**
	 * Action: index
	 */
	public function action_index() {

		// Build page
		$this->view = View_Page::factory(__('Shouts'));

		// Shouts
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_shouts());

	}


	/**
	 * Action: shout
	 */
	public function action_shout() {
		$shout = Model_Shout::factory();

		if (Permission::has($shout, Permission_Interface::PERMISSION_CREATE) && Security::csrf_valid()) {
			$shout->author_id = self::$user->id;
			$shout->shout     = $_POST['shout'];
			$shout->created   = time();
			try {
				$shout->save();
			} catch (Validation_Exception $e) {
			}
		}

		if ($this->ajax) {
			$this->response->body($this->section_shouts());

			return;
		}

		$this->request->redirect(Route::get('shouts')->uri());
	}


	/**
	 * Get shouts.
	 *
	 * @return  View_Index_Shouts
	 */
	public function section_shouts() {
		$section = new View_Index_Shouts();

		if (!$this->ajax) {
			$section->title = null;
		}

		return $section;
	}

}
