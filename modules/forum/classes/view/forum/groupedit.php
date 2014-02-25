<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum Edit Group.
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
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

		<?= Form::input_wrap(
			'name',
			$this->group->name,
			null,
			__('Name'),
			Arr::get($this->errors, 'name')
		) ?>

		<?= Form::input_wrap(
			'description',
			$this->group->description,
			null,
			__('Description'),
			Arr::get($this->errors, 'description')
		) ?>

		<?= Form::input_wrap(
			'sort',
			$this->group->sort,
			null,
			__('Sort'),
			Arr::get($this->errors, 'sort')
		) ?>

	</fieldset>

	<fieldset>
		<?= Form::button('save', __('Save'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
		<?= HTML::anchor(Request::back(Route::url('forum'), true), __('Cancel'), array('class' => 'cancel')) ?>

		<?= Form::csrf() ?>
	</fieldset>

<?php

		echo Form::close();

		return ob_get_clean();
	}

}
