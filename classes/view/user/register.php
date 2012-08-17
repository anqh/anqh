<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User_Register
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_User_Register extends View_Section {

	/**
	 * @var  string
	 */
	public $code;

	/**
	 * @var  array
	 */
	public $errors;

	/**
	 * @var  Model_User
	 */
	public $user;


	/**
	 * Create new view.
	 *
	 * @param  Model_User  $user
	 * @param  string      $code
	 */
	public function __construct(Model_User $user, $code) {
		parent::__construct();

		$this->user = $user;
		$this->code = $code;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo Form::open();

?>

<fieldset>
	<legend><?= __('Almost there!') ?></legend>

	<?= Form::control_group(
		Form::input('username', $this->user->userame, array('class' => 'input-small', 'placeholder' => __('JohnDoe'))),
		array('username' => __('Username')),
		Arr::get($this->errors, 'username'),
		__('Choose a unique username with at least <var>:length</var> characters. No special characters, thank you.',
			array(':length' => Kohana::config('visitor.username.length_min')))
	) ?>

	<?= Form::control_group(
		Form::password('password'),
		array('password' => __('Password')),
		Arr::get($this->errors, 'password')
	) ?>

	<?= Form::control_group(
		Form::password('password_confirm'),
		array('password_confirm' => __('Confirm')),
		Arr::get($this->errors, 'password_confirm'),
	__('Try to use letters, numbers and special characters for a stronger password, with at least <var>8</var> characters.')
	) ?>

	<?= Form::control_group(
		Form::input('email', $this->user->email, array('disabled' => 'disabled', 'class' => 'input-xxlarge', 'placeholder' => __('john.doe@domain.tld'))),
		array('email' => __('Email')),
		Arr::get($this->errors, 'email'),
		__('Please remember: sign up is available only with a valid, invited email.')
	) ?>

</fieldset>

<fieldset>
	<?= Form::hidden('code', $this->code) ?>
	<?= Form::button('register', __('Sign up!'), array('type' => 'submit', 'class' => 'btn btn-primary btn-large')) ?>
	<?= HTML::anchor(Request::back('/', true), __('Cancel'), array('class' => 'cancel')) ?>
</fieldset>

<?php

		echo Form::close();

		return ob_get_clean();
	}

}
