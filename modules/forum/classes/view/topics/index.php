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

<table class="ui table">
	<tbody>

	<?php foreach ($this->topics as $topic):
		$last_poster = $topic->last_post()->author();
		$area        = $this->area ? false : $topic->area();
		if ($topic->recipient_count > 2):
			$icon = '<i class="users icon" title="' . __('Group message') . '"></i> ';
		elseif ($topic->recipient_count > 0):
			$icon = '<i class="mail icon" title="' .  __('Personal message') . '"></i> ';
		else:
			$icon = '';
		endif;

		?>

	<tr>

		<td>
			<h4 class="ui header">
			<?php if ($this->area || $topic->recipient_count): ?>
				<?= $icon . HTML::anchor(Route::model($topic), Forum::topic($topic)) ?>
				<small class="transparent"><?= HTML::anchor(Route::model($topic, '?page=last#last'), '<i class="level down icon"></i>', array('title' => __('Last post'))) ?></small>
			<?php else: ?>
				<?= $icon . HTML::anchor(Route::model($topic, '?page=last#last'), Forum::topic($topic)) ?>
				<small class="transparent"><?= HTML::anchor(Route::model($topic), '<i class="level up icon"></i>', array('title' => __('First post'))) ?></small>
			<?php endif; ?>

				<?php if ($area): ?>
				<p class="sub header">
					<small>
						<?= HTML::anchor(Route::model($area), HTML::chars($area->name), array('class' => 'hoverable')) ?>
					</small>
				</p>
				<?php endif; ?>
			</h4>

			<?php if ($topic->recipient_count > 2): ?>
			&nbsp; <small class="muted" title="<?= __('Recipients') ?>"><i class="users icon"></i> <?= Num::format($topic->recipient_count, 0) ?></small>
			<?php endif; ?>
		</td>

		<td class="right aligned nowrap">
			<small><?= Num::format($topic->post_count - 1, 0) ?> <i class="comment icon"></i></small>
		</td>

		<td class="nowrap">
			<?= HTML::avatar(
				$last_poster ? $last_poster['avatar'] : null,
				$last_poster ? $last_poster['username'] : null
			) ?>
			<?= $last_poster ? HTML::user($last_poster) : HTML::chars($topic->last_poster) ?>
		</td>

		<td class="right aligned">
			<small><?= HTML::time(Date::short_span($topic->last_posted, true, true), $topic->last_posted) ?></small>
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
