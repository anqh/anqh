<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum groups
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php foreach ($groups as $group): ?>

<section class="group">

	<?php if (count($groups) > 1): ?>
	<header>
		<h3><?php echo HTML::anchor(Route::model($group), $group->name) ?></h3>
		<p><?php echo HTML::chars($group->description) ?></p>
	</header>
	<?php endif; ?>

	<?php if (count($group->areas)): ?>

		<?php foreach ($group->areas as $area): ?>

			<?php if (Permission::has($area, Model_Forum_Area::PERMISSION_READ)): ?>

			<article class="area">
				<header>
					<h4 class="unit size2of3"><?php echo HTML::anchor(Route::model($area), HTML::chars($area->name), array('title' => strip_tags($area->description))) ?></h4>
					<ul class="details unit size1of3">
						<li class="unit size1of2"><?php echo HTML::icon_value(array(':topics' => $area->topic_count), ':topics topic', ':topics topics', 'topics') ?></li>
						<li class="unit size1of2"><?php echo HTML::icon_value(array(':posts' => $area->post_count), ':posts post', ':posts posts', 'posts') ?></li>
					</ul>
				</header>
				<?php echo $area->description ?><br />
				<?php if ($area->topic_count > 0): ?>

				<small class="ago"><?php echo HTML::time(Date::short_span($area->last_topic->last_posted, true, true), $area->last_topic->last_posted) ?></small>
				<?php echo HTML::user($area->last_topic->author, $area->last_topic->last_poster) ?>:
				<?php echo HTML::anchor(Route::model($area->last_topic, '?page=last#last'), HTML::chars($area->last_topic->name)) ?>

				<?php else: ?>
				<sup><?php echo __('No topics yet.') ?></sup>
				<?php endif; ?>
			</article>

			<?php elseif ($area->status != Model_Forum_Area::STATUS_HIDDEN): ?>

			<article class="area disabled">
				<header>
					<span title="<?php echo strip_tags($area->description) ?>"><?php echo HTML::chars($area->name) ?></span>
				</header>
				<?php echo __('Members only') ?>
			</article>

			<?php	endif; ?>

		<?php endforeach; ?>

	<?php else: ?>

		<article class="empty">
			<?php echo __('No areas yet.') ?>
		</article>

	<?php endif; ?>

</section>

<?php endforeach; ?>
