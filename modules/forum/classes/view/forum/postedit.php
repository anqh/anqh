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
	public $class = 'media speech';

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

		// Create form attributes
		$attributes = array('class' => 'form-horizontal small-labels');
		if ($this->form_id):
			$attributes['id'] = $this->form_id;
		endif;

		$button = __('Save');

		if (!$this->form_action):
			switch ($this->mode):

				case self::QUOTE:
					$this->form_action = Route::url($this->private ? 'forum_private_post' : 'forum_post', array(
						'topic_id' => $this->forum_topic->id,
						'id'       => $this->forum_post->parent_id,
						'action'   => 'quote'
					));
					$button = __('Reply');
					break;

				case self::REPLY:
					$this->form_action = Route::model($this->forum_topic, 'reply');
					$button = __('Reply');
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

				if ($is_admin):

					// Build available areas list
					$areas = array();
					foreach (Model_Forum_Group::factory()->find_all() as $_group):
						$areas[$_group->name] = array();
						foreach ($_group->areas() as $_area):
							$areas[$_group->name][$_area->id] = $_area->name;
						endforeach;
					endforeach;


?>

<fieldset class="row-fluid">
	<div class="span4">
		<?= Form::control_group(
					Form::select('status', array(
						Model_Forum_Topic::STATUS_NORMAL => __('Normal'),
						Model_Forum_Topic::STATUS_SINK   => __('Sink'),
						Model_Forum_Topic::STATUS_LOCKED => __('Locked'),
					), $this->forum_topic->status, array('class' => 'input-small')),
					array('status' => __('Status')),
					Arr::get($this->errors, 'status')); ?>
	</div>

	<div class="span4">
		<?= Form::control_group(
			Form::select('sticky', array(
				Model_Forum_Topic::STICKY_NORMAL => __('Normal'),
				Model_Forum_Topic::STICKY_STICKY => __('Sticky'),
			), $this->forum_topic->sticky, array('class' => 'input-small')),
			array('sticky' => __('Stickyness')),
			Arr::get($this->errors, 'sticky')); ?>
	</div>
</fieldset>

	<?= Form::control_group(
		Form::select('forum_area_id', $areas, $this->forum_topic->forum_area_id, array('class' => 'input-block-level')),
		array('forum_area_id' => __('Area')),
		Arr::get($this->errors, 'forum_area_id')) ?>

		<?php endif; ?>

	<?= Form::control_group(
		Form::input('name', $this->forum_topic->name, array('class' => 'input-block-level')),
		array('name' => __('Topic')),
		Arr::get($this->errors, 'name')) ?>

	<?php if ($this->private) echo Form::control_group(
		Form::textarea('recipients', $this->recipients, array('rows' => 3, 'placeholder' => __('Required'), 'class' => 'input-block-level'), true),
		array('recipients' => __('Recipients')),
		Arr::get($this->errors, 'recipients')) ?>

<?php

				if ($this->mode === self::EDIT_TOPIC && !$is_admin):
					break;
				endif;

			// Replying to a topic
			case self::REPLY:
			case self::QUOTE:

?>

<div class="pull-left">
	<?= HTML::avatar(self::$_user->avatar, self::$_user->username) ?>
</div>

<div class="arrow"></div>

<?php

			// Editing old post
			case self::EDIT_POST:

?>

<div class="post-edit media-body form-vertical">
	<header>
		<?= HTML::user(self::$_user) ?>
	</header>

	<fieldset>
		<?= Form::control_group(
			Form::textarea_editor('post', $this->forum_post->post, array('id' => uniqid(), 'class' => 'input-block-level'), true),
			null,
			Arr::get($this->errors, 'post')) ?>
	</fieldset>

	<fieldset class="form-actions">
		<?= Form::button('save', $button, array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
		<?= Form::button('preview', __('Preview'), array('class' => 'btn btn-inverse btn-large')) ?>
		<?= $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>

		<?= Form::csrf() ?>
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
