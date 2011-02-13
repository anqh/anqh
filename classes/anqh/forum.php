<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Forum {

	/**
	 * Forum version
	 */
	const VERSION = 0.5;


	/**
	 * Find new private messages
	 *
	 * @static
	 * @param   Model_User $user
	 * @return  array
	 */
	public static function find_new_private_messages(Model_User $user) {
		return Jelly::select('forum_private_recipient')->where('user_id', '=', $user->id)->and_where('unread', '>', 0)->execute();
	}


	/**
	 * Get private messages URL
	 *
	 * @static
	 * @return  string
	 */
	public static function private_messages_url() {
		return Route::get('forum_private_area')->uri();
	}

}
