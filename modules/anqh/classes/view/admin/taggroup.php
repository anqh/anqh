<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Admin_TagGroup
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Admin_TagGroup extends View_Section {

	/**
	 * @var  Model_Tag_Group
	 */
	public $group;


	/**
	 * Create new view.
	 *
	 * @param  Model_Tag_Group  $group
	 */
	public function __construct(Model_Tag_Group $group = null) {
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

		$tags = $this->group->tags();

		if (empty($tags)):

?>

<div class="empty">
	<?= __('No tags yet.') ?>
</div>

<?php else: ?>

<ul>
	<?php foreach ($tags as $tag): ?>
	<li><?= HTML::anchor(Route::model($tag), $tag->name) ?></li>
	<?php endforeach; ?>
</ul>

<?php

		endif;


		echo Form::open();

?>

<fieldset>

	<?= Form::control_group(
		Form::input('name', $this->group->name, array('class' => 'input-xxlarge', 'maxlength' => 32)),
		array('name' => __('Name')),
		Arr::get($this->errors, 'name')) ?>

	<?= Form::control_group(
		Form::input('description', $this->group->description, array('class' => 'input-xxlarge')),
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
