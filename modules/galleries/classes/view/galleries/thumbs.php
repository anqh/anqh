<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Galleries_Thumbs
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Galleries_Thumbs extends View_Section {

	/**
	 * @var  Model_Gallery[]
	 */
	public $galleries;

	/**
	 * @var  boolean  Display year titles
	 */
	public $years = false;


	/**
	 * Create new view.
	 *
	 * @param  Model_Gallery[]  $galleries
	 */
	public function __construct($galleries) {
		parent::__construct();

		$this->galleries = $galleries;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		$current_year = 0;
		foreach ($this->galleries as $gallery):
			$default_image = $gallery->default_image();

			if (!$this->years && !$current_year):

				// No titles

?>

<ul class="thumbnails">

<?php

				$current_year = true;

			elseif ($this->years):

				// Display year titles
				$year = date('Y', $gallery->date);
				if ($year !== $current_year):
					if ($current_year): ?>

</ul>

<?php     endif; ?>

<header><h3><?= $year ?></h3></header>
<ul class="thumbnails">

<?php

				$current_year = $year;
			endif;
		endif;

?>

	<li>
		<a class="thumbnail" href="<?= Route::model($gallery) ?>">

			<?= $default_image ? HTML::image($default_image->get_url('thumbnail', $gallery->dir)) : __('Thumbnail pending') ?>

			<p class="description"><?= HTML::chars($gallery->name) ?></p>

<?php

			// Rating
//				if ($gallery->rate_count > 0):
//					echo HTML::rating($gallery->rate_total, $gallery->rate_count, false, true, true), '<br />';
//				endif;

			echo '<span class="stats">';

			// Image count
			echo '<i class="icon-camera-retro"></i> ', $gallery->image_count;

			// Comment count
			if ($gallery->comment_count > 0):
				echo '<i class="icon-comment"></i> ', $gallery->comment_count;
			endif;

			echo '</span>';

?>

		</a>
	</li>

<?php endforeach; ?>

</ul>

<?php

		return ob_get_clean();
	}

}
