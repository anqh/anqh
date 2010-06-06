<?php echo Form::input_wrap(
	$name,
	$value,
	$attributes + Form::attributes($field),
	isset($label) ? $label : '',
	isset($errors) ? $errors : '',
	isset($tip) ? $tip : ''
);
