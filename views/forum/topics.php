<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum Topics
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if (count($topics)): ?>

<header>
	<h3 class="grid5 first"><?php echo __('Topic') ?></h3>
	<span class="grid1"><?php echo __('Replies') ?></span>
	<span class="grid2"><?php echo __('Latest post') ?></span>
</header>

	<?php foreach ($topics as $topic): ?>

<article>
	<header class="grid6 first">
		<?php echo HTML::anchor(Route::model($topic, '?page=last#last'), HTML::chars($topic->name), array('class' => 'grid5 first')) ?>
		<span class="grid1"><?php echo Num::format($topic->post_count - 1, 0) ?></span>
	</header>

	<p class="grid2">
		<small class="ago"><?php echo HTML::time(Date::short_span($topic->last_posted, true, true), $topic->last_posted) ?></small>
		<?php echo HTML::user($topic->last_post->original('author'), $topic->last_poster) ?>
	</p>
</article>

	<?php endforeach; ?>

<?php else: ?>

	<article class="empty">
		<?php echo __('No topics yet.') ?>
	</article>

<?php endif; ?>
