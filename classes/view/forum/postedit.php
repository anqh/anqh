<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum_PostEdit
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Forum_PostEdit extends View_Article {

	/** Editing an old post */
	const EDIT_POST = 'edit_post';

	/** Editing an old topic */
	const EDIT_TOPIC = 'edit_topic';

	/** Creating a new topic */
	const NEW_TOPIC = 'new_topic';

	/** Quoting a post */
	const QUOTE = 'quote';

	/** Replying to a post */
	const REPLY = 'reply';

	/**
	 * @var  string
	 */
	public $cancel;

	/**
	 * @var  string  View class
	 */
	public $class = 'row';

	/**
	 * @var  array
	 */
	public $errors;

	/**
	 * @var  string
	 */
	public $form_action;

	/**
	 * @var  string
	 */
	public $form_id;

	/**
	 * @var  Model_Forum_Area  Needed when adding a new topic
	 */
	public $forum_area;

	/**
	 * @var  Model_Forum_Post  Needed always
	 */
	public $forum_post;

	/**
	 * @var  Model_Forum_Topic  Needed when replying to a topic
	 */
	public $forum_topic;

	/**
	 * @var  string
	 */
	public $mode;

	/**
	 * @var  Model_Forum_Post  Needed when quoting a post
	 */
	public $parent_post;

	/**
	 * @var  boolean
	 */
	public $private = false;

	/**
	 * @var  string
	 */
	public $recipients;


	/**
	 * Create new view.
	 *
	 * @param  string            $mode
	 * @param  Model_Forum_Post  $forum_post
	 *
	 * @throws  InvalidArgumentException
	 */
	public function __construct($mode, Model_Forum_Post $forum_post = null) {
		parent::__construct();

		if (!in_array($mode, array(self::EDIT_POST, self::EDIT_TOPIC, self::NEW_TOPIC, self::QUOTE, self::REPLY))) {
			throw new InvalidArgumentException('Invalid edit mode: ' . $mode);
		}

		$this->mode       = $mode;
		$this->forum_post = $forum_post;
		$this->class     .= ' ' . $this->mode;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		Form::$bootsrap = true;

		// Create form attributes
		$attributes = array();
		if ($this->form_id):
			$attributes['id'] = $this->form_id;
		endif;

		if (!$this->form_action):
			switch ($this->mode):

				case self::QUOTE:
					$this->form_action = Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
						'topic_id' => $this->forum_topic->id,
						'id'       => $this->forum_post->parent_id,
						'action'   => 'quote'
					));
					break;

				case self::REPLY:
					$this->form_action = Route::model($this->forum_topic, 'reply');
					break;

				case self::EDIT_POST:
					$this->form_action = Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
						'topic_id' => $this->forum_topic->id,
						'id'       => $this->forum_post->id,
						'action'   => 'edit'
					));
					break;

			endswitch;
		endif;
		echo Form::open($this->form_action ? $this->form_action : null, $attributes);


		// Progressively add content, note that we don't break
		switch ($this->mode):

			// Adding new topic
			case self::NEW_TOPIC:

			// Editing an old topic
			case self::EDIT_TOPIC:
				$is_admin = self::$_user->has_role(array('admin', 'moderator', 'forum moderator'));

?>

<div class="span8">

	<fieldset>
		<?php if ($is_admin):
			echo Form::control_group(
				Form::select('status', array(
					Model_Forum_Topic::STATUS_NORMAL => __('Normal'),
					Model_Forum_Topic::STATUS_SINK   => __('Sink'),
					Model_Forum_Topic::STATUS_LOCKED => __('Locked'),
				), $this->forum_topic->status, array('class' => 'input-small')),
				array('status' => __('Status')),
				Arr::get($this->errors, 'status'));

			echo Form::control_group(
				Form::select('sticky', array(
					Model_Forum_Topic::STICKY_NORMAL => __('Normal'),
					Model_Forum_Topic::STICKY_STICKY => __('Sticky'),
				), $this->forum_topic->sticky, array('class' => 'input-small')),
				array('sticky' => __('Stickyness')),
				Arr::get($this->errors, 'sticky'));
		endif; ?>

		<?php echo Form::control_group(
			Form::input('name', $this->forum_topic->name, array('class' => 'input-max')),
			array('name' => __('Topic')),
			Arr::get($this->errors, 'name')) ?>

		<?php if ($this->private) echo Form::control_group(
			Form::textarea('recipients', $this->recipients, array('rows' => 3, 'placeholder' => __('Required'), 'class' => 'input-max'), true),
			array('recipients' => __('Recipients')),
			Arr::get($this->errors, 'recipients')) ?>

	</fieldset>

</div>

<?php

				if ($this->mode === self::EDIT_TOPIC && !$is_admin):
					break;
				endif;

			// Replying to a topic
			case self::REPLY:
			case self::QUOTE:

?>

<div class="author span2">
	<?= HTML::avatar(self::$_user->avatar, self::$_user->username) ?>
	<?= HTML::user(self::$_user) ?>
</div>

<?php

			// Editing old post
			case self::EDIT_POST:

?>

<div class="post-edit span6">

	<fieldset>
		<?php echo Form::control_group(
			Form::textarea_editor('post', $this->forum_post->post, array('id' => uniqid(), 'class' => 'input-max'), true),
			null,
			Arr::get($this->errors, 'post')) ?>
	</fieldset>

	<fieldset class="form-actions">
		<?php echo Form::button('save', __('Save'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
		<?php echo $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>

		<?php echo Form::csrf() ?>
	</fieldset>

</div>

<?php

		endswitch;

		echo Form::close();

		// Auto-complete recipients
		if ($this->private):

?>

<script>
head.ready('anqh', function() {
	$('textarea[name=recipients]').autocompleteUser({ user: <?= self::$_user_id ?>, maxUsers: 100 });
});
</script>

<?php

		endif;

		return ob_get_clean();
	}

}
