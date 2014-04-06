<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Friend view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Users_Friend extends View_Base {

	/**
	 * @var  integer
	 */
	public $common;

	/**
	 * @var  array
	 */
	public $user;


	/**
	 * Create new view.
	 *
	 * @param  mixed    $user  Light user array
	 * @param  integer  $common  Common friends
	 */
	public function __construct($user = null, $common = null) {
		parent::__construct();

		if ($user instanceof Model_User) {
			$user = $user->light_array();
		}

		$this->user   = $user;
		$this->common = $common;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function render() {
		ob_start();

?>

<li class="media">
	<div class="pull-left">
		<?= HTML::avatar($this->user['avatar'], $this->user['username']) ?>
	</div>
	<div class="media-body">

		<?php if (self::$_user && !self::$_user->is_friend($this->user)): ?>
		<?= HTML::anchor(
			URL::user($this->user, 'friend') . '?token=' . Security::csrf(),
			'<i class="fa fa-heart"></i> ' . __('Add to friends'),
			array('class' => 'ajaxify btn btn-lovely btn-sm pull-right', 'data-ajaxify-target' => 'li.media')) ?>
		<?php endif; ?>

		<?= HTML::user($this->user) ?><br />
		<?php if ($this->common): ?>
		<small><?= __(':friends mutual friends', array(':friends' => $this->common)) ?></small><br />
		<?php endif; ?>

	</div>
</li>

<?php

		return ob_get_clean();
	}

}
