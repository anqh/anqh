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
	 * @var  string  View class
	 */
	public $class = 'forum-topics table';

	/**
	 * @var  Model_Forum_Topic[]
	 */
	public $topics;


	/**
	 * Create new view.
	 *
	 * @param  Model_Forum_Topics[]  $topics
	 */
	public function __construct($topics = null) {
		parent::__construct();

		$this->topics = $topics;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		if (count($this->topics)): ?>

<table class="table table-condensed table-striped">
	<thead>
		<tr>
			<th class="span4"><h3><?= __('Topic') ?></h3></th>
			<th class="span2 muted"><?= __('Last post') ?></th>
		</tr>
	</thead>

	<tbody>

		<?php foreach ($this->topics as $topic):
			$last_poster = $topic->last_post()->author(); ?>

		<tr>
			<td>
				<?= HTML::anchor(Route::model($topic), Forum::topic($topic)) ?>
				<?= HTML::anchor(Route::model($topic, '?page=last#last'), '<i class="muted iconic-download"></i>', array('title' => __('Last post'))) ?>
				&nbsp; <small class="muted" title="<?= __('Replies') ?>"><i class="icon-comment"></i> <?= Num::format($topic->post_count - 1, 0) ?></small>
			</td>
			<td class="media">
				<div class="pull-left">
					<?= HTML::avatar(
						$last_poster ? $last_poster['avatar'] : null,
						$last_poster ? $last_poster['username'] : null,
						true
					) ?>
				</div>
				<div class="media-body">
					<small class="ago"><?= HTML::time(Date::short_span($topic->last_posted, true, true), $topic->last_posted) ?></small>
					<?= $last_poster ? HTML::user($last_poster) : HTML::chars($topic->last_poster) ?>
				</div>
			</td>
		</tr>

		<?php endforeach; ?>

	</tbody>
</table>

<?php

		else:

			// Empty area
			echo new View_Alert(__('Here be nothing yet.'), null, View_Alert::INFO);

		endif;

		return ob_get_clean();
	}

}
