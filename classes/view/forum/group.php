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
	 * @var  string  View class
	 */
	public $class = 'forum-group';

	/**
	 * @var  Model_Forum_Group
	 */
	public $group;


	/**
	 * Create new view.
	 *
	 * @param  Model_Forum_Group  $group
	 */
	public function __construct($group) {
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

		$areas = $this->group->areas();

		if (count($areas)):
?>

<table class="table">
	<thead>
		<tr>
			<th class="span4"><h3><?= HTML::anchor(Route::model($this->group), HTML::chars($this->group->name)) ?></h3></th>
			<th class="span1"><?= __('Topics') ?></th>
			<th class="span1"><?= __('Posts') ?></th>
			<th class="span2"><?= __('Latest post') ?></th>
		</tr>
	</thead>

	<tbody>

	<?php foreach ($areas as $area): ?>

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

	<?php endforeach; ?>

	</tbody>
</table>

<?php

		else:

			echo new View_Alert(__('No areas yet.'), null, View_Alert::INFO);

		endif;

		return ob_get_clean();
	}

}
