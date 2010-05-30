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
	 * Return model specific url
	 *
	 * @param  Jelly_Model  $model
	 */
	public static function model(Jelly_Model $model) {
		return Jelly::model_name($model) . '/' . self::title($model->id() . ' ' . $model->name());
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
			return 'member/' . urlencode($user);
		}

		return null;
	}


}
