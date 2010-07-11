<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * User info
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<dl>

	<?php echo $user->name     ? '<dt>' . __('Name')          . '</dt><dd>' . HTML::chars($user->name) . '</dd>' : '' ?>
	<?php echo $user->homepage ? '<dt>' . __('Homepage')      . '</dt><dd>' . HTML::anchor($user->homepage, HTML::chars($user->homepage)) . '</dd>' : '' ?>
	<?php echo $user->gender   ? '<dt>' . __('Gender')        . '</dt><dd>' . ($user->gender == 'm' ? __('Male') : __('Female'))  . '</dd>' : '' ?>
	<?php echo $user->dob      ? '<dt>' . __('Date of Birth') . '</dt><dd>' . Date::format('DMYYYY', $user->dob) . ' (' . Date::timespan_short($user->dob) . ')</dd>' : '' ?>

	<dt><?php echo __('Registered') ?></dt><dd><?php echo HTML::time(Date::fuzzy_span($user->created), $user->created) ?>
		(<?php echo __('member #:member', array(':member' => '<var>' . number_format($user->id) . '</var>')) ?>)</dd>
	<dt><?php echo __('Updated') ?></dt><dd><?php echo HTML::time(Date::fuzzy_span($user->modified), $user->modified) ?></dd>
	<dt><?php echo __('Last login') ?></dt><dd><?php echo HTML::time(Date::fuzzy_span($user->last_login), $user->last_login) ?>
		(<?php echo __($user->login_count == 1 ? ':logins login' : ':logins logins', array(':logins' => '<var>' . number_format($user->login_count) . '</var>')) ?>)</dd>

</dl>
