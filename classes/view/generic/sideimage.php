<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * SideImage
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_SideImage extends View_Article {

	/**
	 * @var  Model_Image|string
	 */
	public $image;

	/**
	 * @var  string
	 */
	public $link;


	/**
	 * Create new view.
	 *
	 * @param  Model_Image|string  $image
	 * @param  string              $link
	 */
	public function __construct($image = null, $link = null) {
		parent::__construct();

		$this->span  = 4;
		$this->image = $image;
		$this->link  = $link;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {

		// Parse image element
		if ($this->image) {
		}

		ob_start();

		if ($this->image):
			$image = HTML::image(is_string($this->image) ? $this->image : $this->image->get_url(), array('width' => 290));

?>

<div id="slideshow-image">
	<?php echo isset($this->link) && $this->link ? HTML::anchor($this->link, $image) : $image ?>
</div>

<?php else: ?>

<div class="well">
	<?php echo __('No images yet.') ?>
</div>

<?php

		endif;

		return ob_get_clean();
	}

}
