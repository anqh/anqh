<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * New users view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Users_New extends View_Section {

	/**
	 * @var  integer  Max number of users
	 */
	public $limit = 50;


	/**
	 * Initialize New users.
	 */
	public function _initialize() {
		$this->title = __('New users');
	}


	/**
	 * Var method for new_users.
	 *
	 * @return  array
	 */
	public function new_users() {
		$dates     = array();
		foreach (Model_User::find_new_users($this->limit) as $user_id => $stamp) {
			$user = Model_User::find_user_light($user_id);
			$dates[Date::format(Date::DMY_SHORT, $stamp)][] = array(
				'avatar' => HTML::avatar($user['avatar'], $user['username']),
				'user'   => HTML::user($user),
				'time'   => Date::format(Date::TIME, $stamp),
			);
		}

		$new_users = array();
		foreach ($dates as $date => $users) {
			$new_users[] = array(
				'date'  => $date,
				'users' => $users,
			);
		}

		return $new_users;
	}

}
