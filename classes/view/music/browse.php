<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Music_Browse view.
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Music_Browse extends View_Section {

	/**
	 * @var  Model_Music_Track[]
	 */
	public $tracks;


	/**
	 * Create new article.
	 *
	 * @param  Model_Music_Track[]  $tracks
	 */
	public function __construct($tracks) {
		parent::__construct();

		$this->tracks = $tracks;
	}


	/**
	 * Section content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<table class="table music">
	<thead>
		<tr>
			<th></th>
			<th><?= __('Name') ?></th>
			<th><?= __('Plays') ?></th>
			<th><?= __('Added') ?></th>
		</tr>
	</thead>

	<tbody>

	<?php foreach ($this->tracks as $track): ?>
		<tr>
			<td class="cover">
				<?php if (Valid::url($track->cover)) echo HTML::image($track->cover, array('width' => 50)) ?>
			</td>
			<td class="track">
				<?= HTML::anchor(Route::model($track), HTML::chars($track->name), array('class' => 'track')) ?><br />
				<?= HTML::user($track->author_id) ?>
			</td>
			<td class="count"><?= $track->listen_count ?></td>
			<td class="date">
				<small class="ago"><?= HTML::time(Date::short_span($track->created, true, true), $track->created) ?></small>
			</td>
		</tr>
	<?php endforeach ?>

	</tbody>
</table>

<?php

		return ob_get_clean();
	}

}
