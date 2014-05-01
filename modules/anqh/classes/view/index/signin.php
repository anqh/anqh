<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Sign in.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Index_Signin extends View_Section {

	/**
	 * Get form.
	 *
	 * @return  string
	 */
	public static function form() {
		ob_start();

		echo HTML::anchor(
			Route::url('oauth', array('action' => 'login', 'provider' => 'facebook')),
			'&nbsp;<i class="fa fa-facebook"></i> ' . __('Connect with Facebook') . '&nbsp;',
			array('class' => 'btn btn-block btn-facebook', 'title' => __('Sign in with your Facebook account'))
		);

		echo '<hr>';

		echo Form::open(Route::url('sign', array('action' => 'in')));
		echo Form::input_wrap('username', null, array('autofocus'), __('Username or email'));
		echo Form::password_wrap('password', null, null, __('Password') . ' &nbsp; ' . HTML::anchor(Route::url('sign', array('action' => 'password')), __('Forgot?'), array('class' => 'text-muted')));

		echo Form::form_group(
			Form::checkbox_wrap('remember', 'true', true, null, __('Stay logged in'))
		);

		echo Form::button(null, __('Login'), array('class' => 'btn btn-block btn-primary', 'title' => __('Remember to sign out if on a public computer!')));
		echo Form::close();

		return ob_get_clean();
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo self::form();

		echo '<hr>';

		echo HTML::anchor(
			Route::url('sign', array('action' => 'up')),
			'<i class="fa fa-heart"></i> ' . __('Sign up'),
			array('class' => 'btn btn-lovely btn-block', 'title' => __("Did we mention it's FREE!"))
		);

		return ob_get_clean();
	}

}
