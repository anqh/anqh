<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Image Upload Help.
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Image_UploadHelp extends View_Section {

	/**
	 * Create new view.
	 */
	public function __construct() {
		parent::__construct();

		$this->title = __('Instructions');
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<ol>
	<li><?= __('Select the Event.') ?></li>
	<li><?= __('Add files by clicking the dropzone or <em>drag and drop</em> them to the designated area (Internet Explorer not supported).') ?></li>
	<li><?= __('Press <strong>Start upload</strong> and wait for your images to upload and process.') ?></li>
	<li><?= __('If you still have more images to upload, click the area or drag and drop again and repeat steps 2 and 3 until you are all done.') ?></li>
	<li><?= __('Thank you! Your images are now ready.') ?></li>
</ol>

<?php

		return ob_get_clean();
	}

}
