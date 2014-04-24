<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Register.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
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

		echo Form::open(null, array('autocomplete' => 'off'));

?>

<fieldset>
	<legend><?= __('Almost there!') ?></legend>

	<?= Form::input_wrap(
		'username',
		$this->user->username,
		array('class' => 'input-lg', 'placeholder' => __('JohnDoe'), 'required'),
		__('Username'),
		Arr::get($this->errors, 'username'),
		__('Choose a unique username with at least <var>:length</var> characters. No special characters, thank you.',
			array(':length' => Kohana::$config->load('visitor.username.length_min')))
	) ?>

	<?= Form::password_wrap(
		'password',
		null,
		array('class' => 'input-lg', 'required'),
		__('Password'),
		Arr::get($this->errors, 'password'),
		__('Try to use letters, numbers and special characters for a stronger password, with at least <var>8</var> characters.')
	) ?>

	<?= Form::input_wrap(
		'email',
		$this->user->email,
		array('class' => 'input-lg', 'type' => 'email', 'disabled', 'placeholder' => __('john.doe@domain.tld')),
		__('Email'),
		Arr::get($this->errors, 'email')
	) ?>

</fieldset>

<fieldset>
	<?= Form::hidden('code', $this->code) ?>
	<?= Form::button('register', __('Sign up!'), array('type' => 'submit', 'class' => 'btn btn-primary btn-lg')) ?>
	<?= HTML::anchor(Request::back('/', true), __('Cancel'), array('class' => 'cancel')) ?>
</fieldset>

<?php

		echo Form::close();

		return ob_get_clean();
	}

}
