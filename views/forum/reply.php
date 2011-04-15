<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Reply
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<section class="author grid2 first">
	<?php echo HTML::avatar($user->avatar, $user->username) ?>

	<?php echo HTML::user($user) ?>
</section>

<section class="post-edit grid6">
	<?php echo View::factory('forum/post_edit', array(
		'form_id' => isset($form_id) ? $form_id : null,
		'ajax'    => isset($ajax) ? $ajax : null,
		'errors'  => isset($errors) ? $errors : null,
		'cancel'  => isset($cancel) ? $cancel : null,
		'post'    => $post,
		'action'  => $post->parent_id	?
			Route::url($private ? 'forum_private_post' : 'forum_post', array(
				'topic_id' => $topic->id,
				'id'       => $post->parent_id,
				'action'   => 'quote'
			)) : Route::model($topic, 'reply'),
	)) ?>
</section>
