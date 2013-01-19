<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum_AreaEdit
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Forum_AreaEdit extends View_Section {

	/**
	 * @var  Model_Forum_Area
	 */
	public $area;

	/**
	 * @var  array
	 */
	public $errors;


	/**
	 * Create new view.
	 *
	 * @param  Model_Forum_Area  $area
	 */
	public function __construct($area = null) {
		parent::__construct();

		$this->area = $area;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		// Build available groups list
		$groups = array();
		foreach (Model_Forum_Group::factory()->find_all() as $_group) {
			$groups[$_group->id] = $_group->name;
		}

		echo Form::open();

?>

	<fieldset>

		<?= Form::control_group(
			Form::select('forum_group_id', $groups, $this->area->forum_group_id),
			array('forum_group_id' => __('Group')),
			Arr::get($this->errors, 'forum_group_id')) ?>

		<?= Form::control_group(
			Form::input('name', $this->area->name),
			array('name' => __('Name')),
			Arr::get($this->errors, 'name')) ?>

		<?= Form::control_group(
			Form::input('description', $this->area->description),
			array('description' => __('Description')),
			Arr::get($this->errors, 'description')) ?>

		<?= Form::control_group(
			Form::input('sort', $this->area->sort, array('class' => 'input-mini')),
			array('sort' => __('Sort')),
			Arr::get($this->errors, 'sort')) ?>

	</fieldset>

	<fieldset>
		<legend><?php echo __('Settings') ?></legend>

		<?= Form::control_group(
			Form::select('access_read', array(
				Model_Forum_Area::READ_NORMAL  => __('Everybody'),
				Model_Forum_Area::READ_MEMBERS => __('Members only'),
			), $this->area->access_read),
			array('access_read' => __('Read access')),
			Arr::get($this->errors, 'access_read')) ?>

		<?= Form::control_group(
			Form::select('access_write', array(
				Model_Forum_Area::WRITE_NORMAL => __('Members'),
				Model_Forum_Area::WRITE_ADMINS => __('Admins only'),
			), $this->area->access_write),
			array('access_write' => __('Write access')),
			Arr::get($this->errors, 'access_write')) ?>

		<?= Form::control_group(
			Form::select('type', array(
				Model_Forum_Area::TYPE_NORMAL => __('Normal'),
				// Model_Forum_Area::TYPE_PRIVATE => __('Private messages'),
				Model_Forum_Area::TYPE_BIND => __('Bind, topics bound to content'),
			), $this->area->type),
			array('type' => __('Type')),
			Arr::get($this->errors, 'type')) ?>

		<?= Form::control_group(
			Form::select('bind', array('' => __('None')) + Model_Forum_Area::get_binds(), $this->area->bind),
			array('bind' => __('Bind config')),
			Arr::get($this->errors, 'bind')) ?>

		<?= Form::control_group(
			Form::select('status', array(
				Model_Forum_Area::STATUS_NORMAL => __('Normal'),
				Model_Forum_Area::STATUS_HIDDEN => __('Hidden'),
			), $this->area->status),
			array('status' => __('Status')),
			Arr::get($this->errors, 'status')) ?>

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
