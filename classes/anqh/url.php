<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * URL helper
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_URL extends Kohana_URL {

	/**
	 * Transforms an database id to file path, 1234567 = 01/23/45
	 *
	 * @param  int $id
	 * @return string
	 */
	public static function id($id) {

		// Convert numeric id to hex and split to chunks of 2
		$path = str_split(sprintf('%08x', (int)$id), 2);

		// Scrap the last chunk, 256 files per dir
		array_pop($path);

		return implode('/', $path);
	}


	/**
	 * Get URL for user
	 *
	 * @param   mixed  $user
	 * @return  string
	 */
	public static function user($user) {

		// User id given
		if (is_numeric($user) && (int)$user > 0) {
			$user = Model_User::find_user($user);
		}

		// Model_User given
		if ($user instanceof Model_User) {
			$user = $user->username;
		} else if (is_array($user) && isset($user['username'])) {
			$user = $user['username'];
		}

		// Username given
		if (is_string($user)) {
			return Route::get('user')->uri(array('username' => urlencode($user)));
		}

		return null;
	}


}
