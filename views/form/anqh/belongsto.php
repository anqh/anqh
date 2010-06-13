<?php echo Form::select_wrap(
	$name,
	$field->null ? array('' => '') + $options : $options,
	$value->id(),
	$attributes + Form::attributes($field),
	isset($label) ? $label : '',
	isset($errors) ? $errors : '',
	isset($tip) ? $tip : ''
);
