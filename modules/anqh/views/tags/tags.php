<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Tags
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if (empty($tags)): ?>
<div class="empty">
	<?php echo __('No tags yet.') ?>
</div>
<?php else: ?>
<ul>
	<?php foreach ($tags as $tag): ?>

	<li><?php echo HTML::anchor(Route::model($tag), $tag->name) ?></li>
	<?php endforeach; ?>

</ul>
<?php	endif; ?>
