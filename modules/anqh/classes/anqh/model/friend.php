<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Friend model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Friend extends AutoModeler {

	protected $_table_name = 'friends';

	protected $_data = array(
		'id'        => null,
		'user_id'   => null,
		'friend_id' => null,
		'created'   => null,
	);


	/**
	 * Add new friend.
	 *
	 * @static
	 * @param   integer  $user_id
	 * @param   integer  $friend_id
	 * @return  boolean
	 */
	public static function add($user_id, $friend_id) {
		try {
			$friend = new Model_Friend();
			$friend->set_fields(array(
				'user_id'   => $user_id,
				'friend_id' => $friend_id,
				'created'   => time(),
			));
			$friend->save();

			Anqh::cache_delete('friends_' . $user_id);
			Anqh::cache_delete('friends_of_' . $friend_id);
			Anqh::cache_delete('friends_suggestions_' . $user_id);
			Anqh::cache_delete('friends_suggestions_' . $friend_id);

			return true;
		} catch (Exception $e) {
			return false;
		}
	}


	/**
	 * Find ids of users who have added the user as a friend.
	 *
	 * @static
	 * @param   integer  $friend_id
	 * @return  array
	 */
	public static function find_by_friend($friend_id) {
		$friend_id = (int)$friend_id;
		$ckey = 'friends_of_' . $friend_id;

		// Try static cache
		$friends = Anqh::cache_get($ckey);
		if (true || is_null($friends)) {

			// Load from DB
			$friends = (array)DB::select('user_id')
				->from('friends')
				->where('friend_id', '=', $friend_id)
				->execute()
				->as_array(null, 'user_id');

			Anqh::cache_set($ckey, $friends, Date::HOUR);
		}

		return $friends;
	}


	/**
	 * Find ids of users the user hasn't added as a friend.
	 *
	 * @static
	 * @param   integer  $user_id
	 * @param   integer  $common_friends  Minimum number
	 * @return  array
	 */
	public static function find_by_suggestion($user_id, $common_friends = 2) {
		$user_id = (int)$user_id;
		$ckey    = 'friends_suggestions_' . $user_id;

		// Try static cache
		$results = Anqh::cache_get($ckey);
		if (is_null($results)) {

			// Current friends
			$friends = self::find_by_user($user_id);

			// Load from DB
			$suggestions = (array)DB::select('friend_id', array(DB::expr('SUM(1)'), 'score'))
				->from('friends')

				// Get our friend's friends
				->where('user_id', 'IN', $friends)

				// Who aren't my friends
				->and_where('friend_id', 'NOT IN', $friends)

				// And aren't me
				->and_where('friend_id', '!=', $user_id)

				->group_by('friend_id')
				->having(DB::expr('SUM(1)'), '>', (int)$common_friends)
				->order_by('score', 'DESC')
				->limit(200)
				->execute()
				->as_array('friend_id', 'score');

			$results = array();
			foreach ($suggestions as $user_id => $score) {
				$results[(int)$user_id] = (int)$score;
			}

			Anqh::cache_set($ckey, $results, Date::HOUR);
		}

		return $results;
	}


	/**
	 * Find ids of users the user has added as a friend.
	 *
	 * @static
	 * @param   integer  $user_id
	 * @return  array
	 */
	public static function find_by_user($user_id) {
		$user_id = (int)$user_id;
		$ckey = 'friends_' . $user_id;

		// Try static cache
		$friends = Anqh::cache_get($ckey);
		if (is_null($friends)) {

			// Load from DB
			$friends = (array)DB::select('friend_id')
				->from('friends')
				->where('user_id', '=', $user_id)
				->execute()
				->as_array(null, 'friend_id');

			Anqh::cache_set($ckey, $friends, Date::HOUR);
		}

		return $friends;
	}


	/**
	 * Delete friendship.
	 *
	 * @static
	 * @param   integer  $user_id
	 * @param   integer  $friend_id
	 * @return  boolean
	 */
	public static function unfriend($user_id, $friend_id) {
		$deleted = DB::delete('friends')
			->where('user_id', '=', $user_id)
			->and_where('friend_id', '=', $friend_id)
			->execute();

		Anqh::cache_delete('friends_' . $user_id);
		Anqh::cache_delete('friends_of_' . $friend_id);

		return (bool)$deleted;
	}

}
