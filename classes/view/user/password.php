<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User_Password
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_User_Password extends View_Section {

	/**
	 * @var  string
	 */
	public $email;

	/**
	 * @var  string
	 */
	public $message;


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo $this->message;

		echo Form::open();

		echo Form::control_group(
			Form::input('email', $this->email, array('class' => 'input input-xxlarge', 'placeholder' => __('Username or email'))),
			array('email' => __('Send my new password to')),
			null,
			__('We will send you a "new" password generated from your old forgotten password.<br /><em>Please change your password after signing in!</em>')
		);

		echo Form::button(null, '<i class="icon-envelope icon-white"></i> ' . __('Send'), array('class' => 'btn btn-primary'));

		echo Form::close();

?>

<hr />

<blockquote cite="http://dilbert.com/strips/comic/1996-09-05/">
	<dl class="dl-horizontal">
		<dt>Asok:</dt> <dd>I have forgotten my password. I humbly beg for assistance.</dd>
		<dt>Dogbert:</dt> <dd>I have no time for boring administrative tasks, you fool! I'm too busy upgrading the network.</dd>
		<dt>Asok:</dt> <dd>You could have given me a new password in the time it took to belittle me.</dd>
		<dt>Dogbert:</dt> <dd>Yeah, but which option would give me job satisfaction?</dd>
	</dl>
	<small class="pull-right"><a href="http://dilbert.com/strips/comic/1996-09-05/">Dilbert</a></small>
</blockquote>

<?php

		return ob_get_clean();
	}

}
