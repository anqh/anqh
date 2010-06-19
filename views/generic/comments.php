<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Comments
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
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
	$classes = array();

	if ($comment->private) {
		$classes[] = 'private';
	}

	// Viewer's post
	if ($user && $comment->author->id == $user->id) {
		$classes[] = 'my';
		$mine = true;
	} else {
		$mine = false;
	}

	// Topic author's post
	if ($comment->author->id == $comment->user->id) {
		$classes[] = 'owner';
	}
 ?>

<article id="comment-<?php echo $comment->id ?>" class="<?php echo implode(' ', $classes) ?>">

	<?php echo HTML::avatar($comment->author->avatar, $comment->author->username) ?>

	<header>
		<?php if ($user && $comment->user_id == $user->id || $mine): ?>
		<span class="actions">
			<?php if ($private && !$comment->private): ?>
			<?php echo HTML::anchor(sprintf($private, $comment->id), __('Set as private'), array('class' => 'action comment-private')) ?>
			<?php endif; ?>
			<?php echo HTML::anchor(sprintf($delete, $comment->id), __('Delete'), array('class' => 'action comment-delete')) ?>
		</span>
		<?php endif; ?>

		<?php echo HTML::user($comment->author_id, $comment->author->username) ?>,
		<?php echo __(':ago', array(
			':ago' => HTML::time(Date::fuzzy_span($comment->created), $comment->created))
		) ?>
	</header>

	<p>
		<?php echo $comment->private ? '<abbr title="' . __('Private comment') . '">' . __('Priv') . '</abbr>: ' : '' ?>
		<?php echo Text::smileys(Text::auto_link_urls(HTML::chars($comment->comment))) ?>
	</p>
</article>

<?php endforeach; ?>

<?php if (isset($paginatino)): ?>
<footer>

	<?php echo $pagination ?>

</footer>
<?php
endif;

// AJAX hooks
echo HTML::script_source('
$(function() {

	$("a.comment-delete").each(function(i) {
	var action = $(this);
	action.data("action", function() {
		var comment = action.attr("href").match(/([0-9]*)\\/delete/);
		if (comment) {
			$.get(action.attr("href"), function() {
				$("#comment-" + comment[1]).slideUp();
			});
		}
	});
	});

	$("a.comment-private").live("click", function(e) {
	e.preventDefault();
	var href = $(this).attr("href");
	var comment = href.match(/([0-9]*)\\/private/);
	$(this).fadeOut()
	if (comment) {
		$.get(href, function() {
			$("#comment-" + comment[1]).addClass("private");
		});
	}
	return false;
	});
	
	$("section.comments form").live("submit", function(e) {
	e.preventDefault();
	var comment = $(this).closest("section.comments");
	$.post($(this).attr("action"), $(this).serialize(), function(data) {
		comment.replaceWith(data);
	});
	return false;
	});

});
');
