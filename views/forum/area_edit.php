<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Edit area
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open();
?>

	<fieldset>
		<ul>
			<?php echo Form::select_wrap('forum_group_id', $groups, $area, null, __('Group'), $errors) ?>
			<?php echo Form::input_wrap('name', $area, null, __('Name'), $errors) ?>
			<?php echo Form::input_wrap('description', $area, null, __('Description'), $errors) ?>
			<?php echo Form::input_wrap('sort', $area, null, __('Sort'), $errors) ?>
		</ul>
	</fieldset>

	<fieldset>
		<legend><?php echo __('Settings') ?></legend>
		<ul>
			<?php echo Form::select_wrap('access_read', array(
				Model_Forum_Area::READ_NORMAL  => __('Everybody'),
				Model_Forum_Area::READ_MEMBERS => __('Members only'),
			), $area, null, __('Read access')) ?>
			<?php echo Form::select_wrap('access_write', array(
				Model_Forum_Area::WRITE_NORMAL => __('Members'),
				Model_Forum_Area::WRITE_ADMINS => __('Admins only'),
			), $area, null, __('Write access')) ?>
			<?php echo Form::select_wrap('type', array(
				Model_Forum_Area::TYPE_NORMAL => __('Normal'),
				// Model_Forum_Area::TYPE_PRIVATE => __('Private messages'),
				Model_Forum_Area::TYPE_BIND => __('Bind, topics bound to content'),
			), $area, null, __('Type')) ?>
			<?php echo Form::select_wrap('bind', array('' => __('None')) + Model_Forum_Area::get_binds(), $area, null, __('Bind config')) ?>
			<?php echo Form::select_wrap('status', array(
				Model_Forum_Area::STATUS_NORMAL => __('Normal'),
				Model_Forum_Area::STATUS_HIDDEN => __('Hidden'),
			), $area, null, __('Status')) ?>
		</ul>
	</fieldset>

	<fieldset>
		<?php echo Form::submit_wrap('save', __('Save'), null, Request::back(Route::get('forum_group')->uri(), true)) ?>
	</fieldset>

<?php
echo Form::close();
