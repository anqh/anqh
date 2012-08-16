<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Admin_Tag
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Admin_Tag extends View_Section {

	/**
	 * @var  array
	 */
	public $errors;

	/**
	 * @var  Model_Tag
	 */
	public $tag;


	/**
	 * Create new view.
	 *
	 * @param  Model_Tag  $tag
	 */
	public function __construct(Model_Tag $tag) {
		parent::__construct();

		$this->tag = $tag;
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
		Form::input('name', $this->tag->name, array('class' => 'input-xxlarge', 'maxlength' => 32)),
		array('name' => __('Name')),
		Arr::get($this->errors, 'name')) ?>

	<?= Form::control_group(
		Form::input('description', $this->tag->description, array('class' => 'input-xxlarge')),
		array('description' => __('Short description')),
		Arr::get($this->errors, 'description')) ?>

</fieldset>

<fieldset class="form-actions">
	<?= Form::button('save', __('Save'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
	<?= HTML::anchor(Request::back(Route::url('tags'), true), __('Cancel'), array('class' => 'cancel')) ?>
</fieldset>

<?php

		echo Form::close();

		return ob_get_clean();
	}

}
