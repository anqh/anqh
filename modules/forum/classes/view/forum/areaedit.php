<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum Edit Area.
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
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

		<?= Form::select_wrap(
			'forum_group_id',
			$groups,
			$this->area->forum_group_id,
			null,
			__('Group'),
			Arr::get($this->errors, 'forum_group_id')
		) ?>

		<?= Form::input_wrap(
			'name',
			$this->area->name,
			null,
			__('Name'),
			Arr::get($this->errors, 'name')
		) ?>

		<?= Form::input_wrap(
			'description',
			$this->area->description,
			null,
			__('Description'),
			Arr::get($this->errors, 'description')
		) ?>

		<?= Form::input_wrap(
			'sort',
			$this->area->sort,
			null,
			__('Sort'),
			Arr::get($this->errors, 'sort')
		) ?>

	</fieldset>

	<fieldset class="row">
		<legend><?= __('Settings') ?></legend>

		<div class="col-sm-2">
			<?= Form::select_wrap(
				'status',
				array(
					Model_Forum_Area::STATUS_NORMAL => __('Normal'),
					Model_Forum_Area::STATUS_HIDDEN => __('Hidden'),
				),
				$this->area->status,
				null,
				__('Status'),
				Arr::get($this->errors, 'status')
			) ?>
		</div>

		<div class="col-sm-2">
			<?= Form::select_wrap(
				'access_read', array(
					Model_Forum_Area::READ_NORMAL  => __('Everybody'),
					Model_Forum_Area::READ_MEMBERS => __('Members only'),
				),
				$this->area->access_read,
				null,
				__('Read access'),
				Arr::get($this->errors, 'access_read')
			) ?>
		</div>

		<div class="col-sm-2">
			<?= Form::select_wrap(
				'access_write',
				array(
					Model_Forum_Area::WRITE_NORMAL => __('Members'),
					Model_Forum_Area::WRITE_ADMINS => __('Admins only'),
				),
				$this->area->access_write,
				null,
				__('Write access'),
				Arr::get($this->errors, 'access_write')
			) ?>
		</div>

		<div class="col-sm-3">
			<?= Form::select_wrap(
				'type',
				array(
					Model_Forum_Area::TYPE_NORMAL => __('Normal'),
					// Model_Forum_Area::TYPE_PRIVATE => __('Private messages'),
					Model_Forum_Area::TYPE_BIND => __('Bind, topics bound to content'),
				),
				$this->area->type,
				null,
				__('Type'),
				Arr::get($this->errors, 'type')
			) ?>
		</div>

		<div class="col-sm-3">
			<?= Form::select_wrap(
				'bind',
				array('' => __('None')) + Model_Forum_Area::get_binds(),
				$this->area->bind,
				null,
				__('Bind config'),
				Arr::get($this->errors, 'bind')
			) ?>
		</div>

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
