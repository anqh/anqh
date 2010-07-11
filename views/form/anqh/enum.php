<?php echo isset($input) && $input == 'radio'
	? Form::radios_wrap(
			$name,
			$choices,
			$value,
			$attributes + Form::attributes($field),
			isset($label) ? $label : '',
			isset($errors) ? $errors : '',
			isset($tip) ? $tip : ''
		)
	: Form::select_wrap(
			$name,
			$choices,
			$value,
			$attributes + Form::attributes($field),
			isset($label) ? $label : '',
			isset($errors) ? $errors : '',
			isset($tip) ? $tip : ''
		);
