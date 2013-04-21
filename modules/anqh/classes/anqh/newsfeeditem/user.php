<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User NewsfeedItem
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_NewsfeedItem_User extends NewsfeedItem {

	/**
	 * Changes new default image
	 *
	 * Data: image_id
	 */
	const TYPE_DEFAULT_IMAGE = 'default_image';

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
	 * @var  array  Aggregate types
	 */
	public static $aggregate = array(self::TYPE_FRIEND);


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

			case self::TYPE_DEFAULT_IMAGE:
		    $image = Model_Image::factory($item->data['image_id']);
		    if ($image->loaded()) {
			    $text = __('changed their default image');
		    }
		    break;

			case self::TYPE_FRIEND:
				if ($item->is_aggregate()) {
					if ($links = self::get_links($item)) {
						$text = __('added :friends as friends', array(':friends' => Text::implode_and($links)));
					}
				} else if ($link = self::get_link($item)) {
					$text = __('added :friend as a friend', array(':friend' => $item));
				}
				break;

			case self::TYPE_LOGIN:
				$text = __('logged in');
				break;

		}

		return $text;
	}


	/**
	 * Get anchor to newsfeed item target.
	 *
	 * @static
	 * @param   Model_NewsfeedItem  $item
	 * @return  string
	 */
	public static function get_link(Model_NewsfeedItem $item) {
		$text = '';
		switch ($item->type) {

			case self::TYPE_FRIEND:
				$friend = Model_User::find_user($item->data['friend_id']);
				if ($friend->loaded()) {
					$text = HTML::user($friend);
				}
				break;

		}

		return $text;
	}


	/**
	 * Change default image
	 *
	 * @static
	 * @param  Model_User   $user
	 * @param  Model_Image  $image
	 */
	public static function default_image(Model_User $user = null, Model_Image $image = null) {
		if ($user && $image) {
			parent::add($user, 'user', self::TYPE_DEFAULT_IMAGE, array('image_id' => $image->id));
		}
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
