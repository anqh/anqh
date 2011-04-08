<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Enter invitation code
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

echo Form::open();
?>

	<fieldset>
		<legend><?php echo __('Got my invitation!') ?></legend>
		<ul>
			<?php echo Form::input_wrap(
				'code',
				null,
				array('placeholder' => __('M0573XC3LL3N751R')),
				__('Enter your invitation code'),
				$errors,
				__('Your invitation code is included in the mail you received, 16 characters.')) ?>
		</ul>
	</fieldset>

	<fieldset>
		<?php echo Form::submit_wrap('invited', __('Final step!'), null, Request::back('/', true), null, array('signup' => true)) ?>
	</fieldset>

<?php
echo Form::close();
