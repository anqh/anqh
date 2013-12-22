<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Contact_Form
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
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

<fieldset>
	<?= Form::control_group(
		Form::input('name', $this->name, array('class' => 'input-block-level')),
		array('name' => __('Name')),
		Arr::get($this->errors, 'name')) ?>

	<?= Form::control_group(
		Form::input('email', $this->email, array('class' => 'input-block-level')),
		array('email' => __('Email')),
		Arr::get($this->errors, 'email')) ?>

	<?= Form::control_group(
		Form::input('subject', $this->subject, array('class' => 'input-block-level')),
		array('subject' => __('Subject')),
		Arr::get($this->errors, 'subject')) ?>

	<?= Form::control_group(
		Form::textarea('content', $this->content, array('class' => 'input-block-level'), true),
		array('content' => __('Content')),
		Arr::get($this->errors, 'content')) ?>
</fieldset>

<fieldset class="form-actions">
	<?= Form::csrf(); ?>
	<?= Form::button('save', __('Send'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
</fieldset>

<?php

		echo Form::close();

		return ob_get_clean();
	}

}
