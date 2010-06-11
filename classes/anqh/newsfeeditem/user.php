<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User NewsfeedItem
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_NewsfeedItem_User extends NewsfeedItem implements NewsfeedItem_Interface {

	/**
	 * Add a user to friends
	 *
	 * Data: friend_id
	 */
	const TYPE_FRIEND = 'friend';

	/**
	 * Login event
	 */
	const TYPE_LOGIN = 'login';


	/**
	 * Get newsfeed item as HTML
	 *
	 * @static
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get(Model_NewsFeedItem $item) {
		$text = '';
		switch ($item->type) {

			case self::TYPE_FRIEND:
				$friend = Jelly::select('user')->load($item->data['friend_id']);
				if ($friend->loaded()) {
					$text = __('added :friend as a friend', array(':friend' => HTML::user($friend)));
				}
				break;

			case self::TYPE_LOGIN:
				$text = __('logged in');
				break;

		}

		return $text;
	}


	/**
	 * Add a user to friends
	 *
	 * @static
	 * @param  Model_User  $user
	 * @param  Model_User  $friend
	 */
	public static function friend(Model_User $user = null, Model_User $friend = null) {
		if ($user && $friend) {
			parent::add($user, 'user', self::TYPE_FRIEND, array('friend_id' => $friend->id));
		}
	}


	/**
	 * Add new login event
	 *
	 * @static
	 * @param  Model_User  $user
	 */
	public static function login(Model_User $user = null) {
		if ($user) {
			parent::add($user, 'user', self::TYPE_LOGIN);
		}
	}

}
