<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum_Topics_Private
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Topics_Private extends View_Topics_Index {

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
			<th class="span4"><h3><?= __('Topic') ?></h3></th>
			<th class="span2 muted"><?= __('Last post') ?></th>
		</tr>
	</thead>

	<tbody>

		<?php foreach ($this->topics as $topic):
			$author      = Model_User::find_user_light($topic->author_id);
			$last_poster = $topic->last_post()->author(); ?>

		<tr>

			<td class="media">
				<div class="pull-left">
					<?= HTML::avatar(
						$author ? $author['avatar'] : null,
						$author ? $author['username'] : null
					) ?>
				</div>
				<div class="media-body">
					<?= $author ? HTML::user($author) : HTML::chars($topic->author_name) ?>
					<br>
					<?= HTML::anchor(
								Route::model($topic, '?page=last#last'),
								'<i class="' . (($recipients = $topic->recipient_count) < 3 ? 'icon-envelope' : 'icon-comments') . ' icon-white"></i> ' . HTML::chars($topic->name),
								array(
									'title' => $recipients < 3 ? __('Personal message') : __(':recipients recipients', array(':recipients' => Num::format($recipients, 0)))
								)
							) ?>
					&nbsp;
					<small class="muted" title="<?= __('Replies') ?>">
						<i class="icon-comment"></i> <?= Num::format($topic->post_count - 1, 0) ?>
					</small>
				</div>
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
