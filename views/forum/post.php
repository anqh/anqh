<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum post
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

// Post author
$author = Model_User::find_user_light($post->original('author'));

// Viewer's post
$my = ($user && $author && $author['id'] == $user->id);

// Topic author's post
$owners = ($post->original('author') == $topic->original('author'));
?>

	<article id="post-<?php echo $post->id ?>" class="post <?php echo ($owners ? 'owner ' : ''), ($my ? 'my ' : ''), Text::alternate('', 'alt') ?>">

		<section class="author grid2 first">
			<?php echo HTML::avatar($author['avatar'], $author['username']) ?>

			<?php echo HTML::user($author, $post->author_name) ?>
		</section>

		<section class="post-content grid6">
			<header<?php echo $post->id == $topic->last_post->id ? ' id="last"' : '' ?>>
				<small class="ago">
					<?php echo HTML::time(Date::short_span($post->created, true, true), $post->created) ?>
				</small>

				<span class="actions">
				<?php echo HTML::anchor(
					Route::get('forum_post')->uri(array(
						'id'       => Route::model_id($post),
						'topic_id' => Route::model_id($topic)
					)) . '#post-' . $post->id,
					'#' . $number,
					array('title' => __('Permalink'))) ?>

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
			</header>

			<?php if ($post->parent->id) echo __('Replying to :parent', array(
				':parent' => HTML::anchor(
					Route::get('forum_post')->uri(array('topic_id' => Route::model_id($topic), 'id' => $post->parent->id)) . '#post-' . $post->parent->id,
					HTML::chars($post->parent->topic->name)
				))) ?>

<?php echo BB::factory($post->post)->render() ?>

			<footer>
				<?php if ($post->modifies > 0) echo	__('Edited :ago', array(':ago' => HTML::time(Date::fuzzy_span($post->modified), $post->modified))); ?>

				<?php echo $author['signature'] ? BB::factory("\n--\n" . $author['signature'])->render() : '' ?>

			</footer>
		</section>

	</article>
