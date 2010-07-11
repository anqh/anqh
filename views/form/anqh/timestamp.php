<?php echo Form::input_wrap(
	$name,
	is_numeric($value) ? date($pretty_format, $value) : $value,
	$attributes + Form::attributes($field),
	isset($label) ? $label : '',
	isset($errors) ? $errors : '',
	isset($tip) ? $tip : ''
);
