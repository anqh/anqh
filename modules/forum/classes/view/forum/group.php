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
		$this->title  = __('Forum areas');
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

<ol class="unstyled">
	<?php foreach ($this->groups as $group): ?>

	<li>
		<h4><?= HTML::chars($group->name) ?></h4>
		<ol class="unstyled">

<?php

		$areas = $group->areas();
		if (count($areas)):
			foreach ($areas as $area):

?>

			<li>

<?php
				if (Permission::has($area, Model_Forum_Area::PERMISSION_READ, self::$_user)):

					// Can read area
					if ($area->topic_count > 0):
						$last_topic = $area->last_topic();
						if ($last_topic->last_posted):
							echo '<small class="pull-right muted">' . HTML::time(Date::short_span($last_topic->last_posted, true, true), $last_topic->last_posted) . '</small>';
						endif;
					endif;

					echo HTML::anchor(Route::model($area), HTML::chars($area->name), array('class' => 'hoverable'));

				elseif ($area->status != Model_Forum_Area::STATUS_HIDDEN):

					// Can't read area
					echo HTML::chars($area->name);

				endif;

?>

			</li>

<?php

			endforeach;

		else:

?>

			<li>
				<?= __('No areas available.') ?><br>

				<?php if (Permission::has($group, Model_Forum_Group::PERMISSION_UPDATE, self::$_user)): ?>
					<?= HTML::anchor(Route::model($group, 'edit'), '<i class="icon-edit"></i> ' . __('Edit group'), array('class' => 'btn btn-inverse')) ?>
				<?php endif; ?>

				<?php if (Permission::has($group, Model_Forum_Group::PERMISSION_CREATE_AREA, self::$_user)): ?>
					<?= HTML::anchor(Route::model($group, 'add'), '<i class="icon-plus-sign"></i> ' . __('New area'), array('class' => 'btn btn-primary')) ?>
				<?php endif; ?>

			</li>

<?php

		endif;

?>

		</ol>
	</li>

	<?php endforeach; ?>
	</ol>

<?php

		return ob_get_clean();
	}

}
