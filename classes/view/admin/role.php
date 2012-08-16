<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Admin_Role
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Admin_Role extends View_Section {

	/**
	 * @var  array
	 */
	public $errors;

	/**
	 * @var  Model_Role
	 */
	public $_role;


	/**
	 * Create new view.
	 *
	 * @param  Model_Role  $role
	 */
	public function __construct(Model_Role $role = null) {
		parent::__construct();

		$this->_role = $role;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo Form::open();

?>

<fieldset>

	<?= Form::control_group(
		Form::input('name', $this->_role->name, array('class' => 'input-xxlarge', 'maxlength' => 32)),
		array('name' => __('Name')),
		Arr::get($this->errors, 'name')) ?>

	<?= Form::control_group(
		Form::input('description', $this->_role->description, array('class' => 'input-xxlarge')),
		array('description' => __('Short description')),
		Arr::get($this->errors, 'description')) ?>

</fieldset>

<fieldset class="form-actions">
	<?= Form::button('save', __('Save'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
	<?= HTML::anchor(Request::back(Route::url('roles'), true), __('Cancel'), array('class' => 'cancel')) ?>
</fieldset>

<?php

		echo Form::close();

		return ob_get_clean();
	}

}
