<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Galleries search results.
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Galleries_Search extends View_Section {

	/**
	 * @var  Model_Image[]
	 */
	public $images;


	/**
	 * Create new view.
	 *
	 * @param  Model_Image[]  $images
	 */
	public function __construct($images) {
		parent::__construct();

		$this->images = $images;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		$current_year = 0;
		foreach ($this->images as $image):
			$gallery = $image->gallery();

			// Subtitle
			$year    = date('Y', $image->created);
			if ($year !== $current_year):
				if ($current_year): ?>

</ul>

<?php endif; ?>

<header><h3><?= $year ?></h3></header>
<ul class="thumbnails">

<?php

				$current_year = $year;
			endif;

?>

	<li class="span2">
		<a class="thumbnail" href="<?= Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $image->id)) ?>">

			<?= HTML::image($image->get_url('thumbnail', $gallery->dir)) ?>

			<p class="description"><?= HTML::chars($gallery->name) ?></p>

<?php

			echo '<span class="stats">';

			// Image count
			echo '<i class="icon-camera icon-white"></i> ', $gallery->image_count;

			// Comment count
			if ($gallery->comment_count > 0):
				echo '<i class="icon-comment icon-white"></i> ', $gallery->comment_count;
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
