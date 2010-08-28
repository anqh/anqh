<?php echo Form::password_wrap(
	$name,
	$value,
	$attributes + Form::attributes($field),
	isset($label) ? $label : '',
	isset($errors) ? $errors : '',
	isset($tip) ? $tip : ''
);
