<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * New users view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Users_New extends View_Section {

	/**
	 * @var  integer  Max number of users
	 */
	public $limit = 50;


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		$dates = array();
		foreach (Model_User::find_new_users($this->limit) as $user_id => $stamp):
			$user = Model_User::find_user_light($user_id);
			$dates[Date::format(Date::DMY_SHORT, $stamp)][] = array(
				'user'  => $user,
				'stamp' => $stamp,
			);
		endforeach;

		ob_start();

		foreach ($dates as $date => $users):

?>

<h4><?= $date ?></h4>
<ul class="block-grid three-up">
	<?php foreach ($users as $user): ?>
	<li>
		<?= HTML::avatar($user['user']['avatar'], $user['user']['username']), ' ', HTML::user($user['user']) ?><br />
		<time class="muted"><?= Date::format(Date::TIME, $user['stamp']) ?></time>
	</li>
	<?php endforeach; ?>
</ul>

<?php

		endforeach;

		return ob_get_clean();
	}

}
