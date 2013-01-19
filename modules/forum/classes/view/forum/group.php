<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum_Group
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Forum_Group extends View_Section {

	/**
	 * @var  Model_Forum_Group[]
	 */
	public $groups;


	/**
	 * Create new view.
	 *
	 * @param  Model_Forum_Group[]  $groups
	 */
	public function __construct($groups) {
		parent::__construct();

		$this->groups = $groups;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		if (!$this->groups):
			return new View_Alert(__('No groups available..'), __('Oh snap!'), View_Alert::ERROR);
		endif;

		ob_start();

?>

<table class="table">
	<thead>
		<tr>
			<th class="span4"></th>
			<th class="span1"><?= __('Topics') ?></th>
			<th class="span1"><?= __('Posts') ?></th>
			<th class="span2"><?= __('Latest post') ?></th>
		</tr>
	</thead>

	<tbody>

	<?php foreach ($this->groups as $group): ?>

		<tr>
			<th colspan="4"><h3><?= HTML::chars($group->name) ?></h3></th>
		</tr>

<?php

			$areas = $group->areas();

			if (count($areas)):
				foreach ($areas as $area):

?>

		<?php if (Permission::has($area, Model_Forum_Area::PERMISSION_READ, self::$_user)): ?>

		<tr>
			<td>
				<h4><?= HTML::anchor(Route::model($area), HTML::chars($area->name)) ?></h4>
				<?= $area->description ?>
			</td>
			<td><?= Num::format($area->topic_count, 0) ?></td>
			<td><?= Num::format($area->post_count, 0) ?></td>
			<td>

			<?php if ($area->topic_count > 0): $last_topic = $area->last_topic(); ?>

				<small class="ago"><?= HTML::time(Date::short_span($last_topic->last_posted, true, true), $last_topic->last_posted) ?></small>
				<?= HTML::user($last_topic->last_post()->author_id, $last_topic->last_poster) ?><br />
				<?= HTML::anchor(Route::model($last_topic), '<i class="muted iconic-upload"></i>', array('title' => __('First post'))) ?>
				<?= HTML::anchor(Route::model($last_topic, '?page=last#last'), HTML::chars($last_topic->name), array('title' => HTML::chars($last_topic->name))) ?>

			<?php else: ?>

				<sup><?php echo __('No topics yet.') ?></sup>

			<?php endif; ?>

			</td>
		</tr>

		<?php elseif ($area->status != Model_Forum_Area::STATUS_HIDDEN): ?>

		<tr>
			<td colspan="4">
				<h4><?= HTML::chars($area->name) ?></h4>
				<?= __('Members only') ?>
			</td>
		</tr>

		<?php	endif; ?>

<?php

				endforeach; // areas

			else:

?>

		<tr>
			<td colspan="4">

			<?= __('No areas available.') ?>

			<?php if (Permission::has($group, Model_Forum_Group::PERMISSION_UPDATE, self::$_user)): ?>
				<?= HTML::anchor(Route::model($group, 'edit'), '<i class="icon-edit icon-white"></i> ' . __('Edit group'), array('class' => 'btn btn-inverse')) ?>
			<?php endif; ?>

			<?php if (Permission::has($group, Model_Forum_Group::PERMISSION_CREATE_AREA, self::$_user)): ?>
				<?= HTML::anchor(Route::model($group, 'add'), '<i class="icon-plus-sign icon-white"></i> ' . __('New area'), array('class' => 'btn btn-primary')) ?>
			<?php endif; ?>

			</td>
		</tr>

<?php
			endif;

		endforeach; // groups

?>

	</tbody>
</table>

<?php

		return ob_get_clean();
	}

}
