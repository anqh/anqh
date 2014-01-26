<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Gallery thumbnails.
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Gallery_Thumbs extends View_Section {

	/**
	 * @var  Model_Gallery
	 */
	public $gallery;


	/**
	 * Create new view.
	 *
	 * @param  Model_Gallery  $gallery
	 */
	public function __construct(Model_Gallery $gallery) {
		parent::__construct();

		$this->gallery = $gallery;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		$images = $this->gallery->images();

		$copyright = $multiple = null;

		foreach ($images as $image):

			// Add copyright
			if ($image->author_id != $copyright):
				$copyright = $image->author_id;
				if ($multiple):

					// Not first copyright

?>

	</div>

<?php

				else:

					// First copyright
					$multiple = true;

				endif;

?>

	<header><h3>&copy; <?= HTML::user($copyright) ?></h3></header>
	<div class="row">

<?php endif; // Copyright ?>

		<article class="col-xs-6 col-md-4 col-lg-3">
			<div class="thumbnail">

				<?= HTML::anchor(
					Route::url('gallery_image', array('gallery_id' => Route::model_id($this->gallery), 'id' => $image->id, 'action' => '')),
					HTML::image($image->get_url('thumbnail', $this->gallery->dir))
				) ?>

				<small class="stats label label-default">
					<?= (int)$image->view_count ?> <i class="fa fa-eye"></i>
					<?php if ($image->comment_count): ?>
						&nbsp; <?= (int)$image->comment_count ?> <i class="fa fa-comment"></i>
					<?php endif; ?>
				</small>

			</div>
		</article>

<?php	endforeach; ?>

	</div>

<?php

		return ob_get_clean();
	}

}
