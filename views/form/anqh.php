<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh form builder
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open(Arr::get($form, 'action'), Arr::get($form, 'attributes'));

foreach ($form['groups'] as $group_name => $group): ?>

	<fieldset<?php echo HTML::attributes(Arr::get($group, 'attributes')) ?>>
		<?php if (isset($group['header'])): ?> <legend><?php echo HTML::chars($group['header']) ?></legend<?php endif; ?>

		<ul>
		<?php foreach ($group['fields'] as $field_name => $field): $model = Arr::get($field, 'model', $form['values']); ?>

			<?php if ($model) echo $model->input($field_name, 'form/anqh', $field + array('errors' => Arr::get($form, 'errors'))) ?>

		<?php endforeach; ?>
		</ul>
	</fieldset>
<?php endforeach; ?>

	<fieldset>

		<?php echo Form::csrf(Arr::path($form, 'csrf.id'), Arr::path($form, 'csrf.action')) ?>
		<?php echo Form::submit_wrap('save', __('Save'), Arr::path($form, 'save.attributes'), Arr::get($form, 'cancel'), null, Arr::get($form, 'hidden')) ?>

	</fieldset>

<?php
echo Form::close();
