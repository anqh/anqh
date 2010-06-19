<?php
if (!isset($show_date) || $show_date):
	echo Form::input_wrap(
		$name . '[date]',
		is_numeric($value) ? Date::format('DMYYYY', $value) : $value,
		$attributes + Form::attributes($field),
		isset($label_date) ? $label_date : '',
		isset($errors) ? Arr::get($errors, $name) : '',
		isset($tip_date) ? $tip_date : ''
	);
endif;

if (!isset($show_time) || $show_time):
	echo Form::input_wrap(
		$name . '[time]',
		is_numeric($value) ? Date::format('HHMM', $value) : $value,
		$attributes + Form::attributes($field),
		isset($label_time) ? $label_time : '',
		isset($errors) ? Arr::get($errors, $name) : '',
		isset($tip_time) ? $tip_time : ''
	);
endif;
