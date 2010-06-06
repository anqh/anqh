<?php echo Form::textarea_wrap(
	$name,
	$value,
	$attributes + Form::attributes($field),
	true,
	isset($label) ? $label : '',
	isset($errors) ? $errors : '',
	isset($tip) ? $tip : ''
);
