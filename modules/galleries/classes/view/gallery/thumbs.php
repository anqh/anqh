<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Gallery_Thumbs
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
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

	</ul>

<?php

				else:

					// First copyright
					$multiple = true;

				endif;

?>

	<header><h3>&copy; <?= HTML::user($copyright) ?></h3></header>
	<ul class="thumbnails">

<?php endif; // Copyright ?>

		<li>

			<a class="thumbnail" href="<?= Route::url('gallery_image', array('gallery_id' => Route::model_id($this->gallery), 'id' => $image->id, 'action' => '')) ?>">
				<?= HTML::image($image->get_url('thumbnail', $this->gallery->dir)) ?>

				<?php if ($image->description): ?>
				<p class="description"><?= HTML::chars($image->description) ?></p>
				<?php endif; ?>

				<span class="stats">
				<?php if ($image->comment_count): ?>
					<i class="icon-comment icon-white"></i> <?= (int)$image->comment_count ?>
				<?php endif; ?>
					<i class="icon-eye-open icon-white"></i> <?= (int)$image->view_count ?>
				</span>
			</a>

		</li>

<?php	endforeach; ?>

	</ul>

<?php

		return ob_get_clean();
	}

}
