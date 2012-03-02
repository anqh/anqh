<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Users_List
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Users_List extends View_Article {

	/**
	 * @var  array
	 */
	public $users;


	/**
	 * Create new view.
	 *
	 * @param  array  $users
	 */
	public function __construct(array $users = null) {
		parent::__construct();

		$this->users = $users;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {

		// Build short (friends) and long (others) user list
		$short = $long = array();
		$total = count($this->users);
		foreach ($this->users as $user):
			$user = is_array($user) ? $user : Model_User::find_user_light($user);
			if ($total < 11 || self::$_user && self::$_user->is_friend($user)):
				$short[mb_strtoupper($user['username'])] = HTML::user($user);
			else:
				$long[mb_strtoupper($user['username'])] = HTML::user($user);
			endif;
		endforeach;
		ksort($long);

		// If no friends, pick random from long
		if (empty($short) && !empty($long)):
			$shorts = (array)array_rand($long, min(10, count($long)));
			foreach ($shorts as $move):
				$short[$move] = $long[$move];
				unset($long[$move]);
			endforeach;
		endif;
		ksort($short);


		ob_start();

		if (count($short)) echo implode(', ', $short);

		if (count($long)):
			echo __('and'), ' ', HTML::anchor(
				'#users',
				__(count($long) == 1 ? ':count other' : ':count others', array(':count' => count($long))),
				array('class' => 'expander', 'title' => __('Show all'), 'onclick' => '$(this).next(".long").toggle("fast"); return false;')
			);
			echo '<div class="long">', implode(', ', $long), '</div>';
		endif;

		return ob_get_clean();
	}

}
