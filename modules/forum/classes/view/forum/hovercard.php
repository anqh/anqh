<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum_HoverCard
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Forum_HoverCard extends View_Section {

	/**
	 * @var  Model_Forum_Area
	 */
	public $area;


	/**
	 * Create new view.
	 *
	 * @param  Model_Forum_Area  $area
	 */
	public function __construct(Model_Forum_Area $area) {
		parent::__construct();

		$this->area  = $area;
		$this->title = HTML::chars($area->name);
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		// Title
		if ($this->area->description):
			echo $this->area->description . '<hr>';
		endif;

		if ($this->area->topic_count):

			// Area has topics
			$last_topic  = $this->area->last_topic();
			$last_poster = $last_topic->last_post()->author();

?>

		<div class="media">
			<div class="pull-left">
				<?= HTML::avatar(
					$last_poster ? $last_poster['avatar'] : null,
					$last_poster ? $last_poster['username'] : null,
					false
				) ?>
			</div>
			<div class="media-body">
				<small class="ago"><?= HTML::time(Date::short_span($last_topic->last_posted, true, true), $last_topic->last_posted) ?></small>
				<?= $last_poster ? HTML::user($last_poster) : HTML::chars($last_topic->last_poster) ?>
				<br>
				<?= HTML::anchor(Route::model($last_topic), '<i class="muted iconic-upload"></i>', array('title' => __('First post'))) ?>
				<?= HTML::anchor(Route::model($last_topic, '?page=last#last'), Forum::topic($last_topic), array('title' => HTML::chars($last_topic->name))) ?><br />
			</div>
		</div>

		<small class="stats muted">
			<i class="icon-comments"></i> <?= Num::format($this->area->topic_count, 0) ?>
			<i class="icon-comment"></i> <?= Num::format($this->area->post_count, 0) ?>
		</small>

<?php


		else:

			// Empty area
			echo __('No topics yet.');

		endif;

		return ob_get_clean();
	}

}
