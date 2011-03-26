<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Edit group
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open();
?>

	<fieldset>
		<ul>
			<?php echo Form::input_wrap('name', $group, null, __('Name'), $errors) ?>
			<?php echo Form::input_wrap('description', $group, null, __('Description'), $errors) ?>
			<?php echo Form::input_wrap('sort', $group, null, __('Sort'), $errors) ?>
		</ul>
	</fieldset>

	<fieldset>
		<?php echo Form::submit_wrap('save', __('Save'), null, Request::back(Route::get('forum_group')->uri(), true)) ?>
	</fieldset>

<?php
echo Form::close();
