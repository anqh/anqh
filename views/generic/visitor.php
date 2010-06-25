<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Visitor card
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if ($user): // Member ?>

	<?php echo HTML::avatar($user->avatar, $user->username) ?>
	<?php echo __('[#:id] :user - :signout', array(
		':id'      => $user->id,
		':user'    => HTML::user($user),
		':signout' => HTML::anchor('sign/out', __('Sign out')))) ?><br />

	<?php
$new_messages = array();
if ($user->newcomments):
	$new_messages[] = HTML::anchor(
		URL::user($user),
		__(':commentsC', array(':comments' => $user->newcomments)),
		array('title' => __('New comments'), 'class' => 'new-comments')
	);
endif;
if (!empty($new_messages)):
	echo ' - ', __('New messages: '), implode(' ', $new_messages);
endif;

// Logout also from Facebook
/*
if (FB::enabled() && Visitor::instance()->get_provider()) {
	Widget::add('dock', ' - ' . HTML::anchor('sign/out', FB::icon() . __('Sign out'), array('onclick' => "FB.Connect.logoutAndRedirect('/sign/out'); return false;")));
} else {
*/
//}

/*
if (Kohana::config('site.inviteonly')) {
				widget::add('dock', ' | ' . html::anchor('sign/up', __('Send invite')));
}
*/
?>

<?php else: // Guest ?>

	<?php echo
		Form::open(Route::get('sign')->uri(array('action' => 'in'))),
		Form::input('username', null, array('title' => __('Username'))),
		Form::password('password', null, array('title' => __('Password'))),
		Form::submit('signin', __('Sign in')),
		Form::close(),
		HTML::anchor(Route::get('sign')->uri(array('action' => 'up')), __('Sign up')); ?>

<?php endif; ?>
