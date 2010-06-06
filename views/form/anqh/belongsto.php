<?php echo Form::select_wrap(
	$name,
	$options,
	$value->id(),
	$attributes + Form::attributes($field),
	isset($label) ? $label : '',
	isset($errors) ? $errors : '',
	isset($tip) ? $tip : ''
);
