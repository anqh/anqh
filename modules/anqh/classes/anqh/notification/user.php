<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Notification_User
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Notification_User extends Notification {

	/** Added as friends */
	const TYPE_FRIEND = 'friend';


	/**
	 * Get notification as HTML.
	 *
	 * @static
	 * @param   Model_Notification
	 * @return  string
	 */
	public static function get(Model_Notification $notification) {
		$text = '';
		switch ($notification->type) {

			case self::TYPE_FRIEND:
				$friend = Model_User::find_user($notification->user_id);
				if ($friend->loaded()) {
					$text = __(':friend added you as a friend', array(':friend' => HTML::user($friend)));
				}
				break;

		}

		return $text;
	}


	/**
	 * Added as a friends.
	 *
	 * @static
	 * @param  Model_User  $user
	 * @param  Model_User  $friend
	 */
	public static function friend(Model_User $user = null, Model_User $friend = null) {
		if ($user && $friend) {
			parent::add($user, $friend, 'user', self::TYPE_FRIEND);
		}
	}

}
