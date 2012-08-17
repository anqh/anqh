<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User_Invite
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
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

		// Send invite
		echo Form::open();

?>

<fieldset>
	<legend><?= __('Not yet invited?') ?></legend>

	<?php echo Form::control_group(
		Form::input('email', $this->invitation->email, array('class' => 'input-xxlarge', 'placeholder' => __('john.doe@domain.tld'))),
		array('email' => __('Send an invitation to')),
		Arr::get($this->errors, 'email'),
		__('Please remember: sign up is available only with a valid, invited email.')
	) ?>

</fieldset>

<fieldset class="form-actions">
	<?= Form::button('invite', __('Send invitation'), array('type' => 'submit', 'class' => 'btn btn-primary btn-large')) ?>
	<?= HTML::anchor(Request::back('/', true), __('Cancel'), array('class' => 'cancel')) ?>
</fieldset>

<?php

		echo Form::close();


		// Enter code
		echo Form::open();

?>

<fieldset>
	<legend><?= __('Got my invitation!') ?></legend>

	<?php echo Form::control_group(
		Form::input('code', null, array('class' => 'input-xxlarge', 'placeholder' => __('M0573XC3LL3N751R'))),
		array('code' => __('Enter your invitation code')),
		Arr::get($this->errors, 'code'),
	__('Your invitation code is included in the mail you received, 16 characters.')
	) ?>

</fieldset>

<fieldset class="form-actions">
	<?= Form::hidden('signup', true) ?>
	<?= Form::button('invited', __('Final step!'), array('type' => 'submit', 'class' => 'btn btn-primary btn-large')) ?>
	<?= HTML::anchor(Request::back('/', true), __('Cancel'), array('class' => 'cancel')) ?>
</fieldset>

<?php

		echo Form::close();

		return ob_get_clean();
	}

}
