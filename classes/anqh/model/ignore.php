<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Ignore model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Ignore extends AutoModeler {

	protected $_table_name = 'friends';

	protected $_data = array(
		'id'        => null,
		'user_id'   => null,
		'ignore_id' => null,
		'created'   => null,
	);


	/**
	 * Add new ignore.
	 *
	 * @static
	 * @param   integer  $user_id
	 * @param   integer  $ignore_id
	 * @return  boolean
	 */
	public static function add($user_id, $ignore_id) {
		try {
			$ignore = new Model_Friend();
			$ignore->set_fields(array(
				'user_id'   => $user_id,
				'ignore_id' => $ignore_id,
				'created'   => time(),
			));
			$ignore->save();

			Anqh::cache_delete('ignores_' . $user_id);
			Anqh::cache_delete('ignorers_' . $ignore_id);

			return true;
		} catch (Exception $e) {
			return false;
		}
	}


	/**
	 * Find ids of users who have ignored the user.
	 *
	 * @static
	 * @param   integr  $friend_id
	 * @return  array
	 */
	public static function find_by_ignorer($ignore_id) {
		$ignore_id = (int)$ignore_id;
		$ckey = 'ignorers_' . $ignore_id;

		// Try static cache
		$ignores = Anqh::cache_get($ckey);
		if (true || is_null($ignores)) {

			// Load from DB
			$ignores = (array)DB::select('user_id')
				->from('ignore')
				->where('ignore_id', '=', $ignore_id)
				->execute()
				->as_array(null, 'user_id');

			Anqh::cache_set($ckey, $ignores, Date::HOUR);
		}

		return $ignores;
	}


	/**
	 * Find ids of users the user has ignored.
	 *
	 * @static
	 * @param   integr  $user_id
	 * @return  array
	 */
	public static function find_by_user($user_id) {
		$user_id = (int)$user_id;
		$ckey = 'ignores_' . $user_id;

		// Try static cache
		$ignores = Anqh::cache_get($ckey);
		if (is_null($ignores)) {

			// Load from DB
			$friends = (array)DB::select('ignore_id')
				->from('ignores')
				->where('user_id', '=', $user_id)
				->execute()
				->as_array(null, 'ignore_id');

			Anqh::cache_set($ckey, $ignores, Date::HOUR);
		}

		return $ignores;
	}


	/**
	 * Delete ignore.
	 *
	 * @static
	 * @param   integer  $user_id
	 * @param   integer  $ignore_id
	 * @return  boolean
	 */
	public static function unignore($user_id, $ignore_id) {
		$deleted = DB::delete('ignores')
			->where('user_id', '=', $user_id)
			->and_where('ignore_id', '=', $ignore_id)
			->execute();

		Anqh::cache_delete('ignores_' . $user_id);
		Anqh::cache_delete('ignored_by_' . $ignore_id);

		return (bool)$deleted;
	}

}
