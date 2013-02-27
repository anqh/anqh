<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Users_Friends
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Users_FriendSuggestions extends View_Section {

	/**
	 * @var  integer
	 */
	public $limit;

	/**
	 * @var  Model_User
	 */
	public $user;


	/**
	 * Create new view.
	 *
	 * @param  Model_User  $user
	 */
	public function __construct($user = null, $limit = 10) {
		parent::__construct();

		$this->title = __("Why can't we be friends?");
		$this->user  = $user;
		$this->limit = (int)$limit;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		$friends     = array();
		$suggestions = $this->user->find_friend_suggestions();
		if ($suggestions):
			$randoms = array_rand($suggestions, min($this->limit, count($suggestions)));
		  foreach ($randoms as $friend_id):
			  $friend = Model_User::find_user_light($friend_id);
			  $friends[$friend['username']] = array(
				  'user'  => $friend,
				  'score' => $suggestions[$friend_id]
			  );
		  endforeach;

?>

<ul class="media-list">
	<?php foreach ($friends as $friend): ?>

	<?= new View_Users_Friend($friend['user'], $friend['score']) ?>

	<?php endforeach; ?>
</ul>

<?php

		else:

			echo __('Darn, no friend suggestions available. You might want to consider adding some friends first?');

		endif;

		return ob_get_clean();
	}

}
