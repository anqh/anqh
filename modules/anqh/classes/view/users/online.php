<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Online users view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Users_Online extends View_Section {

	/**
	 * @var  array  Friends online
	 */
	private $_friends = array();

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
	private $_users = array();


	/**
	 * Create new shouts view.
	 */
	public function __construct() {
		parent::__construct();

//		$this->_guest_count = Model_User_Online::get_guest_count();
		$users              = Model_User_Online::find_online_users();
		$this->title        = __('Online');

		// Build user lists
		$friends = (bool)Visitor::$user ? Visitor::$user->find_friends() : array();
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
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		// Friends
		echo implode(', ', $this->_friends);

		if ($this->_friends && $this->_users):
			echo '<br>';
		endif;

		// Others
		echo implode(', ', $this->_users);

		// Guests
		/*
		if ($this->_guest_count):
			if ($this->_friends || $this->_users):
				echo ' ' . __('and') . ' ';
			endif;

			echo __(':count guests', array(':count' => $this->_guest_count));
		endif;
		*/

		return ob_get_clean();
	}

}
