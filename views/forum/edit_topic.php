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
			<?php echo Form::input_wrap('name', $topic, array('tabindex' => ++$tabindex), __('Topic'), $errors) ?>
			<?php if (!$post):
				echo Form::select_wrap('status', array(
					Model_Forum_Topic::STATUS_NORMAL => __('Normal'),
					Model_Forum_Topic::STATUS_SINK   => __('Sink'),
					Model_Forum_Topic::STATUS_LOCKED => __('Locked'),
				), $topic, array('tabindex' => ++$tabindex), __('Status'), $errors);
				echo Form::radios_wrap('sticky', array(0 => __('Normal'), 1 => __('Sticky')), $topic->sticky, null, __('Sticky'), $errors);
			endif; ?>
			<?php if ($private) echo Form::textarea_wrap('recipients', $recipients, array('rows' => 3, 'placeholder' => __('Required'), 'tabindex' => ++$tabindex), null, __('Recipients'), $errors) ?>
		</ul>
	</fieldset>

	<?php if ($post): ?>
	<fieldset id="fields-post">
		<ul>
			<?php echo Form::textarea_wrap('post', $post, array('tabindex' => ++$tabindex), true, __('Post'), $errors) ?>
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
