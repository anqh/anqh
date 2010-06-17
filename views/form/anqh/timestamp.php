<?php echo Form::input_wrap(
	$name,
	strtotime($value) ? date($pretty_format, $value) : $value,
	$attributes + Form::attributes($field),
	isset($label) ? $label : '',
	isset($errors) ? $errors : '',
	isset($tip) ? $tip : ''
);
