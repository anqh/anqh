<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Topics_Index
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Topics_Index extends View_Section {

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

		if (count($this->topics)):

?>

<table class="table">
	<thead>
		<tr>
			<th class="span4 topic"><?= __('Topic') ?></th>
			<th class="span1 replies"><?= __('Replies') ?></th>
			<th class="span2 latest"><?= __('Latest post') ?></th>
		</tr>
	</thead>

	<tbody>

		<?php foreach ($this->topics as $topic): ?>

		<tr>
			<td class="topic">
				<?= HTML::anchor(Route::model($topic), Forum::topic($topic)) ?>
				<?= HTML::anchor(Route::model($topic, '?page=last#last'), '<i class="muted iconic-download"></i>', array('title' => __('Last post'))) ?>
			</td>
			<td class="replies">
				<?= Num::format($topic->post_count - 1, 0) ?>
			</td>
			<td class="latest">
				<small class="ago"><?php echo HTML::time(Date::short_span($topic->last_posted, true, true), $topic->last_posted) ?></small>
				<?php echo HTML::user($topic->last_poster, $topic->last_poster) ?>
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
