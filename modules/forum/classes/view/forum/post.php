<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum_Post
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Forum_Post extends View_Article {

	/**
	 * @var  Model_User
	 */
	protected $author;

	/**
	 * @var  Model_Forum_Post
	 */
	public $forum_post;

	/**
	 * @var  Model_Forum_Topic
	 */
	public $forum_topic;

	/**
	 * @var  boolean  Current user's post
	 */
	protected $my = false;

	/**
	 * @var  integer  Nth post in topic
	 */
	public $nth;

	/**
	 * @var  boolean  Topic owner's post
	 */
	protected $owner = false;

	/**
	 * @var  boolean  Private post
	 */
	public $private = false;


	/**
	 * Create new view.
	 *
	 * @param  Model_Forum_Post   $forum_post
	 * @param  Model_Forum_Topic  $forum_topic
	 */
	public function __construct(Model_Forum_Post $forum_post, Model_Forum_Topic $forum_topic) {
		parent::__construct();

		$this->forum_post  = $forum_post;
		$this->forum_topic = $forum_topic;

		// Get post author
		$this->author = Model_User::find_user($this->forum_post->author_id);

		// Viewer's post
		$this->my = self::$_user && $this->author && $this->author->id == self::$_user_id;

		// Topic author's post
		$this->owner = $this->author
			? $this->author->id == $this->forum_topic->author_id
			: $this->forum_post->author_name == $this->forum_topic->author_name;


		$this->id    = 'post-' . $this->forum_post->id;
		$this->class = 'comment permalink' . ($this->owner ? ' owner' : '') . ($this->my ? ' my' : '');
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		$bbcode = BB::factory();
		$footer = $this->author && $this->author->signature;

		ob_start();

		if ($this->author):
			echo HTML::avatar($this->author->avatar, $this->author->username);
		else:
			echo HTML::avatar(false);
		endif;

/*
	<p>
		<small><?= __('Posts: :posts', array(':posts' => '<var>' . Num::format($this->author->post_count, 0) . '</var>')) ?></small>
	</p>
*/

?>

<div class="content">
	<header class="ui top attached secondary segment" <?= $this->forum_post->id == $this->forum_topic->last_post_id ? ' id="last"' : '' ?>>

		<?php if ($this->author):
				echo HTML::user($this->author->light_array(), null, array('class' => 'author'));
				if ($this->author->title):
					echo ' <small>&ldquo;' . HTML::chars($this->author->title) . '&rdquo;</small>';
				endif;
			else:
				echo $this->forum_post->author_name;
				echo ' <small>&ldquo;' . __('Guest') . '&rdquo;</small>';
			endif; ?>

		<div class="metadata">
			<div class="actions">

				&bull;

				<?= HTML::anchor(
					Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
						'id'       => Route::model_id($this->forum_post),
						'topic_id' => Route::model_id($this->forum_topic)
					)) . '#post-' . $this->forum_post->id,
					'#' . $this->nth,
					array('title' => __('Permalink'))) ?>

				&bull;

				<?php if (Permission::has($this->forum_topic, Model_Forum_Topic::PERMISSION_POST, self::$_user)) echo HTML::anchor(
						Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
							'id'       => Route::model_id($this->forum_post),
							'topic_id' => Route::model_id($this->forum_topic),
							'action'   => 'quote')),
							__('Reply'),
						array('class' => 'post-quote')) ?>

				<?php if (Permission::has($this->forum_post, Model_Forum_Post::PERMISSION_UPDATE, self::$_user)) echo HTML::anchor(
						Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
							'id'       => Route::model_id($this->forum_post),
							'topic_id' => Route::model_id($this->forum_topic),
							'action'   => 'edit')),
							__('Edit'),
						array('class' => 'post-edit')) ?>

				<?php if (Permission::has($this->forum_post, Model_Forum_Post::PERMISSION_DELETE, self::$_user)) echo HTML::anchor(
						Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
							'id'       => Route::model_id($this->forum_post),
							'topic_id' => Route::model_id($this->forum_topic),
							'action'   => 'delete')) . '?token=' . Security::csrf(),
							__('Delete'),
						array('class' => 'post-delete')) ?>

				&bull;

			</div>

			<div class="date">
				<?= HTML::time(Date::short_span($this->forum_post->created, true, true), $this->forum_post->created) ?>
				<?php if ($this->forum_post->modify_count): ?>
				&bull;
				<span title="<?= __($this->forum_post->modify_count == 1 ? ':edits edit, :ago' : ':edits edits, last :ago', array(
						':edits' => $this->forum_post->modify_count,
						':ago'   => Date::fuzzy_span($this->forum_post->modified)
				)) ?>"><?= __('Edited') ?></span>
				<?php endif; ?>
			</div>
		</div>

	</header>

	<div class="ui <?= $footer ? '' : 'bottom' ?> attached segment text">

		<?php if ($this->forum_post->parent_id) echo '<p class="muted">' . __('Replying to :parent', array(
			':parent' => HTML::anchor(
				Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
					'topic_id' => Route::model_id($this->forum_topic),
					'id'       => $this->forum_post->parent_id)) . '#post-' . $this->forum_post->parent_id,
				HTML::chars($this->forum_post->parent()->topic()->name)
			))) . ':</p>' ?>

		<?= $bbcode->render($this->forum_post->post) ?>

		<?php if ($this->forum_post->attachment):
				$attachment = 'images/liitteet/' . $this->forum_post->attachment;
				if (file_exists($attachment)):
					echo HTML::image($attachment);
				endif;
			endif; ?>

	</div>

	<?php if ($footer): ?>
	<footer class="ui bottom attached secondary segment">
		<?= $this->author && $this->author->signature ? $bbcode->render($this->author->signature, true) : '' ?>
	</footer>
	<?php endif; ?>

</div>

<?php

		return ob_get_clean();
	}

}
