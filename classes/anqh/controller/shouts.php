<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Shouts controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Shouts extends Controller_Template {

	/**
	 * Controller default action
	 */
	public function action_index() {
		$view = View_Module::factory('generic/shout', array(
			'mod_title' => __('Shouts'),
			'shouts'    => Model_Shout::find_latest(50),
			'can_shout' => Permission::has(new Model_Shout, Model_Shout::PERMISSION_CREATE),
			'errors'    => array(),
		));

		Widget::add('main', $view);
	}


	/**
	 * Action: shout
	 */
	public function action_shout() {
		$shout = Model_Shout::factory();
		$errors = array();

		if (Permission::has($shout, Permission_Interface::PERMISSION_CREATE) && Security::csrf_valid()) {
			$shout->author = self::$user;
			$shout->shout  = $_POST['shout'];
			try {
				$shout->save();
				if (!$this->ajax) {
					$this->request->redirect(Route::get('shouts')->uri());
				}
			} catch (Validate_Exception $e) {
				$errors = $e->array->errors('validate');
			}
		}

		$shouts = Model_Shout::find_latest(10);
		$view = View_Module::factory('generic/shout', array(
			'mod_title' => __('Shouts'),
			'shouts'    => $shouts,
			'can_shout' => Permission::has($shout, Model_Shout::PERMISSION_CREATE),
			'errors'    => $errors,
		));

		if ($this->ajax) {
			echo $view;
		} else {
			Widget::add('side', $view);
		}
	}

}
