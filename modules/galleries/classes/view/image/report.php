<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Image_Report
 *
 * @package    Galleries
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Image_Report extends View_Section {

	/**
	 * @var  Model_Image
	 */
	public $image;


	/**
	 * Create new view.
	 *
	 * @param  Model_Image  $image
	 */
	public function __construct($image = null) {
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

		$gallery = $this->image->gallery();

		echo Form::open(
			Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $this->image->id, 'action' => 'report')),
			array('class' => Request::current()->is_ajax() ? 'ajaxify' : '')
		);

?>

<fieldset>

	<?= Form::control_group(
			Form::input('reason', null, array('class' => 'input-block-level')),
			array('name' => __('Reason')),
			null,
			__('You can enter an optional reason for reporting this image, e.g. why it should be removed')
		) ?>

</fieldset>

<fieldset class="form-actions">
	<?= Form::button('save', __('Report'), array('type' => 'submit', 'class' => 'btn btn-danger btn-large')) ?>
	<?= Request::current()->is_ajax() ? '' : HTML::anchor(
		Route::url('gallery_image', array('gallery_id' => Route::model_id($gallery), 'id' => $this->image->id, 'action' => '')),
		__('Cancel'),
		array('class' => 'cancel')) ?>

	<?= Form::csrf() ?>
</fieldset>

<?php

		return ob_get_clean();
	}

}
