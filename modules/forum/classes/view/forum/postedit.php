<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum Edit Post.
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
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
		$attributes = array('class' => 'media');
		if ($this->form_id):
			$attributes['id'] = $this->form_id;
		endif;

		$button = __('Save');

		$author = self::$_user;

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
					$author = $this->forum_post->author();
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

<div class="panel panel-danger">
	<header class="panel-heading"><?= __('Admin tools') ?></header>
	<fieldset class="form-horizontal panel-body">
		<div class="col-md-6">
			<?= Form::radios_wrap(
				'sticky',
				array(
					Model_Forum_Topic::STICKY_NORMAL => __('Normal'),
					Model_Forum_Topic::STICKY_STICKY => '<i class="fa fa-thumb-tack text-warning"></i> ' . __('Pinned'),
				),
				(int)$this->forum_topic->sticky,
				array('class' => 'radios'),
				__('Pinning'),
				Arr::get($this->errors, 'sticky'),
				null,
				'radio-inline'
			) ?>
		</div>

		<div class="col-md-6">
			<?= Form::radios_wrap(
				'status',
				array(
					Model_Forum_Topic::STATUS_NORMAL => __('Normal'),
					Model_Forum_Topic::STATUS_SINK   => '<i class="fa fa-unlock text-muted"></i> ' . __('Sink'),
					Model_Forum_Topic::STATUS_LOCKED => '<i class="fa fa-lock text-muted"></i> ' . __('Locked'),
				),
				(int)$this->forum_topic->status,
				array('class' => 'radios'),
				__('Status'),
				Arr::get($this->errors, 'status'),
				null,
				'radio-inline'
			) ?>
		</div>

		<div class="col-md-12">
			<?php if (!$this->private) echo Form::select_wrap(
					'forum_area_id',
					$areas,
					$this->forum_topic->forum_area_id,
					null,
					__('Area'),
					Arr::get($this->errors, 'forum_area_id')
			) ?>
		</div>

	</fieldset>
</div>

		<?php endif; // admin ?>

	<?= Form::input_wrap(
			'name',
			$this->forum_topic->name,
			null,
			__('Topic'),
			Arr::get($this->errors, 'name')
	) ?>

	<?php if ($this->private) echo Form::textarea_wrap(
			'recipients',
			$this->recipients,
			array('rows' => 3, 'placeholder' => __('Required')),
			true,
			__('Recipients'),
			Arr::get($this->errors, 'recipients')
	) ?>

	<?php if ($this->mode === self::EDIT_TOPIC && !$is_admin): ?>

<fieldset>
	<?= Form::button('save', $button, array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
	<?= Form::button('preview', __('Preview'), array('class' => 'btn btn-default btn-large')) ?>
	<?= $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>

	<?= Form::csrf() ?>
</fieldset>

<?php

					break;
				endif;

			// Replying to a topic
			case self::REPLY:
			case self::QUOTE:

?>

<div class="pull-left">
	<?= HTML::avatar(self::$_user->avatar, self::$_user->username) ?>
</div>

<?php

			// Editing old post
			case self::EDIT_POST:

?>

<div class="post-edit media-body panel panel-success form-vertical">
	<header class="panel-heading">
		<?= $author ? HTML::user($author) : HTML::chars($this->forum_post->author_name) ?>
	</header>

	<fieldset class="panel-body">
		<?= Form::textarea_wrap(
			'post',
			$this->forum_post->post,
			array('id' => uniqid()),
			true,
			null,
			Arr::get($this->errors, 'post'),
			null,
			true
		) ?>
	</fieldset>

	<fieldset class="panel-body">
		<?= Form::button('save', $button, array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
		<?= Form::button('preview', __('Preview'), array('class' => 'btn btn-default btn-large')) ?>
		<?= $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>

		<?= Form::csrf() ?>
	</fieldset>
</div>

<?php

		endswitch;

?>


<?php

		echo Form::close();

		// Auto-complete recipients
		if ($this->private):

?>

<script>
head.ready('anqh', function() {
	$('textarea[name=recipients]').autocompleteUser({
		user: <?= self::$_user_id ?>,
		maxUsers: 100
	});
});
</script>

<?php

		endif;

		return ob_get_clean();
	}

}
