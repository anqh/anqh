<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Registration form
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open();
?>

	<fieldset>
		<legend><?php echo __('Almost there!') ?></legend>
		<ul>
			<?php echo Form::input_wrap(
				'username',
				$user,
				array('placeholder' => __('JohnDoe')),
				__('Username'),
				$errors,
				__(
					'Choose a unique username with at least <var>:length</var> characters. No special characters, thank you.',
					array(':length' => Kohana::config('visitor.username.length_min'))
				)); ?>
			<?php echo Form::password_wrap('password', null, null, __('Password'), $errors); ?>
			<?php echo Form::password_wrap(
				'password_confirm',
				null,
				null,
				__('Confirm'),
				$errors,
				__('Try to use letters, numbers and special characters for a stronger password, with at least <var>8</var> characters.')
			); ?>
			<?php echo Form::input_wrap(
				'email',
				$user,
				array('disabled' => 'disabled', 'placeholder' => __('john.doe@domain.tld')),
				__('Email'),
				$errors,
				__('Please remember: sign up is available only with a valid, invited email.')) ?>
		</ul>
	</fieldset>

	<fieldset>
		<?php echo Form::submit_wrap('register', __('Sign up!'), null, Request::back('/', true), null, array('code' => $code)) ?>
	</fieldset>

<?php
echo Form::close();
