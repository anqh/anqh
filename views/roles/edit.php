<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Role edit
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?= form::open() ?>

	<fieldset>
		<ul>

			<?= Form::input_wrap('name', $values, array('maxlength' => 32), __('Name'), $errors) ?>

			<?= Form::input_wrap('description', $values, null, __('Description'), $errors) ?>

		</ul>
	</fieldset>

	<fieldset>
		<?= Form::submit(false, __('Save')) ?>
		<?= HTML::anchor(Request::back('roles', true), __('Cancel')) ?>
	</fieldset>

<?= form::close() ?>
