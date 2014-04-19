<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User_Info
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
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

?>

<dl>

		<?= $this->user->homepage ? '<dt>' . __('Homepage')      . '</dt><dd>' . HTML::anchor($this->user->homepage, HTML::chars($this->user->homepage)) . '</dd>' : '' ?>
		<?= ($this->user->gender == 'm' || $this->user->gender == 'f') ? '<dt>' . __('Gender') . '</dt><dd>' . ($this->user->gender == 'm' ? __('Male') : __('Female'))  . '</dd>' : '' ?>
		<?= $this->user->dob      ? '<dt>' . __('Date of Birth') . '</dt><dd>' . Date::format('DMYYYY', $this->user->dob) . ' (' . Date::age($this->user->dob) . ')</dd>' : '' ?>

		<dt><?= __('Registered') ?></dt><dd><?php echo HTML::time(Date::fuzzy_span($this->user->created), $this->user->created) ?>
			(<?= __('member #:member', array(':member' => '<var>' . number_format($this->user->id) . '</var>')) ?>)</dd>
		<dt><?= __('Updated') ?></dt><dd><?php echo HTML::time(Date::fuzzy_span($this->user->modified), $this->user->modified) ?></dd>
		<dt><?= __('Last login') ?></dt><dd><?php echo HTML::time(Date::fuzzy_span($this->user->last_login), $this->user->last_login) ?>
			(<?= __($this->user->login_count == 1 ? ':logins login' : ':logins logins', array(':logins' => '<var>' . number_format($this->user->login_count) . '</var>')) ?>)</dd>

</dl>

<?php

		return ob_get_clean();
	}

}
