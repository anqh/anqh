<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Navigation
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<nav role="navigation">
	<ul>
	<?php	foreach ($items as $id => $link): ?>
		<li class="menu-<?php echo $id . ($selected == $id ? ' selected' : '') ?>">
			<?php echo HTML::anchor($link['url'], $link['text']) ?>
			<?php if ($selected == $id && !empty($sub_items)): ?>

			<ul class="submenu">
				<?php foreach ($sub_items as $id => $link): ?>
					<li class="submenu-<?php echo $id . ($sub_selected == $id ? ' selected' : '') ?>"><?php echo HTML::anchor($link['url'], $link['text']) ?></li>
				<?php endforeach; ?>

			</ul>
			<?php endif; ?>

		</li>
	<?php	endforeach; ?>

	</ul>
</nav>
