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

<nav>
	<ul>
	<?php	foreach ($items as $id => $link): ?>
		<li class="menu-<?php echo $id . ($selected == $id ? ' selected' : '') ?>"><?php echo HTML::anchor($link['url'], $link['text']) ?></li>
	<?php	endforeach; ?>
	</ul>
</nav>
