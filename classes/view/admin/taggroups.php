<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * TagGroups
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Admin_TagGroups extends View_Section {

	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		$groups = Model_Tag_Group::factory()->find_all();
		if (empty($groups)):

?>

<div class="empty">
	<?= __('No groups yet.') ?>
</div>

<?php else: ?>

<ul class="unstyled">

	<?php foreach ($groups as $group): ?>
	<li>
		<h3><?= HTML::anchor(Route::model($group), $group->name) ?></h3>
		<sup><?= $group->description ?></sup><br />
		<?php foreach ($group->tags() as $tag) echo HTML::anchor(Route::model($tag), $tag->name) . ' ' ?>
	</li>
	<?php endforeach; ?>

</ul>

<?php

		endif;

		return ob_get_clean();
	}

}
