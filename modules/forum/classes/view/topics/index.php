<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Topics Index view.
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
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
	public $class = 'table';

	/**
	 * @var  Model_Forum_Topic[]
	 */
	public $topics;


	/**
	 * Create new view.
	 *
	 * @param  Model_Forum_Topic[]  $topics
	 * @param  boolean              $area    single area
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

<table class="table table-condensed table-striped">
	<tbody>

	<?php foreach ($this->topics as $topic):
		$last_poster = $topic->last_post()->author();
		$area        = $this->area ? false : $topic->area();

	?>

	<tr>

		<td>
			<h4 class="media-heading">
			<?php if ($this->area || $topic->recipient_count): ?>
				<?= HTML::anchor(Route::model($topic), Forum::topic($topic)) ?>
				<small class="transparent"><?= HTML::anchor(Route::model($topic, '?page=last#last'), '<i class="text-muted fa fa-level-down"></i>', array('title' => __('Last post'))) ?></small>
			<?php else: ?>
				<?= HTML::anchor(Route::model($topic, '?page=last#last'), Forum::topic($topic)) ?>
				<small class="transparent"><?= HTML::anchor(Route::model($topic), '<i class="text-muted fa fa-level-up"></i>', array('title' => __('First post'))) ?></small>
			<?php endif; ?>
			</h4>

			<?php if ($area): ?>
			<small class="muted">
				<?= HTML::anchor(Route::model($area), HTML::chars($area->name), array('class' => 'hoverable')) ?>
			</small>
			<?php endif; ?>

		</td>

		<td class="text-right muted nowrap">
			<small title="<?= __('Replies') ?>"><?= Num::format($topic->post_count - 1, 0) ?> <i class="fa fa-comment"></i></small>
		</td>

		<td>
			<?= HTML::avatar(
				$last_poster ? $last_poster['avatar'] : null,
				$last_poster ? $last_poster['username'] : null,
				'small'
			) ?>
			<?= $last_poster ? HTML::user($last_poster) : HTML::chars($topic->last_poster) ?>
		</td>

		<td>
			<small class="muted pull-right nowrap"><?= HTML::time(Date::short_span($topic->last_posted, true, true), $topic->last_posted) ?></small>
		</td>

	</tr>

	<?php endforeach ?>

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
