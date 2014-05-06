<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum Post.
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
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
		$this->my = Visitor::$user && $this->author && $this->author->id == Visitor::$user->id;

		// Topic author's post
		$this->owner = $this->author
			? $this->author->id == $this->forum_topic->author_id
			: $this->forum_post->author_name == $this->forum_topic->author_name;


		$this->id    = 'post-' . $this->forum_post->id;
		$this->class = 'media permalink post' . ($this->owner ? ' owner' : '') . ($this->my ? ' my' : '');
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		$bbcode = BB::factory();

		ob_start();

		if ($this->my):
			$panel_class = 'panel-success';
		elseif ($this->owner):
			$panel_class = 'panel-info';
		else:
			$panel_class = 'panel-default';
		endif;

?>

<div class="pull-left">

	<?php if ($this->author): ?>
		<?= HTML::avatar($this->author->avatar, $this->author->username) ?>

		<p>
			<small><?= __('Posts: :posts', array(':posts' => '<var>' . Num::format($this->author->post_count, 0) . '</var>')) ?></small>
		</p>
	<?php else: ?>
		<?= HTML::avatar(false) ?>

	<?php endif; ?>

</div>

<div class="media-body panel <?= $panel_class ?>">
	<header class="panel-heading"<?= $this->forum_post->id == $this->forum_topic->last_post_id ? ' id="last"' : '' ?>>
		<small class="pull-right">
			<?= HTML::anchor(
				Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
					'id'       => Route::model_id($this->forum_post),
					'topic_id' => Route::model_id($this->forum_topic)
				)) . '#post-' . $this->forum_post->id,
				'#' . $this->nth,
				array('title' => __('Permalink'))) ?>

			&bull;

			<?php if (Permission::has($this->forum_post, Model_Forum_Post::PERMISSION_UPDATE)) echo HTML::anchor(
					Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
						'id'       => Route::model_id($this->forum_post),
						'topic_id' => Route::model_id($this->forum_topic),
						'action'   => 'edit')),
						__('Edit'),
					array('class' => 'post-edit')) ?>

			<?php if (Permission::has($this->forum_post, Model_Forum_Post::PERMISSION_DELETE)) echo HTML::anchor(
					Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
						'id'       => Route::model_id($this->forum_post),
						'topic_id' => Route::model_id($this->forum_topic),
						'action'   => 'delete')) . '?token=' . Security::csrf(),
						__('Delete'),
					array('class' => 'post-delete')) ?>

			<?php if (Permission::has($this->forum_topic, Model_Forum_Topic::PERMISSION_POST)) echo HTML::anchor(
					Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
						'id'       => Route::model_id($this->forum_post),
						'topic_id' => Route::model_id($this->forum_topic),
						'action'   => 'quote')),
						__('Reply'),
					array('class' => 'post-quote')) ?>

			&bull;

			<?= HTML::time(Date::short_span($this->forum_post->created, true, true), $this->forum_post->created) ?>

			<?php if ($this->forum_post->modify_count > 0): ?>
			&bull;
			<span title="<?= __($this->forum_post->modify_count == 1 ? ':edits edit, :ago' : ':edits edits, last :ago', array(
				':edits' => $this->forum_post->modify_count,
				':ago'   => Date::fuzzy_span($this->forum_post->modified)
			)) ?>"><?= __('Edited') ?></span>
			<?php endif; ?>

		</small>

		<?php if ($this->author):
				echo HTML::user($this->author->light_array());
				if ($this->author->title):
					echo ' <small>' . HTML::chars($this->author->title) . '</small>';
				endif;
			else:
				echo $this->forum_post->author_name;
				echo ' <small>' . __('Guest') . '</small>';
			endif; ?>
	</header>

	<div class="panel-body">

		<?php if ($this->forum_post->parent_id) echo '<p class="text-muted">' . __('Replying to :parent', array(
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

	<?php if ($this->author && $this->author->signature): ?>
	<footer class="panel-body">
		<?= $this->author && $this->author->signature ? $bbcode->render("\n--\n" . $this->author->signature, true) : '' ?>
	</footer>
	<?php endif; ?>
</div>

<?php

		return ob_get_clean();
	}

}
