<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum_GroupEdit
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Forum_GroupEdit extends View_Section {

	/**
	 * @var  array
	 */
	public $errors;

	/**
	 * @var  Model_Forum_Group
	 */
	public $group;


	/**
	 * Create new view.
	 *
	 * @param  Model_Forum_Group  $group
	 */
	public function __construct($group) {
		parent::__construct();

		$this->group = $group;
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
			Form::input('name', $this->group->name),
			array('name' => __('Name')),
			Arr::get($this->errors, 'name')) ?>

		<?= Form::control_group(
			Form::input('description', $this->group->description),
			array('description' => __('Description')),
			Arr::get($this->errors, 'description')) ?>

		<?= Form::control_group(
			Form::input('sort', $this->group->sort, array('class' => 'input-mini')),
			array('sort' => __('Sort')),
			Arr::get($this->errors, 'sort')) ?>

	</fieldset>

	<fieldset class="form-actions">
		<?= Form::button('save', __('Save'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
		<?= HTML::anchor(Request::back(Route::url('forum_group'), true), __('Cancel'), array('class' => 'cancel')) ?>

		<?= Form::csrf() ?>
	</fieldset>

<?php

		echo Form::close();

		return ob_get_clean();
	}

}
