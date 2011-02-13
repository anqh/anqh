<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Comments
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php echo Form::open() ?>
<fieldset class="horizontal">
	<ul>
		<?php if (isset($private) && $private): ?>
		<?php echo Form::checkbox_wrap('private', '1', $values, array('onchange' => "\$('input[name=comment]').toggleClass('private', this.checked)\""), '<abbr class="private" title="' . __('Private comment') . '">' . __('Priv') . '</abbr>') ?>
		<?php endif; ?>

		<?php echo Form::input_wrap('comment', $values, array('maxlength' => 300), null, $errors) ?>

		<li><?php echo Form::submit(false, __('Comment')) ?></li>
	</ul>
	<?php echo Form::csrf() ?>
</fieldset>
<?php echo Form::close() ?>

<?php foreach ($comments as $comment):
	$author = Model_User::find_user_light($comment->original('author'));

	// Ignore
	if ($user && $user->is_ignored($author)) continue;

	$classes = array();

	if ($comment->private) {
		$classes[] = 'private';
	}

	// Viewer's post
	if ($user && $author['id'] == $user->id) {
		$classes[] = 'my';
		$mine = true;
	} else {
		$mine = false;
	}

	// Topic author's post
	if ($author['id'] == $comment->original('user')) {
		$classes[] = 'owner';
	}
 ?>

<article id="comment-<?php echo $comment->id ?>" class="<?php echo implode(' ', $classes) ?>">
	<?php echo HTML::avatar($author['avatar'], $author['username'], true) ?>
	<?php echo HTML::user($author) ?>
	<small class="ago"><?php echo HTML::time(Date::short_span($comment->created, true, true), $comment->created) ?></small>

	<?php if ($user && $comment->user->id == $user->id || $mine): ?>
	<nav class="actions inline">
		<?php if ($private && !$comment->private): ?>
		<?php echo HTML::anchor(sprintf($private, $comment->id), __('Set as private'), array('class' => 'action small comment-private')) ?>
		<?php endif; ?>
		<?php echo HTML::anchor(sprintf($delete, $comment->id), __('Delete'), array('class' => 'action small comment-delete')) ?>
	</nav>
	<?php endif; ?>

	<p>
		<?php echo $comment->private ? '<abbr title="' . __('Private comment') . '">' . __('Priv') . '</abbr>: ' : '' ?>
		<?php echo Text::smileys(Text::auto_link_urls(HTML::chars($comment->comment))) ?>
	</p>
</article>

<?php endforeach; ?>
