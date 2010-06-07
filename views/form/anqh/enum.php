<?php echo Form::select_wrap(
	$name,
	$choices,
	$value,
	$attributes + Form::attributes($field),
	isset($label) ? $label : '',
	isset($errors) ? $errors : '',
	isset($tip) ? $tip : ''
);
