<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Users_Friends
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Users_Friends extends View_Section {

	/**
	 * @var  Model_User
	 */
	public $user;


	/**
	 * Create new view.
	 *
	 * @param  Model_User  $user
	 */
	public function __construct($user = null) {
		parent::__construct();

		$this->title = __('Friends');
		$this->user  = $user;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		$friends = array();
	  foreach ($this->user->find_friends() as $friend_id) {
		  $friend = Model_User::find_user_light($friend_id);
		  $friends[$friend['username']] = $friend;
	  }
	  ksort($friends, SORT_LOCALE_STRING);


?>

<ul class="unstyled">
	<?php foreach ($friends as $friend): ?>

	<li class="row-fluid">
		<?= HTML::avatar($friend['avatar'], $friend['username']) ?>
		<?= HTML::user($friend) ?>
		<?php if ($friend['last_login']) echo '<small class="ago">' . HTML::time(Date::short_span($friend['last_login'], true, true), $friend['last_login']) . '</small>'; ?>
	</li>
	<?php endforeach; ?>

</ul>


<?php

		return ob_get_clean();
	}

}
