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
	public static function button_wrap($name, $body, array $attributes = null, $label = '', $error = '', $tip = '') {
		$body = is_array($body) ? Arr::get($body, $name) : $body;

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
	public static function checkbox_wrap($name, $value = null, $checked = false, array $attributes = null, $label = '', $error = '', $tip = '') {
		$value = is_array($value) ? Arr::get($value, $name) : $value;
		$checked = is_array($checked) ? Arr::get($checked, $name) == $value : $checked;

		// Add id to input if label given
		if ($label && !isset($attributes['id'])) {
			$attributes['id'] = $name;
		}

		$input = Form::checkbox($name, $value, $checked, $attributes);

		return Form::wrap($input, $name, $label, $error, $tip, true);
	}


	/**
	 * Creates checkboxes list
	 *
	 * @param   string        $name     input name
	 * @param   array         $values   input values
	 * @param   array         $checked  checked statuses
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @param   string        $class
	 * @return  string
	 */
	public static function checkboxes_wrap($name, $values = array(), $checked = array(), $label = '', $error = '', $tip = '', $class = null) {

		// Get checkboxes
		$checkboxes = Arr::get($values, $name, $values);
		if (!empty($checkboxes)) {

			// Create internal id
			$singular = Inflector::singular($name) . '_';

			// Get values
			$checked = Arr::get($checked, $name, $checked);
			$input = $class ? "<ul>\n" : '<ul class="' . $class . "\">\n";
			foreach ($checkboxes as $checkbox_id => $checkbox_name) {
				$internal_id = $singular . $checkbox_id;
				$input .= '<li>';
				$input .= Form::checkbox($name . '[' . $checkbox_id . ']', 1, isset($checked[$checkbox_id]), array('id' => $internal_id));
				$input .= Form::label($internal_id, $checkbox_name);
				$input .= "</li>\n";
			}
			$input .= "</ul>\n";

			return Form::wrap($input, $name, $label, $error, $tip);
		}
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
	public static function file_wrap($name, array $attributes = null, $label = '', $error = '', $tip = '') {

		// Add id to input if label given
		if ($label && !isset($attributes['id'])) {
			$attributes['id'] = $name;
		}

		$input = Form::file($name, $attributes);

		return Form::wrap($input, $name, $label, $error, $tip);
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
	public static function input_wrap($name, $value = null, array $attributes = null, $label = '', $error = '', $tip = '') {
		$value = is_array($value) ? Arr::get($value, $name) : $value;

		// Add id to input if label given
		if ($label && !isset($attributes['id'])) {
			$attributes['id'] = $name;
		}

		$input = Form::input($name, $value, $attributes);

		return Form::wrap($input, $name, $label, $error, $tip);
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
	public static function password_wrap($name, $value = null, array $attributes = null, $label = '', $error = '', $tip = '', $show_password = '') {
		$value = is_array($value) ? Arr::get($value, $name) : $value;

		// Inject show password element id
		if ($show_password) {
			$attributes['show'] = $name . '_show';
		}

		// Add id to input if label given
		if ($label && !isset($attributes['id'])) {
			$attributes['id'] = $name;
		}

		$input = Form::password($name, $value, $attributes);

		// Add 'Show password' ?
		if ($show_password) {
			$input .= Form::checkbox($name . '_show', 'yes') . Form::label($name . '_show', $show_password);
		}

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
	public static function select_wrap($name, array $options = null, $selected = null, array $attributes = null, $label = '', $error = '', $tip = '') {
		$selected = Arr::get($selected, $name, $selected);
		$options = Arr::get($options, $name, $options);

		$input = Form::select($name, $options, $selected, $attributes);

		return Form::wrap($input, $name, $label, $error, $tip);
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
	public static function textarea_wrap($name, $body = '', array $attributes = null, $double_encode = true, $label = '', $error = '', $tip = '') {
		$body = is_array($body) ? Arr::get($body, $name) : $body;

		// Add id to input if label given
		if ($label && !isset($attributes['id'])) {
			$attributes['id'] = $name;
		}

		$input = Form::textarea($name, $body, $attributes, $double_encode);

		return Form::wrap($input, $name, $label, $error, $tip);
	}


	/**
	 * Create Anqh styles form input wrapped in list
	 *
	 * @param   string        $input
	 * @param   string|array  $name
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @param   bool          $label_after
	 * @return  string
	 */
	public static function wrap($input, $name, $label = '', $error = '', $tip = '', $label_after = false) {
		$wrap = '';

		// Find the input error if any
		$error = HTML::error($error, $name);
		$wrap = empty($error) ? '<li>' : '<li class="error">' . $error;

		// Input label if any
		if (!empty($label)) {
			$wrap .= Form::label($name, $label);
		}

		// Input tip if any
		if (!empty($tip)) {
			$tip = '<p class="tip">' . (is_array($tip) ? Arr::get($tip, $name) : $tip) . '</p>';
		}

		return ($label_after ? $input . $wrap : $wrap . $input) . $tip . "</li>\n";
	}

}
