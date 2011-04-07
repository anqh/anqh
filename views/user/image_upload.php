<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Upload flyer
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

$attributes = array('enctype' => 'multipart/form-data');
if ($ajaxify) {
//	$attributes['class'] = 'ajaxify';
	$cancel_attributes = array('class' => 'ajaxify');
} else {
	$cancel_attributes = null;
}

echo Form::open(null, $attributes);
?>

	<fieldset>
		<ul>
			<?php echo Form::file_wrap('file', null, __('Image'), $errors) ?>
		</ul>
	</fieldset>

	<fieldset>
		<?php echo Form::submit_wrap('save', __('Upload'), null, $cancel, $cancel_attributes) ?>
	</fieldset>

<?php
echo Form::close();
