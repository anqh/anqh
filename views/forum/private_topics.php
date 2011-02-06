<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum Private Topics
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php if (count($topics)): ?>

<header>
	<span class="grid1 first from"><?php echo __('From') ?></span>
	<span class="grid4 topic"><?php echo __('Topic') ?></span>
	<span class="grid1 replies"><?php echo __('Replies') ?></span>
	<span class="grid2 latest"><?php echo __('Latest post') ?></span>
</header>

	<?php foreach ($topics as $topic): ?>

<article>
	<span class="grid1 first from">
		<?php echo HTML::user($topic->original('author'), $topic->author_name) ?>
	</span>

	<header class="grid4 topic">
		<?php echo HTML::anchor(
			Route::model($topic, '?page=last#last'),
			HTML::chars($topic->name),
			array(
				'class' => ($recipients = $topic->recipient_count) < 3 ? 'icon private-message' : 'icon group-message',
				'title' => $recipients < 3 ? __('Personal message') : __(':recipients recipients', array(':recipients' => Num::format($recipients, 0)))
			)
		) ?>
	</header>

	<span class="grid1 replies">
		<?php echo Num::format($topic->post_count - 1, 0) ?>
	</span>

	<span class="grid2 latest">
		<small class="ago"><?php echo HTML::time(Date::short_span($topic->last_posted, true, true), $topic->last_posted) ?></small>
		<?php echo HTML::user($topic->original('last_poster'), $topic->last_poster) ?>
	</span>
</article>

	<?php endforeach; ?>

<?php else: ?>

	<article class="empty">
		<?php echo __('No topics yet.') ?>
	</article>

<?php endif; ?>
