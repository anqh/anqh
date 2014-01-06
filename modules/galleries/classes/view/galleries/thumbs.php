<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Galleries_Thumbs
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

<div class="ui four items">

<?php


				$current_year = true;

			elseif ($this->years):

				// Display year titles
				$year = date('Y', $gallery->date);
				if ($year !== $current_year):
					if ($current_year): ?>

</div>

<?php     endif; ?>

<h3 class="ui header"><?= $year ?></h3>
<div class="ui four items">

<?php

				$current_year = $year;
			endif;
		endif;

?>

	<a class="item" href="<?= Route::model($gallery) ?>">
		<div class="image">
			<?= $default_image ? HTML::image($default_image->get_url('thumbnail', $gallery->dir)) : __('Thumbnail pending') ?>
		</div>

		<div class="content">
			<p class="name"><?= HTML::chars($gallery->name) ?></p>
		</div>

		<div class="extra">
			<?= $gallery->image_count ?> <i class="retro camera icon"></i>

			<?php if ($gallery->comment_count > 0): ?>
			<?= $gallery->comment_count ?> <i class="comment icon"></i>
			<?php endif; ?>
		</div>
	</a>

<?php endforeach; ?>

</div>

<?php

		return ob_get_clean();
	}

}
