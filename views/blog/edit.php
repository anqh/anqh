<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Edit blog entry
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open();
?>

	<fieldset>
		<ul>
			<?php echo Form::input_wrap('name', $entry, array('tabindex' => 1), __('Title'), $errors) ?>
			<?php echo Form::textarea_wrap('content', $entry, array('tabindex' => 2), true, __('Content'), $errors, null, true) ?>
		</ul>
	</fieldset>

	<fieldset>
		<?php echo Form::csrf(); ?>
		<?php echo Form::submit_wrap('save', __('Save'), array('tabindex' => 3), $cancel, array('tabindex' => 4)) ?>
	</fieldset>

<?php
echo Form::close();
