<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum groups
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

$areas = $group->areas();
?>

<header>
	<h3 class="grid4 first"><?php echo HTML::anchor(Route::model($group), $group->name) ?></h3>
	<span class="grid1"><?php echo __('Topics') ?></span>
	<span class="grid1"><?php echo __('Posts') ?></span>
	<span class="grid2"><?php echo __('Latest post') ?></span>
</header>

<?php if (count($areas)): ?>

	<?php foreach ($areas as $area): ?>

		<?php if (Permission::has($area, Model_Forum_Area::PERMISSION_READ, $user)): ?>

<article class="area">
	<header class="grid6 first">
		<h4 class="grid4 first"><?php echo HTML::anchor(Route::model($area), HTML::chars($area->name), array('class' => 'grid4 first')) ?></h4>
		<span class="grid1"><?php echo Num::format($area->topic_count, 0) ?></span>
		<span class="grid1"><?php echo Num::format($area->post_count, 0) ?></span>
		<br />
		<?php echo $area->description ?>
	</header>

	<p class="grid2 cut">
		<?php if ($area->topic_count > 0): $last_topic = $area->last_topic(); ?>

		<small class="ago"><?php echo HTML::time(Date::short_span($last_topic->last_posted, true, true), $last_topic->last_posted) ?></small>
		<?php echo HTML::user($last_topic->last_post()->author_id, $last_topic->last_poster) ?><br />
		<?php echo HTML::anchor(Route::model($last_topic, '?page=last#last'), HTML::chars($last_topic->name), array('title' => HTML::chars($last_topic->name))) ?>

		<?php else: ?>
		<sup><?php echo __('No topics yet.') ?></sup>
		<?php endif; ?>
	</p>
</article>

		<?php elseif ($area->status != Model_Forum_Area::STATUS_HIDDEN): ?>

<article class="area disabled">
	<header>
		<h4><?php echo HTML::chars($area->name) ?></h4>
		<?php echo __('Members only') ?>
	</header>
</article>

		<?php	endif; ?>

	<?php endforeach; ?>

<?php else: ?>

<article class="empty">
	<?php echo __('No areas yet.') ?>
</article>

<?php endif; ?>
