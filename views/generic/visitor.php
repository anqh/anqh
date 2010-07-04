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
	echo HTML::avatar($user->avatar, $user->username, true);
	echo __('[#:id] :user - :signout', array(
		':id'      => $user->id,
		':user'    => HTML::user($user),
		':signout' => HTML::anchor('sign/out', __('Sign out')))), '<br />';

	$new_comments = $user->find_new_comments();
	if (!empty($new_comments)):
		echo __('New messages'), ': ', implode(', ', $new_comments);
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

else:

	// Guest
	echo
		Form::open(Route::get('sign')->uri(array('action' => 'in'))),
		Form::input('username', null, array('title' => __('Username'))),
		Form::password('password', null, array('title' => __('Password'))),
		Form::submit('signin', __('Sign in')),
		Form::close();
		//HTML::anchor(Route::get('sign')->uri(array('action' => 'up')), __('Sign up'));

endif;
