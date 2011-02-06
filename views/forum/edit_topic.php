<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Topic edit
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

$tabindex = 0;
echo Form::open(null, array('id' => 'form-topic-edit'));
?>

	<fieldset id="fields-topic">
		<ul>
			<?php echo $topic->input('name', 'form/anqh', array('errors' => $errors, 'attributes' => array('tabindex' => ++$tabindex))) ?>
			<?php if (!$post) echo $topic->input('status', 'form/anqh', array('errors' => $errors, 'attributes' => array('tabindex' => ++$tabindex))) ?>
			<?php if ($private) echo Form::textarea_wrap('recipients', $recipients, array('rows' => 3, 'placeholder' => __('Required'), 'tabindex' => ++$tabindex), null, __('Recipients'), $errors) ?>
		</ul>
	</fieldset>

	<?php if ($post): ?>
	<fieldset id="fields-post">
		<ul>
			<?php echo $post->input('post', 'form/anqh', array('errors' => $errors, 'attributes' => array('tabindex' => ++$tabindex))) ?>
		</ul>
	</fieldset>
	<?php endif; ?>

	<fieldset>
		<?php echo Form::csrf() ?>
		<?php echo Form::submit_wrap('save', __('Save'), array('tabindex' => ++$tabindex), $cancel) ?>
	</fieldset>

<?php
echo Form::close();

if ($private)	echo HTML::script_source('
head.ready("anqh", function() {
	$("textarea[name=recipients]").autocompleteUser({ user: ' . $user->id . ', maxUsers: 100 });
});
');
