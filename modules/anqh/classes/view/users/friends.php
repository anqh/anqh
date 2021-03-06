<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Users_Friends
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Users_Friends extends View_Section {

	/**
	 * @var  boolean  People who friended user
	 */
	public $friended = false;

	/**
	 * @var  array
	 */
	public $friends = array();

	/**
	 * @var  Model_User
	 */
	public $user;


	/**
	 * Create new view.
	 *
	 * @param  Model_User  $user
	 * @param  boolean     $friended
	 */
	public function __construct($user = null, $friended = false) {
		parent::__construct();

		$this->user     = $user;
		$this->friended = $friended;

		foreach ($this->user->find_friends($this->friended) as $friend_id) {
		  $friend = Model_User::find_user_light($friend_id);
		  $this->friends[$friend['username']] = $friend;
    }
		ksort($this->friends, SORT_LOCALE_STRING);

		$this->title  = $friended ? __('Friending me') : __('Friends');
		$this->title .= ' <small class="muted">(' . count($this->friends) . ')</small>';
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();



?>

<ul class="media-list">
	<?php foreach ($this->friends as $friend): ?>

	<?= new View_Users_Friend($friend) ?>

	<?php endforeach; ?>
</ul>

<?php

		return ob_get_clean();
	}


	/**
	 * Get tabs.
	 *
	 * @return  array
	 */
	public function tabs() {
		return array(
			array(
				'selected' => !$this->friended,
				'tab'      => HTML::anchor(URL::user($this->user, 'friends'), __('My friends')),
			),
			array(
				'selected' => $this->friended,
				'tab'      => HTML::anchor(URL::user($this->user, 'friends') . '?of=me', __('Friending me')),
			)
		);
	}

}
