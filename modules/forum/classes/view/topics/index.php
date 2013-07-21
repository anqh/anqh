<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Topics Index view.
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Topics_Index extends View_Section {

	/**
	 * @var  boolean  Single area view
	 */
	public $area = false;

	/**
	 * @var  string  View class
	 */
	public $class = 'border';

	/**
	 * @var  Model_Forum_Topic[]
	 */
	public $topics;


	/**
	 * Create new view.
	 *
	 * @param  Model_Forum_Topics[]  $topics
	 * @param  boolean               $area    single area
	 */
	public function __construct($topics = null, $area = false) {
		parent::__construct();

		$this->topics = $topics;
		$this->area   = $area;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		if (count($this->topics)): ?>

<ol class="media-list">

	<?php foreach ($this->topics as $topic):
		$last_poster = $topic->last_post()->author();
		$area        = $this->area ? false : $topic->area(); ?>

	<li class="media">
		<div class="pull-left">
			<?= HTML::avatar(
				$last_poster ? $last_poster['avatar'] : null,
				$last_poster ? $last_poster['username'] : null,
				false
			) ?>
		</div>
		<div class="media-body">
			<?= $last_poster ? HTML::user($last_poster) : HTML::chars($topic->last_poster) ?>

			&nbsp; <small class="muted"><?= HTML::time(Date::short_span($topic->last_posted, true, true), $topic->last_posted) ?></small>

			<?php if ($area): ?>
			<small class="muted"><?= __('in :area', array(
					':area' => HTML::anchor(Route::model($area), HTML::chars($area->name), array('class' => 'hoverable'))
				)) ?></small>
			<?php endif; ?>

			&nbsp; <small class="muted" title="<?= __('Replies') ?>"><i class="icon-comment"></i> <?= Num::format($topic->post_count - 1, 0) ?></small>

			<br>

			<h4 class="media-heading">
			<?= HTML::anchor(Route::model($topic), Forum::topic($topic)) ?>
			<small><?= HTML::anchor(Route::model($topic, '?page=last#last'), '<i class="muted iconic-download"></i>', array('title' => __('Last post'))) ?></small>
			</h4>
		</div>
	</li>

	<?php endforeach ?>

</ol>

<?php

		else:

			// Empty area
			echo new View_Alert(__('Here be nothing yet.'), null, View_Alert::INFO);

		endif;

		return ob_get_clean();
	}

}
