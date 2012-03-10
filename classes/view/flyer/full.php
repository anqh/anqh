<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Flyer_Full
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Flyer_Full extends View_Section {

	/**
	 * @var  Model_Image
	 */
	public $image;


	/**
	 * Create new view.
	 *
	 * @param  Model_Image  $image
	 */
	public function __construct(Model_Image $image) {
		parent::__construct();

		$this->image = $image;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<div class="image">
	<figure>

		<?= HTML::image($this->image->get_url()) ?>

	</figure>
</div>

<?php

		return ob_get_clean();
	}

}
