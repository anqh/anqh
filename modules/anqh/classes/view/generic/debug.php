<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Generic_Debug
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_Debug extends View_Section {
	public $class = 'kohana';
	public $id    = 'debug';


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		$groups = array(
			'Cache'   => Cache::$queries,
			'Session' => Session::instance()->as_array(),
			'Cookies' => $_COOKIE,
		);

		ob_start();

		foreach ($groups as $group => $content):

?>

<table class="profiler">
	<tr class="group">
		<th class="name" colspan="2"><?= __(ucfirst($group)) ?> (<?= count($content) ?>)</th>
	</tr>
	<tr class="headers">
		<th class="name"><?= __('Key') ?></th>
		<th class="average"><?= __('Value') ?></th>
	</tr>
	<?php foreach ($content as $key => $value): ?>
	<tr class="mark memory">
		<th class="name"><?= $key ?></th>
		<td class="average">
			<div>
				<div class="value"><?php if (strlen($value = print_r($value, true)) > 100): ?>
					<a href="#" onclick="$(this).next('div').toggle(); return false;"><?= __('Show') ?> (<?= strlen($value) ?>)</a>
					<div style="display:none"><?= $value ?></div>
				<?php else: ?>
					<?= $value ?>
				<?php endif ?></div>
			</div>
		</td>
	</tr>
	<?php endforeach ?>
</table>

<?php

		endforeach;

		return ob_get_clean();
	}

}
