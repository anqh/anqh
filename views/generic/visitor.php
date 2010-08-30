<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Visitor card
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

if ($user):

	// Member
	echo __(':user <var class="uid">[#:id]</var>', array(
		':id'      => $user->id,
		':user'    => HTML::user($user),
	)), '<br />';
	echo HTML::avatar($user->avatar, $user->username);
?>
<ul>
	<li><?php echo HTML::anchor(URL::user($user, 'settings'), __('Settings')) ?></li>
	<li><?php echo HTML::anchor(Route::get('sign')->uri(array('action' => 'out')), __('Sign out')) ?></li>
</ul>
<?php

	$new_comments = $user->find_new_comments();
	if (!empty($new_comments)):
?>
<ul class="new-messages">
	<?php foreach ($new_comments as $class => $link): ?>
	<li class="<?php echo $class ?>"><?php echo $link ?></li>
	<?php endforeach; ?>
</ul>
<?php
	endif;

// Logout also from Facebook
/*
if (FB::enabled() && Visitor::instance()->get_provider()) {
	Widget::add('dock', ' - ' . HTML::anchor('sign/out', FB::icon() . __('Sign out'), array('onclick' => "FB.Connect.logoutAndRedirect('/sign/out'); return false;")));
} else {
*/
//}

else:

	// Guest
	echo
		Form::open(Route::get('sign')->uri(array('action' => 'in'))),
		Form::input('username', null, array('title' => __('Username'))),
		Form::password('password', null, array('title' => __('Password'))),
		Form::checkbox('remember', 'true', false, array('disabled' => 'disabled')),
		Form::label('remember', __('Remember me')),
		Form::submit('signin', __('Sign in')),
		Form::close();

	echo HTML::anchor(Route::get('sign')->uri(array('action' => 'up')), __('Sign up now!'), array('class' => 'action user-add'));

endif;
