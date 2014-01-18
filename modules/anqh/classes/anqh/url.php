<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * URL helper
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_URL extends Kohana_URL {

	/**
	 * Transforms an database id to file path, 1234567 = 01/23/45
	 *
	 * @param  integer  $id
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
	 * @param   mixed   $user   true for session user
	 * @param   string  $action
	 * @return  string
	 */
	public static function user($user, $action = null) {
		static $_visitor;

		if (is_numeric($user) && (int)$user > 0) {

			// User id given
			if ($user = Model_User::find_user($user)) {
				$user = $user->username;
			}

		} else if ($user instanceof Model_User) {

			// Model_User given
			$user = $user->username;

		} else if (is_array($user) && isset($user['username'])) {

			// Light user array given
			$user = $user['username'];

		} else if ($user === true) {

			// Use session user
			if ($_visitor === null) {
				if ($user = Visitor::instance()->get_user()) {
					$_visitor = $user->username;
				} else {

					// No session user available
					$_visitor = false;

				}
			}
			$user = $_visitor;

		}

		// Username available
		if (is_string($user)) {
			return Route::url('user', array('username' => urlencode($user), 'action' => $action));
		}

		return null;
	}


}
