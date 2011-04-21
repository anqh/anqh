<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Edit post
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

// Create form attributes
$attributes = array();
isset($form_id) and $attributes['id'] = $form_id;
//isset($ajax) and $attributes['class'] = 'ajaxify';

echo Form::open(isset($action) ? $action : null, $attributes);
?>

	<fieldset>
		<ul>
			<?php echo Form::textarea_wrap('post', $post, null, true, null, $errors, null, true) ?>
		</ul>
	</fieldset>

	<fieldset>
		<?php echo Form::csrf() ?>
		<?php echo Form::submit_wrap('save', __('Save'), null, $cancel) ?>
	</fieldset>

<?php
echo Form::close();
