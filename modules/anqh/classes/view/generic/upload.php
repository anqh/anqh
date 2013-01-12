<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * File upload.
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_Upload extends View_Article {

	/**
	 * @var  string  URL for action target
	 */
	public $action;

	/**
	 * @var  string  URL for cancel action
	 */
	public $cancel;

	/**
	 * @var  array
	 */
	public $errors;


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

			if (self::$_request_type == Controller::REQUEST_AJAX) {
				$cancel_attributes = array('class' => 'ajaxify');
			} else {
				$cancel_attributes = null;
			}

			echo Form::open($this->action, array('enctype' => 'multipart/form-data'));

?>

		<fieldset>
			<?= Form::file('file') ?>
		</fieldset>

		<fieldset>
			<?= Form::csrf(); ?>
			<?= Form::button('save', '<i class="icon-upload icon-white"></i> ' . __('Upload'), array('type' => 'submit', 'class' => 'btn btn-primary btn-small')) ?>
			<?= $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), $cancel_attributes) : '' ?>
		</fieldset>

<?php

		echo Form::close();

		return ob_get_clean();
	}

}
