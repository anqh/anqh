<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Notification
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Notification {

	/**
	 * Add a Notification.
	 *
	 * @static
	 * @param   Model_User  $user
	 * @param   Model_User  $target
	 * @param   string      $class  e.g. 'user'
	 * @param   string      $type   e.g. 'login'
	 * @param   integer     $data_id
	 * @param   string      $text   extra data
	 * @return  boolean
	 */
	protected static function add(Model_User $user, Model_User $target, $class, $type, $data_id = null, $text = null) {
		$notification = new Model_Notification();
		$notification->set_fields(array(
			'user_id'   => $user->id,
			'target_id' => $target->id,
			'class'     => $class,
			'type'      => $type,
			'data_id'   => $data_id,
			'text'      => $text,
			'stamp'     => time(),
		));

		if (!Permission::has($notification, Model_Notification::PERMISSION_CREATE, $user)) {
			return false;
		}

		$notification->save();

		return $notification->loaded();
	}


	/**
	 * Get user's notifications.
	 *
	 * @param   Model_User  $target
	 * @return  array
	 */
	public static function get_notifications(Model_User $target) {
		$notifications = array();

		foreach (Model_Notification::factory()->find_by_target($target) as $notification) {

			// Ignored?
			if ($target->is_ignored($notification->user_id)) {
				$target->delete();

				continue;
			}

			$class = 'Notification_' . $notification->class;
			if (method_exists($class, 'get') && $text = call_user_func(array($class, 'get'), $notification)) {
				$notifications[$notification->id] = $text;
			}
		}

		return $notifications;
	}
}
