<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Shouts controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Shouts extends Controller_Page {

	/**
	 * Action: index
	 */
	public function action_index() {}


	/**
	 * Action: shout
	 */
	public function action_shout() {
		$shout  = Model_Shout::factory();
		$errors = array();

		if (Permission::has($shout, Permission_Interface::PERMISSION_CREATE) && Security::csrf_valid()) {
			$shout->author_id = self::$user->id;
			$shout->shout     = $_POST['shout'];
			$shout->created   = time();
			try {
				$shout->save();
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		if ($this->ajax) {
			echo new View_Index_Shouts();
			exit;
		}

		$this->request->redirect(Route::get('shouts')->uri());
	}

}
