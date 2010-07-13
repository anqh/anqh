<?php echo Form::file_wrap(
	$name,
	$attributes + Form::attributes($field),
	isset($label) ? $label : '',
	isset($errors) ? $errors : '',
	isset($tip) ? $tip : ''
);
