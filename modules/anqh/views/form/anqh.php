<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh form builder
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

// Add ajaxify class?
$form_attributes = Arr::get($form, 'attributes', array());
if (Arr::get($form, 'ajaxify')) {
	$form_attributes['class'] = trim(Arr::get($form_attributes, 'class') . ' ajaxify');
	$cancel_attributes = array('class' => 'ajaxify');
} else {
	$cancel_attributes = null;
}

echo Form::open(Arr::get($form, 'action'), $form_attributes);

foreach ($form['groups'] as $group_name => $group):

	// Print HTML block if any
	if (isset($group['html'])):
		echo $group['html'];
		continue;
	endif;

	$group_attributes = Arr::get($group, 'attributes', array());
	if (is_string($group_name)) {
		$group_attributes += array('id' => 'fields-' . $group_name);
	}
?>

	<fieldset<?php echo HTML::attributes($group_attributes) ?>>
		<?php if (isset($group['header'])): ?> <legend><?php echo HTML::chars($group['header']) ?></legend><?php endif; ?>

		<ul>
		<?php foreach ($group['fields'] as $field_name => $field):
			$model = Arr::get($field, 'model', $form['values']);
			$name  = Arr::get($field, 'column', $field_name)?>

			<?php if ($model) echo $model->input($name, 'form/anqh', $field + array('errors' => Arr::get($form, 'errors'))) ?>

		<?php endforeach; ?>
		</ul>
	</fieldset>
<?php endforeach; ?>

	<fieldset>

		<?php echo Form::csrf(Arr::path($form, 'csrf.id'), Arr::path($form, 'csrf.action')) ?>
		<?php if (!isset($form['save']) || $form['save'] !== false)
			echo Form::submit_wrap(
				'save', Arr::path($form, 'save.label', __('Save')), Arr::path($form, 'save.attributes'),
				Arr::get($form, 'cancel'), $cancel_attributes,
				Arr::get($form, 'hidden')) ?>

	</fieldset>

<?php
echo Form::close();
