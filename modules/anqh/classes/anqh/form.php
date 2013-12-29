<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Form helper.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Form extends Kohana_Form {

	/**
	 * @var  array  Form errors
	 */
	public $errors = null;

	/**
	 * @var  object  Model being edited
	 */
	public $model = null;

	/**
	 * @var  array  Form values
	 */
	public $values = null;


	/**
	 * Creates a button form input.
	 *
	 * @param   string        $name        input name
	 * @param   string        $body        input value
	 * @param   array         $attributes  html attributes
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
	 * @param   string         $name        input name
	 * @param   string         $value       input value
	 * @param   boolean|array  $checked     checked status
	 * @param   array          $attributes  html attributes

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

		$input = Form::checkbox($name, $value, $checked, $attributes);
		$label = is_array($label)
			? Form::label(null, $input . current($label), array('class' => 'checkbox'))
			: Form::label(null, $input . $label, array('class' => 'checkbox'));

		return Form::wrap(null, $name, $label, $error, $tip);
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
		$input = ($class ? '<ul class="unstyled ' . $class . '">' : '<ul class="unstyled">') . "\n";
		foreach ($values as $checkbox_value => $checkbox_title) {
			$id = self::input_id($name) . '-' . $checkbox_value;
			$input .= '<li>';
			$input .= Form::checkbox_wrap($name . '[]', $checkbox_value, isset($checked[$checkbox_value]), array('id' => $id), $checkbox_title);
			$input .= "</li>\n";
		}
		$input .= "</ul>\n";

		return Form::wrap($input, $name, $label, $error, $tip);
	}


	/**
	 * Create Twitter bootstrap styled control group.
	 *
	 * @param   string        $input  Input string to be wrapped
	 * @param   string        $label
	 * @param   string|array  $label  'Label' or 'input-id' => 'Label'
	 * @param   string|array  $error  'Fail' or 'error' => 'Fail', 'success' => 'Yay'
	 * @param   string        $help
	 * @param   array         $attr   Extra attributes
	 * @return  string
	 */
	public static function control_group($input, $label = null, $error = null, $help = null, $attr = array()) {

		// Wrapper basic class
		$class = 'control-group ';

		// Extra classes
		if ($error) {
			if (is_array($error)) {
				$class .= implode(' ', array_keys($error));
				$error  = implode('<br />', $error);
			} else {
				$class .= 'error';
			}
		}

		// Label
		if ($label) {
			$label = Form::label(
				is_array($label) ? key($label) : null,
				is_array($label) ? current($label) : $label,
				array('class' => 'control-label')
			);
		}

		// Error / help messages
		if ($error || $help) {
			$help = '<p class="help-block">' . $error . ($error && $help ? '<br />' : '') . $help . '</p>';
		}

		$attr['class'] = trim($class . ' ' . $attr['class']);

		return '<div' . HTML::attributes($attr) . '>' . $label . '<div class="controls">' . $input . $help . '</div></div>';
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
	 * @param   string        $name        input name
	 * @param   array         $attributes  html attributes
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
	 * @param   string        $name        input name
	 * @param   string        $value       input value
	 * @param   array         $attributes  html attributes
	 *
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @param   string        $append
	 * @return  string
	 */
	public static function input_wrap($name, $value = null, array $attributes = null, $label = null, $error = null, $tip = null, $append = null) {
		if (is_array($value)) {
			$value = Arr::get($value, $name);
		} else if (is_object($value)) {
			$value = $value->$name;
		}
		$attributes = (array)$attributes + array('id' => self::input_id($name));
		$label      = $label ? array($attributes['id'] => $label) : '';
		$input      = Form::input($name, $value, $attributes);
		if ($append) {
			$input = '<div class="input-append">' . $input . '<span class="add-on">' . $append . '</span></div>';
		}

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
	 * @param   string        $name        input name
	 * @param   string        $value       input value
	 * @param   array         $attributes  html attributes
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
	 * @param   string        $checked  checked status
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

		$input = '';
		foreach ($values as $radio_value => $radio_title) {
			$id = self::input_id($name) . '-' . $radio_value;
			$radio  = Form::radio($name, $radio_value, $checked === $radio_value, array('id' => $id));
			$input .= Form::label(null, $radio . $radio_title, array('class' => 'radio ' . $class));
		}

		return Form::wrap($label ? '<div class="controls">' . $input . '</div>' : $input, $name, $label, $error, $tip);
	}


	/**
	 * Creates a select form input.
	 *
	 * @param   string        $name        input name
	 * @param   array         $options     available options
	 * @param   string        $selected    selected option
	 * @param   array         $attributes  html attributes
	 *
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @return  string
	 */
	public static function select_wrap($name, array $options = null, $selected = null, array $attributes = null, $label = null, $error = null, $tip = null) {
		if (is_array($selected)) {
			$selected = Arr::get($selected, $name);
		} else if (is_object($selected)) {
			$selected = $selected->$name;
		}
		$options    = Arr::get($options, $name, $options);
		$attributes = (array)$attributes + array('id' => self::input_id($name));
		$label      = $label ? array($attributes['id'] => $label) : '';

		$input = Form::select($name, $options, $selected, $attributes);

		return Form::wrap($input, $name, $label, $error, $tip);
	}


	/**
	 * Creates a submit form input with cancel link.
	 *
	 * @param   string  $name               input name
	 * @param   string  $value              input value
	 * @param   array   $attributes         html attributes
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
	 * Creates a textarea form input with BBCode editor.
	 *
	 * @param   string   $name           textarea name
	 * @param   string   $body           textarea body
	 * @param   array    $attributes     html attributes
	 * @param   boolean  $double_encode  encode existing HTML characters
	 * @return  string
	 *
	 * @uses    HTML::attributes
	 * @uses    HTML::chars
	 */
	public static function textarea_editor($name, $body = '', array $attributes = null, $double_encode = true) {

		// Get DOM element
		if ($element = Arr::get($attributes, 'id')) {
			$element = '#' . $element;
		} else {
			$element = 'textarea[name=' . $name . ']';
		}

		return
			new View_Generic_Smileys($element)
				. Form::textarea($name, $body, $attributes, $double_encode)
				. HTML::script_source('head.ready("bbcode", function initMarkItUp() { $("' . $element . '").markItUp(bbCodeSettings); });');
	}


	/**
	 * Creates a textarea form input.
	 *
	 * @param   string        $name           textarea name
	 * @param   string        $body           textarea body
	 * @param   array         $attributes     html attributes
	 * @param   boolean       $double_encode  encode existing HTML characters
	 *
	 * @param   string        $label
	 * @param   string|array  $error
	 * @param   string|array  $tip
	 * @param   boolean       $bbcode
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
			$input .= HTML::script_source('
head.ready("bbcode", function initMarkItUp() {
	$("#' . $attributes['id'] . '").markItUp(bbCodeSettings);
});
');
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

		$attributes['class'] .= ' control-group';

		// Label
		if ($label) {
			$label = is_array($label)
				? Form::label(key($label), current($label), array('class' => 'control-label'))
				: Form::label($name, $label, array('class' => 'control-label'));
		}

		// Tip
		if ($tip) {
			$tip = '<p class="help-block">' . (is_array($tip) ? Arr::get($tip, $name) : $tip) . '</p>';
		}

		return '<div' . HTML::attributes($attributes) . '>' . ($label_after ? $input . $label : $label . $input) . $error . $tip . "</div>\n";
	}

}
