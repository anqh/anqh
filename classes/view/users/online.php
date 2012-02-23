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
	 * Create new shouts view.
	 */
	public function __construct() {
		parent::__construct();

		$this->_guest_count = Model_User_Online::get_guest_count();
		$users              = Model_User_Online::find_online_users();
		$this->title        = __('Online');

		// Build user lists
		$this->_friends = $this->_users = array();
		$friends = (bool)self::$_user ? self::$_user->find_friends() : array();
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

?>

<ul class="unstyled">
	<?php foreach ($this->_friends as $user) { ?>
	<li><?php echo $user ?></li>
	<?php } ?>
</ul>

<ul class="unstyled">
	<?php foreach ($this->_users as $user) { ?>
	<li><?php echo $user ?></li>
	<?php } ?>
	<li><?php echo __('and :count guests', array(':count' => $this->_guest_count)) ?></li>
</ul>

<?php

		return ob_get_clean();
	}

}
