<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User info.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_User_Info extends View_Section {

	/**
	 * @var  Model_User
	 */
	public $user;


	/**
	 * Create new view.
	 *
	 * @param  Model_User  $user
	 */
	public function __construct(Model_User $user) {
		parent::__construct();

		$this->user = $user;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		$dob_visibility = $this->user->setting('user.dob');

?>

<dl>

		<?= $this->user->homepage ? '<dt>' . __('Homepage')      . '</dt><dd>' . HTML::anchor($this->user->homepage, HTML::chars($this->user->homepage)) . '</dd>' : '' ?>
		<?= ($this->user->gender == Model_User::GENDER_FEMALE || $this->user->gender == Model_User::GENDER_MALE) ? '<dt>' . __('Gender') . '</dt><dd>' . ($this->user->gender == Model_User::GENDER_MALE ? ('<i class="fa fa-male male"></i> ' . __('Male')) : ('<i class="fa fa-female female"></i> ' . __('Female'))  . '</dd>') : '' ?>
		<?php if ($this->user->dob && $dob_visibility == Model_User::DOB_VISIBLE): ?>
		<dt><?= __('Date of Birth') ?></dt><dd><?= Date::format(Date::DMY_LONG, $this->user->dob) ?> (<?= Date::age($this->user->dob) ?>)</dd>
		<?php elseif ($this->user->dob && $dob_visibility == Model_User::DOB_DATEONLY): ?>
		<dt><?= __('Date of Birth') ?></dt><dd><?= Date::format(Date::DM_LONG, $this->user->dob) ?></dd>
		<?php endif; ?>

		<dt><?= __('Registered') ?></dt><dd><?= Date::format(Date::DATETIME, $this->user->created) ?>
			(<?= __('member #:member', array(':member' => '<var>' . number_format($this->user->id) . '</var>')) ?>)</dd>
		<dt><?= __('Last login') ?></dt><dd><?= HTML::time(Date::fuzzy_span($this->user->last_login), $this->user->last_login) ?></dd>

</dl>

<?php

		return ob_get_clean();
	}

}
