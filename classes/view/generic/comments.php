<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Generic_Comments
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_Comments extends View_Section {

	/**
	 * @var  string  Section class
	 */
	public $class = 'comments';

	/**
	 * @var  Model_Comment[]
	 */
	public $comments;

	/**
	 * @var  string  Delete comment url template
	 */
	public $delete = null;

	/**
	 * @var  string  Private comment url template
	 */
	public $private = null;

	/**
	 * @var  array  Form values
	 */
	public $values;


	/**
	 * Create new view.
	 *
	 * @param  Model_Comment[]  $comments
	 */
	public function __construct($comments) {
		parent::__construct();

		$this->comments = $comments;
		$this->title    = __('Comments');
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		Form::$bootsrap = true;

		ob_start();

		// Comment form
		echo $this->form();

?>

<ul class="unstyled">

<?php

		$new_comments = isset($new_comments) ? (int)$new_comments : 0;
		foreach ($this->comments as $comment):
			/** @var  Model_Comment  $comment */
			$author = $comment->author();

			// Ignore
			if (self::$_user && self::$_user->is_ignored($author)) continue;

			$classes = array('row-fluid');

			// Private comment?
			if ($comment->private) {
				$classes[] = 'private';
			}

			// Viewer's post
			if (self::$_user_id && $author['id'] == self::$_user_id) {
				$classes[] = 'my';
			}

			// Topic author's post
			if ($author['id'] == $comment->user_id) {
				$classes[] = 'owner';
			}

			// New comment?
			if ($new_comments-- > 0) {
				$classes[] = 'new';
			}

?>

	<li class="<?= implode(' ', $classes) ?>" id="comment-<?= $comment->id ?>">
		<?= HTML::avatar($author['avatar'], $author['username'], true) ?>
		<?= HTML::user($author) ?>
		<small class="ago"><?= in_array('new', $classes) ? __('New') : '' ?> <?= HTML::time(Date::short_span($comment->created, true, true), $comment->created) ?></small>

		<?php if (self::$_user_id && $comment->user_id == self::$_user_id || in_array('my', $classes)): ?>
		<span class="actions transparent">

<?php

			if ($this->private && !$comment->private):
				echo HTML::anchor(sprintf($this->private, $comment->id), __('Set as private'), array('class' => 'btn btn-special btn-mini comment-private'));
			endif;

			if ($this->delete):
				echo HTML::anchor(sprintf($this->delete, $comment->id), __('Delete'), array('class' => 'btn btn-danger btn-mini comment-delete'));
			endif;

?>

		</span>
		<?php endif; ?>

		<p>
			<?= $comment->private ? '<span class="label label-special" title="' . __('Private comment') . '">' . __('Priv') . '</span>: ' : '' ?>
			<?= Text::smileys(Text::auto_link_urls(HTML::chars($comment->comment))) ?>
		</p>
	</li>

	<?php endforeach; ?>

</ul>

<?php

		return ob_get_clean();
	}


	/**
	 * Comment form view.
	 *
	 * @return  string
	 */
	public function form() {
		ob_start();

		echo Form::open(null, array('class' => 'form-inline'));

		// Private message?
		if ($this->private) {
			$input = Form::checkbox(
				'private',
				1,
				Arr::get($this->values, 'private'),
				array(
					'id'       => 'field-private',
					'onchange' => "\$('input[name=comment]').toggleClass('private', this.checked);")
			);

			echo Form::label(
				'field-private',
				$input . ' <span class="label label-special private" title="' . __('Private comment') . '">' . __('Priv') . '</span> ',
				array('class' => 'checkbox')
			);
		}

		echo Form::input('comment', Arr::get($this->values, 'comment'), array('class' => 'input-xlarge', 'maxlength' => 300)), ' ';
		echo Form::submit(false, __('Comment'), array('class' => 'btn btn-primary'));
		echo Form::csrf();

		echo Form::close();

		return ob_get_clean();
	}

}
