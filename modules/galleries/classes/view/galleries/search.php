<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Galleries search results.
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2013-2014 Antti Qvickström
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

</div>

				<?php endif; ?>

<header><h3><?= $year ?></h3></header>
<div class="row">

<?php

				$current_year = $year;
			endif;

?>

	<article class="col-xs-6 col-sm-4 col-md-3 col-lg-2">
		<div class="thumbnail">

			<?= HTML::anchor(
					Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $image->id)),
					HTML::image($image->get_url('thumbnail', $gallery->dir))
			) ?>

			<div class="caption">
				<h4><?= HTML::anchor(Route::model($gallery), HTML::chars($gallery->name)) ?></h4>
			</div>

			<small class="stats label label-default">
				<?= (int)$image->view_count ?> <i class="fa fa-eye"></i>
				<?php if ($image->comment_count): ?>
					&nbsp; <?= (int)$image->comment_count ?> <i class="fa fa-comment"></i>
				<?php endif; ?>
			</small>
		</div>
	</article>

<?php endforeach; ?>

</div>

<?php

		return ob_get_clean();
	}

}
