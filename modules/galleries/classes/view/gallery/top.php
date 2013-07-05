<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Gallery_Top
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Gallery_Top extends View_Section {

	/**
	 * @var  Model_Image[]
	 */
	public $images;


	/**
	 * @var  string
	 */
	public $type;

	/**
	 * Create new view.
	 *
	 * @param  string         $type
	 * @param  Model_Image[]  $images
	 */
	public function __construct($type, $images = null) {
		parent::__construct();

		$this->type   = $type;
		$this->images = $images;

		switch ($type) {
			case Model_Image::TOP_COMMENTED: $this->title = __('Most Commented'); break;
			case Model_Image::TOP_RATED:     $this->title = __('Top Rated'); break;
			case Model_Image::TOP_VIEWED:    $this->title = __('Most Viewed'); break;
		}
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<ul class="thumbnails">

	<?php foreach($this->images as $image): $gallery = $image->gallery(); ?>

		<li>
			<a class="thumbnail" href="<?= Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $image->id)) ?>">

				<?= HTML::image($image->get_url('thumbnail', $gallery->dir)) ?>

				<p class="description"><?= HTML::chars($gallery->name) ?></p>
				<span class="stats">

	<?php switch ($this->type):

		case Model_Image::TOP_COMMENTED:
			echo '<i class="icon-comment"></i> ', Num::format($image->comment_count, 0);
			break;

		case Model_Image::TOP_RATED:
			echo '<i class="icon-star"></i> ', round($image->rate_total / $image->rate_count, 2);
			break;

		case Model_Image::TOP_VIEWED:
			echo '<i class="icon-eye-open"></i> ', Num::format($image->view_count, 0);
			break;

	endswitch; ?>

				</span>
			</a>
		</li>

<?php	endforeach; ?>

</ul>

<?php

		return ob_get_clean();
	}

}
