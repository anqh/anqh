<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Galleries thumbnails.
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Galleries_Thumbs extends View_Section {

	/**
	 * @var  Model_Gallery[]
	 */
	public $galleries;

	/**
	 * @var  boolean  Wide view
	 */
	public $wide = true;

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

<div class="row">

<?php

				$current_year = true;

			elseif ($this->years):

				// Display year titles
				$year = date('Y', $gallery->date);
				if ($year !== $current_year):
					if ($current_year): ?>

</div>

<?php     endif; ?>

<header><h3><?= $year ?></h3></header>
<div class="row">

<?php

				$current_year = $year;
			endif;
		endif;

?>

	<article class="<?= $this->wide ? 'col-xs-6 col-sm-4 col-md-3 col-lg-2' : 'col-xs-6 col-md-4 col-lg-3' ?>">
		<div class="thumbnail">

			<?= HTML::anchor(Route::model($gallery), $default_image ? HTML::image($default_image->get_url('thumbnail', $gallery->dir)) : __('Thumbnail pending')) ?>

			<div class="caption">
				<h4><?= HTML::anchor(Route::model($gallery), HTML::chars($gallery->name)) ?></h4>
			</div>

			<small class="stats label label-default">
				<?= $gallery->image_count ?> <i class="fa fa-camera-retro"></i>
				<?php if ($gallery->comment_count > 0): ?>
				 &nbsp; <?= $gallery->comment_count ?> <i class="fa fa-comment"></i>
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
