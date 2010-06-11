<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Tag groups
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if (empty($groups)): ?>
<div class="empty">
	<?php echo __('No groups yet.') ?>
</div>
<?php else: ?>
<ul>
	<?php foreach ($groups as $group): ?>
	<li class="clearfix">

		<ul>
			<li>
				<h3><?php echo HTML::anchor(Route::model($group), $group->name) ?></h3>
				<sup><?php echo $group->description ?></sup><br />
				<?php foreach ($group->tags as $tag): ?>
				<?php echo HTML::anchor(Route::model($tag), $tag->name) ?>
				<?php endforeach; ?>

			</li>
		</ul>

	</li>
	<?php endforeach; ?>
	
</ul>
<?php	endif; ?>
