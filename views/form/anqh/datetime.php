<?php
if (!isset($show_date) || $show_date):
	echo Form::input_wrap(
		$name . '[date]',
		is_numeric($value) ? Date::format('DMYYYY', $value) : $value,
		$attributes + array('class' => 'date', 'maxlength' => 10) + Form::attributes($field),
		isset($label_date) ? $label_date : '',
		isset($errors) ? Arr::get($errors, $name) : '',
		isset($tip_date) ? $tip_date : ''
	);
endif;

if (!isset($show_time) || $show_time):
	/*echo Form::input_wrap(
		$name . '[time]',
		is_numeric($value) ? Date::format('HHMM', $value) : $value,
		$attributes + array('class' => 'time', 'maxlength' => 5) + Form::attributes($field),
		isset($label_time) ? $label_time : '',
		isset($errors) ? Arr::get($errors, $name) : '',
		isset($tip_time) ? $tip_time : ''
	);*/

	echo Form::select_wrap(
		$name . '[time]',
		Date::hours_minutes(30, true),
		is_numeric($value) ? Date::format('HHMM', $value) : (empty($value) && isset($default_time) ? $default_time : $value),
		$attributes + array('class' => 'time') + Form::attributes($field),
		isset($label_time) ? $label_time : '',
		isset($errors) ? Arr::get($errors, $name) : '',
		isset($tip_time) ? $tip_time : ''
	);
endif;
