<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Online users view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Users_Online extends View_Section {


	/**
	 * @var  array  Friends online
	 */
	private $_friends;

	/**
	 * @var  integer  Number of guests online
	 */
	private $_guest_count;

	/**
	 * @var  string  Box id
	 */
	public $id = 'online';

	/**
	 * @var  array  Users online
	 */
	private $_users;


	/**
	 * Initialize Online.
	 */
	public function _initialize() {
		$this->_guest_count = Model_User_Online::get_guest_count();
		$users              = Model_User_Online::find_online_users();
		$this->title        = __('Online') . ' <span>(' . ($this->_guest_count + count($users)) . ')</span>';

		// Build user lists
		$this->_friends = $this->_users = array();
		$friends = (bool)self::$user ? self::$user->find_friends() : array();
		foreach ($users as $user_id) {
			$user = Model_User::find_user_light($user_id);
			if (in_array($user_id, $friends)) {
				$this->_friends[mb_strtoupper($user['username'])] = HTML::user($user);
			} else {
				$this->_users[mb_strtoupper($user['username'])] = HTML::user($user);
			}
		}
	}


	/**
	 * Var method for guest_count.
	 *
	 * @return  integer
	 */
	public function guest_count() {
		return $this->_guest_count;
	}


	/**
	 * Var method for friends.
	 *
	 * @return  array
	 */
	public function friends() {
		return array_values($this->_friends);
	}


	/**
	 * Var method for users.
	 *
	 * @return  array
	 */
	public function users() {
		return array_values($this->_users);
	}

}
