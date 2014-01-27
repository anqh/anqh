<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Contact form.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Contact_Form extends View_Section {

	/**
	 * @var  string
	 */
	public $content;

	/**
	 * @var  string
	 */
	public $email;

	/**
	 * @var  array
	 */
	public $errors;

	/**
	 * @var  string
	 */
	public $name;

	/**
	 * @var  string
	 */
	public $subject;

	/**
	 * @var  Model_User
	 */
	public $user;


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		echo Form::open();

?>

<div class="row">
	<fieldset class="col-sm-6">

		<?= Form::input_wrap('name', $this->name, null, __('Name'), Arr::get($this->errors, 'name')) ?>

		<?= Form::input_wrap('email', $this->email, array('type' => 'email'), __('Email'), Arr::get($this->errors, 'email')) ?>

		<?= Form::input_wrap('subject', $this->subject, null, __('Subject'), Arr::get($this->errors, 'subject')) ?>

		<?= Form::textarea_wrap('content', $this->content, null, true, __('Content'), Arr::get($this->errors, 'content')) ?>

	</fieldset>
</div>

<fieldset>
	<?= Form::csrf(); ?>
	<?= Form::button('save', __('Send'), array('type' => 'submit', 'class' => 'btn btn-primary btn-lg')) ?>
</fieldset>

<?php

		echo Form::close();

		return ob_get_clean();
	}

}
