<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Visitor section
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

if ($user):

	// Member
?>
<ul>

	<?php if ($new_comments = Anqh::notifications($user)): ?>
	<li class="menu-messages">
		<ul class="new-messages">
			<?php foreach ($new_comments as $class => $link): ?>
			<li class="<?php echo $class ?>"><?php echo $link ?></li>
			<?php endforeach; ?>
		</ul>
	</li>
	<?php endif; ?>

	<li class="menu-profile">
		<?php echo HTML::avatar($user->avatar, $user->username, true); ?>
		<?php echo __(':user <var class="uid">[#:id]</var>', array(
				':id'      => $user->id,
				':user'    => HTML::user($user),
			)); ?>
		<?php echo HTML::anchor('#', '&#9660;', array('class' => 'toggler', 'onclick' => '$("#visitor .submenu").toggleClass("toggled"); return false;')); ?>
		<ul class="submenu">
			<li class="menu-messages"><?php echo HTML::anchor(Forum::private_messages_url(), __('Private messages'), array('class' => 'icon private-message')) ?></li>
			<li class="menu-friends"><?php echo HTML::anchor(URL::user($user, 'friends'), __('Friends'), array('class' => 'icon friends')) ?></li>
			<li class="menu-ignores"><?php echo HTML::anchor(URL::user($user, 'ignores'), __('Ignores'), array('class' => 'icon ignores')) ?></li>
			<li class="menu-settings"><?php echo HTML::anchor(URL::user($user, 'settings'), __('Settings'), array('class' => 'icon settings')) ?></li>
			<?php if ($user->has_role('admin')): ?>
			<li class="menu-roles admin"><?php echo HTML::anchor(Route::get('roles')->uri(), __('Roles'), array('class' => 'icon role')) ?></li>
			<li class="menu-tags admin"><?php echo HTML::anchor(Route::get('tags')->uri(), __('Tags'), array('class' => 'icon tag')) ?></li>
			<li class="menu-profiler admin"><?php echo HTML::anchor('#debug', __('Profiler'), array('class' => 'icon profiler', 'onclick' => '$("div.kohana").toggle();')) ?></li>
			<?php endif; ?>
		</ul>
	</li>

	<li class="menu-logout"><?php echo HTML::anchor(Route::get('sign')->uri(array('action' => 'out')), __('Sign out')); ?></li>

</ul>
<?php

// Logout also from Facebook
/*
if (FB::enabled() && Visitor::instance()->get_provider()) {
	Widget::add('dock', ' - ' . HTML::anchor('sign/out', FB::icon() . __('Sign out'), array('onclick' => "FB.Connect.logoutAndRedirect('/sign/out'); return false;")));
} else {
*/
//}

else:

	// Guest
	echo Form::open(Route::get('sign')->uri(array('action' => 'in')));
?>
<ul>
	<li class="grid1 first"><?php echo Form::input('username', null, array('placeholder' => __('Username'))); ?></li>
	<li class="grid1"><?php echo Form::password('password', null, array('placeholder' => __('Password'))); ?></li>
	<li class="grid1"><?php echo Form::submit('signin', __('Sign in')); ?>
	<!--<li class="grid2"><?php echo Form::checkbox('remember', 'true', false, array('disabled' => 'disabled')), Form::label('remember', __('Remember me')); ?></li>-->
</ul>
<?php
	echo Form::close();

endif;
