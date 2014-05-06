<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Notifications controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Notifications extends Controller_Page {

	public function before() {
		parent::before();

		// Authentication required
		if (!Visitor::$user) {
			$this->request->redirect(URL::site());
		}

	}


	/**
	 * Controller default action
	 */
	public function action_index() {

		// Dismiss notification?
		if ($dismiss = (int)Arr::get($_REQUEST, 'dismiss')) {
			$notification = Model_Notification::factory($dismiss);

			if ($notification->loaded()) {
				Permission::required($notification, Model_Notification::PERMISSION_DELETE, Visitor::$user);

				$notification->delete();

/*				if ($this->_request_type == self::REQUEST_AJAX) {
					$this->response->body('');

					return;
				}*/
			}
		}

		$section = $this->section_notifications(Notification::get_notifications(Visitor::$user));

		if ($this->_request_type == self::REQUEST_AJAX) {
			$this->response->body($section);
		} else {
			$this->view = new View_Page('Notifications');
			$this->view->add(View_Page::COLUMN_CENTER, $section);
		}
	}


	/**
	 * Get notifications.
	 *
	 * @param   array  $notifications
	 * @return  View_Notifications
	 */
	public function section_notifications(array $notifications) {
		return new View_Notifications($notifications);
	}

}
