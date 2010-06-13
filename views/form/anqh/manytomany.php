<?php

// Load all values if not already set
if (!isset($values)):
	$values = array();
	foreach (Jelly::select($foreign['model'])->execute() as $related):
		$values[$related->id()] = $related->name();
	endforeach;
endif;

// Set checked values
$checked = array_flip($ids);

echo Form::checkboxes_wrap(
	$name,
	$values,
	$checked,
	isset($label) ? $label : '',
	isset($errors) ? $errors : '',
	isset($tip) ? $tip : '',
	isset($class) ? $class : ''
);
