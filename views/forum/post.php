<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum post
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

// Viewer's post
$my = ($user && $post->author_id == $user->id);

// Topic author's post
$owners = ($topic->author_id && $post->author_id == $topic->author_id);
?>

	<article id="post-<?php echo $post->id ?>" class="post <?php echo ($owners ? 'owner ' : ''), ($my ? 'my ' : ''), Text::alternate('', 'alt') ?>">
		<header<?php echo $post->id == $topic->last_post_id ? ' id="last"' : '' ?>>

			<?php echo HTML::avatar($post->author->avatar, $post->author->username) ?>

			<span class="actions">
			<?php if ($my): ?>

				<?php echo HTML::anchor(Route::model($post, 'edit'), __('Edit'), array('class' => 'action post-edit')) ?>
				<?php echo HTML::anchor(Route::model($post, 'delete?token=' . Security::csrf()), __('Delete'), array('class' => 'action post-delete')) ?>

			<?php endif; ?>
			<?php if (Permission::has($topic, Model_Forum_Topic::PERMISSION_POST, $user)): ?>

				<?php echo HTML::anchor(Route::model($post, 'quote'), __('Quote'), array('class' => 'action post-quote')) ?>

			<?php endif; ?>
			</span>

			<span class="details">
			<?php echo __(':user, :ago', array(
				':user' => HTML::user($post->author_id, $post->author_name),
				':ago'  => HTML::time(Date::fuzzy_span($post->created), $post->created)
			));
			if ($post->modifies > 0): ?>
			<br />
			<?php echo __('Edited :ago', array(
				':ago' => HTML::time(Date::fuzzy_span($post->modified), $post->modified)
			)) ?>
			<?php endif;
			if ($post->parent_id): $parent_topic = $post->parent->topic; ?>
			<br />
			<?php echo __('Replying to :parent', array(
				':parent' => HTML::anchor(Route::model($parent_topic, $post->parent_id . '#post-' . $post->parent_id), HTML::chars($parent_topic->name)),
			)) ?>
			<?php endif; ?>
			</span>

		</header>

		<section class="post-content">

<?php echo BB::factory($post->post)->render() ?>

		</section>
	</article>
