<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Send invite.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_User_Invite extends View_Section {

	/**
	 * @var  array
	 */
	public $errors;

	/**
	 * @var  Model_Invitation
	 */
	public $invitation;

	/**
	 * @var  string
	 */
	public $message;


	/**
	 * Create new view.
	 *
	 * @param  Model_Invitation  $invitation
	 */
	public function __construct(Model_Invitation $invitation) {
		parent::__construct();

		$this->invitation = $invitation;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo $this->message;

?>

<div class="row">
	<div class="col-sm-6">

		<?= Form::open() ?>

		<fieldset>
			<legend><?= __('Not yet invited?') ?></legend>

			<?= Form::input_wrap(
				'email',
				$this->invitation->email,
				array('class' => 'input-lg', 'type' => 'email', 'placeholder' => 'john.doe@domain.tld', 'required'),
				__('Send an invitation to'),
				Arr::get($this->errors, 'email'),
				__('Please remember: Valid, invited email is required to join. You can invite yourself too!')
			) ?>

		</fieldset>

		<fieldset>
			<?= Form::button('invite', '<i class="fa fa-envelope"></i> ' . __('Send invitation'), array('type' => 'submit', 'class' => 'btn btn-primary btn-large')) ?>
			<?= HTML::anchor(Request::back('/', true), __('Cancel'), array('class' => 'cancel')) ?>
		</fieldset>

		<?= Form::close() ?>

		<br>

		<?= Form::open() ?>

		<fieldset>
			<legend><?= __('Got my invitation!') ?></legend>

			<?= Form::input_wrap(
				'code',
				null,
				array('class' => 'input-lg', 'placeholder' => __('M0573XC3LL3N751R'), 'maxlength' => 16, 'required'),
				__('Invitation code'),
				Arr::get($this->errors, 'code'),
				__('Your invitation code is included in the mail you received, 16 characters.')
			) ?>

		</fieldset>

		<fieldset>
			<?= Form::hidden('signup', true) ?>
			<?= Form::button('invited', __('Final step!') . ' <i class="fa fa-arrow-right"></i>', array('type' => 'submit', 'class' => 'btn btn-primary btn-large')) ?>
			<?= HTML::anchor(Request::back('/', true), __('Cancel'), array('class' => 'cancel')) ?>
		</fieldset>

		<?= Form::close() ?>

	</div>


	<div class="col-md-1 hidden-xs text-center lead">

		<?= __('or') ?>

	</div>


	<div class="col-sm-5">

		<?= HTML::anchor(
				Route::url('oauth', array('action' => 'login', 'provider' => 'facebook')),
				'&nbsp;<i class="fa fa-facebook"></i> ' . __('Connect with Facebook') . '&nbsp;',
				array('class' => 'btn btn-lg btn-facebook', 'title' => __('Sign in with your Facebook account'))
			) ?>

	</div>
</div>

<?php

		return ob_get_clean();
	}

}
