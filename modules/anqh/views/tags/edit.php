<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Tag edit
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open();
?>

	<fieldset>
		<ul>
			<?php echo Form::input_wrap('name', $tag, array('maxlength' => 32), __('Name'), $errors) ?>
			<?php echo Form::input_wrap('description', $tag, null, __('Description'), $errors) ?>
		</ul>
	</fieldset>

	<fieldset>
		<?php echo Form::submit_wrap('save', __('Save'), null, Request::back(Route::get('tags')->uri(), true)) ?>
	</fieldset>

<?php
echo Form::close();
