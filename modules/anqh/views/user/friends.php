<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Friendlist
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>
<ul>
	<?php foreach ($friends as $friend): ?>

	<li class="group">
		<?php echo HTML::avatar($friend['avatar'], $friend['username']) ?>
		<?php echo HTML::user($friend) ?>
		<?php if (isset($friend['last_login'])) echo '<small class="ago">', HTML::time(Date::short_span($friend['last_login'], true, true), $friend['last_login']), '</small>'; ?>
	</li>
	<?php endforeach; ?>

</ul>
