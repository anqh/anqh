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
$my = ($user && $post->author->id == $user->id);

// Topic author's post
$owners = ($topic->author_id && $post->author_id == $topic->author_id);
?>

	<article id="post-<?php echo $post->id ?>" class="post <?php echo ($owners ? 'owner ' : ''), ($my ? 'my ' : ''), Text::alternate('', 'alt') ?>">
		<header<?php echo $post->id == $topic->last_post->id ? ' id="last"' : '' ?>>

			<?php echo HTML::avatar($post->author->avatar, $post->author->username) ?>

			<span class="actions">
			<?php if (Permission::has($post, Model_Forum_Post::PERMISSION_UPDATE, $user)) echo HTML::anchor(
					Route::get('forum_post')->uri(array(
						'id'       => Route::model_id($post),
						'topic_id' => Route::model_id($topic),
						'action'   => 'edit')),
					__('Edit'),
					array('class' => 'action post-edit small')) ?>

			<?php if (Permission::has($post, Model_Forum_Post::PERMISSION_DELETE, $user)) echo HTML::anchor(
					Route::get('forum_post')->uri(array(
						'id'       => Route::model_id($post),
						'topic_id' => Route::model_id($topic),
						'action'   => 'delete')) . '?token=' . Security::csrf(),
					__('Delete'),
					array('class' => 'action post-delete small')) ?>

			<?php if (Permission::has($topic, Model_Forum_Topic::PERMISSION_POST, $user)) echo HTML::anchor(
					Route::get('forum_post')->uri(array(
						'id'       => Route::model_id($post),
						'topic_id' => Route::model_id($topic),
						'action'   => 'quote')),
					__('Quote'),
					array('class' => 'action post-quote small')) ?>

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
			if ($post->parent->id): ?>
			<br />
			<?php echo __('Replying to :parent', array(
				':parent' => HTML::anchor(
					Route::get('forum_post')->uri(array(
						'topic_id' => Route::model_id($topic),
						'id'       => $post->parent->id,
					)) . '#post-' . $post->parent->id,
					HTML::chars($post->parent->topic->name)))) ?>
			<?php endif; ?>
			</span>

		</header>

		<section class="post-content">

<?php echo BB::factory($post->post)->render() ?>

		</section>

		<footer>
			<?php echo $post->author->signature ? BB::factory("\n--\n" . $post->author->signature)->render() : '' ?>

		</footer>
	</article>
