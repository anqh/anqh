<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Top tabs
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if (!empty($tabs)): ?>
<nav class="tabs">
	<ul>

	<?php $count = 0; foreach ($tabs as $id => $tab): ?>
		<li class="tab-<?php echo $id, ($count == 0 ? ' first' : ''), (++$count == count($tabs) ? ' last' : ''), ($selected == $id ? ' selected' : '') ?>"><?php echo HTML::anchor($tab['link'], $tab['text']) ?></li>
	<?php endforeach; ?>

	</ul>
</nav>
<?php endif; ?>
