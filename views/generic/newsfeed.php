<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Newsfeed
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ul>
	<?php foreach ($newsfeed as $item): ?>

		<li class="group">
			<?php echo HTML::avatar($item['user']->avatar, $item['user']->username) ?>
			<?php echo HTML::user($item['user']) ?>
			<small class="ago"><?php echo HTML::time(Date::short_span($item['stamp'], true, true), $item['stamp']) ?></small>
			<?php echo $item['text'] ?>
		</li>
	<?php endforeach; ?>

</ul>
