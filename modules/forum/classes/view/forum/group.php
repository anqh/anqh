<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum_Group
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Forum_Group extends View_Section {

	/**
	 * @var  string  Section class
	 */
	public $class = 'forum-groups table';

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

<table class="table table-condensed">

	<?php foreach ($this->groups as $group): ?>

	<thead>
		<tr>
			<th class="span5"><h3><?= HTML::chars($group->name) ?></h3></th>
			<th class="span3 muted"><?= __('Last post') ?></th>
		</tr>
	</thead>

	<tbody>

<?php

			$areas = $group->areas();

			if (count($areas)):
				foreach ($areas as $area):

?>

		<?php if (Permission::has($area, Model_Forum_Area::PERMISSION_READ, self::$_user)): ?>

		<tr>
			<td>
				<h4>
					<?= HTML::anchor(Route::model($area), HTML::chars($area->name)) ?>
					<small class="muted"><?= Num::format($area->topic_count, 0) ?> topics, <?= Num::format($area->post_count, 0) ?> posts</small>
				</h4>
				<sup>
					<?= $area->description ?>
				</sup>
			</td>
			<td>

			<?php if ($area->topic_count > 0): $last_topic = $area->last_topic(); ?>

				<small class="ago"><?= HTML::time(Date::short_span($last_topic->last_posted, true, true), $last_topic->last_posted) ?></small>
				<?= HTML::anchor(Route::model($last_topic), '<i class="muted iconic-upload"></i>', array('title' => __('First post'))) ?>
				<?= HTML::anchor(Route::model($last_topic, '?page=last#last'), Forum::topic($last_topic), array('title' => HTML::chars($last_topic->name))) ?><br />
				<?= HTML::user($last_topic->last_post()->author_id, $last_topic->last_poster) ?>

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
			<td colspan="3">

			<?= __('No areas available.') ?>

			<?php if (Permission::has($group, Model_Forum_Group::PERMISSION_UPDATE, self::$_user)): ?>
				<?= HTML::anchor(Route::model($group, 'edit'), '<i class="icon-edit icon-white"></i> ' . __('Edit group'), array('class' => 'btn btn-inverse')) ?>
			<?php endif; ?>

			<?php if (Permission::has($group, Model_Forum_Group::PERMISSION_CREATE_AREA, self::$_user)): ?>
				<?= HTML::anchor(Route::model($group, 'add'), '<i class="icon-plus-sign icon-white"></i> ' . __('New area'), array('class' => 'btn btn-primary')) ?>
			<?php endif; ?>

			</td>
		</tr>

		<?php	endif; ?>

	</tbody>

	<?php endforeach; // groups ?>

</table>

<?php

		return ob_get_clean();
	}

}
