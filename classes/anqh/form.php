<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Form helper
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Form extends Kohana_Form {

	/**
	 * @var  array  Form errors
	 */
	public $errors = null;

	/**
	 * @var  Jelly_Model  Model being edited
	 */
	public $model = null;

	/**
	 * @var  array  Form values
	 */
	public $values = null;


	/**
	 * Build input attributes from Jelly model rules
	 *
	 * @static
	 * @param   Jelly_Model  $field
	 * @return  array
	 */
	public static function attributes(Jelly_Field $field) {
		$attributes = array();
		foreach ($field->rules as $rule => $params) {
			switch ($rule) {
				// case 'max_length': $attributes['maxlength'] = $params[0]; break; // @todo Strangely broken
				case 'not_empty':  $attributes['placeholder'] = __('Required'); $attributes['required'] = 'required'; break;
			}
		}

		if ($field instanceof Field_URL) {
			$attributes['placeholder'] = 'http://';
		}

		return $attributes;
	}


	/**
	 * Creates a button form input.
	 *
	 * @param   string        input name
	 * @param   string        input value
	 * @param   array         html attributes
	 *
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @return  string
	 */
	public static function button_wrap($name, $body, array $attributes = null, $label = null, $error = null, $tip = null) {
		$body       = is_array($body) ? Arr::get($body, $name) : $body;
		$attributes = (array)$attributes + array('id' => self::input_id($name, 'button-'));

		$input = Form::button($name, $body, $attributes);

		return Form::wrap($input, $name, $label, $error, $tip);
	}


	/**
	 * Creates a checkbox form input.
	 *
	 * @param   string         input name
	 * @param   string         input value
	 * @param   boolean|array  checked status
	 * @param   array          html attributes

	 * @param   string         $label
	 * @param   string|array   $error
	 * @param   string|array   $tip
	 * @return  string
	 */
	public static function checkbox_wrap($name, $value = null, $checked = false, array $attributes = null, $label = null, $error = null, $tip = null) {
		if (is_array($value)) {
			$value = Arr::get($value, $name);
		} else if (is_object($value)) {
			$value = $value->$name;
		}
		$checked    = is_array($checked) ? Arr::get($checked, $name) == $value : $checked;
		$attributes = (array)$attributes + array('id' => self::input_id($name));
		$label      = $label ? array($attributes['id'] => $label) : '';

		$input = Form::checkbox($name, $value, $checked, $attributes);

		return Form::wrap($input, $name, $label, $error, $tip, true);
	}


	/**
	 * Creates checkboxes list
	 *
	 * @param   string        $name     input name
	 * @param   array         $values   input values
	 * @param   array         $checked  checked statuses
	 *
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @param   string        $class
	 * @return  string
	 */
	public static function checkboxes_wrap($name, $values = array(), $checked = array(), $label = null, $error = null, $tip = null, $class = null) {
			$input = $class ? '<ul class="' . $class . "\">\n" : "<ul>\n";
			foreach ($values as $checkbox_value => $checkbox_title) {
				$id = self::input_id($name) . '-' . $checkbox_value;
				$input .= '<li>';
				$input .= Form::checkbox($name . '[]', $checkbox_value, isset($checked[$checkbox_value]), array('id' => $id));
				$input .= Form::label($id, $checkbox_title);
				$input .= "</li>\n";
			}
			$input .= "</ul>\n";

			return Form::wrap($input, $name, $label, $error, $tip);
	}


	/**
	 * Creates CSRF token input.
	 *
	 * @param   string  $id      e.g. uid
	 * @param   string  $action  optional action
	 * @return  string
	 */
	public static function csrf($id = '', $action = '') {
		return Form::hidden('token', Security::csrf($id, $action));
	}


	/**
	 * Get form error
	 *
	 * @param   string  $name
	 * @return  string
	 */
	public function error($name) {
		return Arr::path($this->errors, $name, null);
	}


	/**
	 * Add errors to from
	 *
	 * @param   array  $errors
	 * @return  Form
	 */
	public function errors(array $errors = null) {
		$this->errors = $errors + $this->errors;

		return $this;
	}


	/**
	 * Create new form
	 *
	 * @static
	 * @param   array   $values
	 * @param   array   $errors
	 * @param   object  $model
	 * @return  Form
	 */
	public static function factory(array $values = null, array $errors = null, $model = null) {
		$form = new Form;
		$form->values = $values;
		$form->errors = $errors;
		$form->model  = $model;

		return $form;
	}


	/**
	 * Creates a file upload form input.
	 *
	 * @param   string        input name
	 * @param   array         html attributes
	 *
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @return  string
	 */
	public static function file_wrap($name, array $attributes = null, $label = null, $error = null, $tip = null) {
		$attributes = (array)$attributes + array('id' => self::input_id($name));
		$label      = $label ? array($attributes['id'] => $label) : '';

		$input = Form::file($name, $attributes);

		return Form::wrap($input, $name, $label, $error, $tip);
	}


	/**
	 * Return input element id based on input name
	 *
	 * @static
	 * @param   string  $name
	 * @param   string  $prefix
	 * @return  string
	 */
	public static function input_id($name, $prefix = 'field-') {
		return $prefix . str_replace(array('_', '[', ']'), array('-', '-', ''), $name);
	}


	/**
	 * Creates a form input. Defaults to a text type.
	 *
	 * @param   string        input name
	 * @param   string        input value
	 * @param   array         html attributes
	 *
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @return  string
	 */
	public static function input_wrap($name, $value = null, array $attributes = null, $label = null, $error = null, $tip = null) {
		if (is_array($value)) {
			$value = Arr::get($value, $name);
		} else if (is_object($value)) {
			$value = $value->$name;
		}
		$attributes = (array)$attributes + array('id' => self::input_id($name));
		$label      = $label ? array($attributes['id'] => $label) : '';
		$input      = Form::input($name, $value, $attributes);

		return Form::wrap($input, $name, $label, $error, $tip);
	}


	/**
	 * Set edited object
	 *
	 * @param   object  $model
	 * @return  Form
	 */
	public function model($model = null) {
		$this->model = $model;

		return $this;
	}


	/**
	 * Creates a password form input.
	 *
	 * @param   string        input name
	 * @param   string        input value
	 * @param   array         html attributes
	 *
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @param   string        $show_password
	 * @return  string
	 */
	public static function password_wrap($name, $value = null, array $attributes = null, $label = null, $error = null, $tip = null, $show_password = null) {
		if (is_array($value)) {
			$value = Arr::get($value, $name);
		} else if (is_object($value)) {
			$value = $value->$name;
		}
		$attributes = (array)$attributes + array('id' => self::input_id($name));
		$label      = $label ? array($attributes['id'] => $label) : '';

		// Inject show password element id
		if ($show_password) {
			$attributes['show'] = $name . '_show';
		}

		$input = Form::password($name, $value, $attributes);

		// Add 'Show password' ?
		if ($show_password) {
			$input .= Form::checkbox($name . '_show', 'yes') . Form::label($name . '_show', $show_password);
		}

		return Form::wrap($input, $name, $label, $error, $tip);
	}


	/**
	 * Creates a list of radio form inputs
	 *
	 * @param   string        $name     input name
	 * @param   array         $values   input values
	 * @param   array         $checked  checked status
	 * @param   array         $attributes
	 *
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @param   string        $class
	 * @return  string
	 */
	public static function radios_wrap($name, $values = array(), $checked = '', $attributes = null, $label = null, $error = null, $tip = null, $class = null) {
		$values = Arr::get($values, $name, $values);
		if (is_object($checked)) {
			$checked->$name;
		} else if (is_array($checked)) {
			$checked = Arr::get($checked, $name);
		}

		$input = $class ? '<ul class="' . $class . "\">\n" : "<ul>\n";
		foreach ($values as $radio_value => $radio_title) {
			$id = self::input_id($name) . '-' . $radio_value;
			$input .= '<li' . HTML::attributes($attributes) . '>';
			$input .= Form::radio($name, $radio_value, $radio_value == $checked, array('id' => $id));
			$input .= Form::label($id, $radio_title);
			$input .= "</li>\n";
		}
		$input .= "</ul>\n";

		return Form::wrap($input, $name, $label, $error, $tip);
	}


	/**
	 * Creates a select form input.
	 *
	 * @param   string        input name
	 * @param   array         available options
	 * @param   string        selected option
	 * @param   array         html attributes
	 *
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @return  string
	 */
	public static function select_wrap($name, array $options = null, $selected = null, array $attributes = null, $label = null, $error = null, $tip = null) {
		$selected   = $selected;
		$options    = Arr::get($options, $name, $options);
		$attributes = (array)$attributes + array('id' => self::input_id($name));
		$label      = $label ? array($attributes['id'] => $label) : '';

		$input = Form::select($name, $options, $selected, $attributes);

		return Form::wrap($input, $name, $label, $error, $tip);
	}


	/**
	 * Creates a submit form input with cancel link.
	 *
	 * @param   string  input name
	 * @param   string  input value
	 * @param   array   html attributes
	 *
	 * @param   string  $cancel             cancel url
	 * @param   array   $cancel_attributes  html attributes for cancel
	 * @param   array   $hidden             hidden fields
	 * @return  string
	 */
	public static function submit_wrap($name, $value, array $attributes = null, $cancel = null, array $cancel_attributes = null, array $hidden = null) {
		$wrap = Form::submit($name, $value, $attributes);

		// Cancel link
		if ($cancel) {
			$cancel_attributes['class'] = trim(Arr::get($cancel_attributes, 'class') . ' cancel');
			$wrap .= "\n" . HTML::anchor($cancel, __('Cancel'), $cancel_attributes);
		}

		// Hidden fields
		if ($hidden) {
			foreach ($hidden as $hidden_name => $hidden_value) {
				$wrap .= Form::hidden($hidden_name, $hidden_value);
			}
		}

		return $wrap;
	}


	/**
	 * Creates a textarea form input.
	 *
	 * @param   string        textarea name
	 * @param   string        textarea body
	 * @param   array         html attributes
	 * @param   boolean       encode existing HTML characters
	 *
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @return  string
	 */
	public static function textarea_wrap($name, $body = '', array $attributes = null, $double_encode = true, $label = null, $error = null, $tip = null, $bbcode = null) {
		if (is_array($body)) {
			$body = Arr::get($body, $name);
		} else if (is_object($body)) {
			$body = $body->$name;
		}
		$attributes = (array)$attributes + array('id' => self::input_id($name));
		$label      = $label ? array($attributes['id'] => $label) : '';

		$input = Form::textarea($name, $body, $attributes, $double_encode);
		if ($bbcode) {
			$input .= HTML::script_source('$(function() { $("#' . $attributes['id'] . '").markItUp(bbCodeSettings); });');
		}

		return Form::wrap($input, $name, $label, $error, $tip);
	}


	/**
	 * Get form input value
	 *
	 * @param   string  $name
	 * @return  string
	 */
	public function value($name) {
		return Arr::get($this->values, $name, is_object($this->model) ? $this->model->$name : null);
	}


	/**
	 * Add values to from
	 *
	 * @param   array  $values
	 * @return  Form
	 */
	public function values(array $values = null) {
		$this->values = $values + $this->values;

		return $this;
	}


	/**
	 * Create Anqh styles form input wrapped in list
	 *
	 * @param   string        $input
	 * @param   string|array  $name
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @param   boolean       $label_after
	 * @param   array         $attributes
	 * @return  string
	 */
	public static function wrap($input, $name, $label = null, $error = null, $tip = null, $label_after = false, array $attributes = null) {

		// Find the input error if any
		$error = HTML::error($error, $name);
		if (!empty($error)) {
			$attributes['class'] = trim('error ' . Arr::get($attributes, 'class'));
		}
		$wrap = '<li' . HTML::attributes($attributes) . '>';

		// Input label if any
		if ($label) {
			$label = is_array($label) ? Form::label(key($label), current($label)) : Form::label($name, $label);
		}

		// Input tip if any
		if ($tip) {
			$tip = '<p class="tip">' . (is_array($tip) ? Arr::get($tip, $name) : $tip) . '</p>';
		}

		return $wrap . ($label_after ? $input . $label : $label . $input) . $error . $tip . "</li>\n";
	}

}
