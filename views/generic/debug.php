<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Debug information
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

$groups = array(
	'Session' => $_SESSION,
	'Cookie'  => $_COOKIE,
)
?>

<div class="kohana">
	<?php foreach ($groups as $group => $content): ?>
	<table class="profiler">
		<tr class="group">
			<th class="name" colspan="2"><?php echo __(ucfirst($group)) ?> (<?= count($content) ?>)</th>
		</tr>
		<tr class="headers">
			<th class="name"><?php echo __('Key') ?></th>
			<th class="average"><?php echo __('Value') ?></th>
		</tr>
		<?php foreach ($content as $key => $value): ?>
		<tr class="mark memory">
			<th class="name"><?php echo $key ?></th>
			<td class="average">
				<div>
					<div class="value"><?php print_r($value) ?></div>
				</div>
			</td>
		</tr>
		<?php endforeach ?>
	</table>
	<?php endforeach ?>
</div>
