<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Send invite
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

if (isset($message)) echo $message;

echo Form::open();
?>

	<fieldset>
		<legend><?php echo __('Not yet invited?') ?></legend>
		<ul>
			<?php echo Form::input_wrap(
				'email',
				$invitation,
				array('placeholder' => __('john.doe@domain.tld')),
				__('Send an invitation to'),
				$errors,
				__('Please remember: sign up is available only with a valid, invited email.')) ?>
		</ul>
	</fieldset>

	<fieldset>
		<?php echo Form::submit_wrap('invite', __('Send invitation'), null, Request::back('/', true)) ?>
	</fieldset>

<?php
echo Form::close();
