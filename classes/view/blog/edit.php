<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Blog_Edit
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Blog_Edit extends View_Section {

	/**
	 * @var  Model_Blog_Entry
	 */
	public $blog_entry;

	/**
	 * @var  string  Cancel URL
	 */
	public $cancel;

	/**
	 * @var  array  Form errors
	 */
	public $errors;


	/**
	 * Create new view.
	 *
	 * @param  Model_Blog_Entry  $blog_entry
	 */
	public function __construct(Model_Blog_Entry $blog_entry) {
		parent::__construct();

		$this->blog_entry = $blog_entry;
	}


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
				Form::input('name', $this->blog_entry->name, array('class' => 'input-xxlarge')),
				array('name' => __('Title')),
				Arr::get($this->errors, 'name')) ?>

			<?= Form::control_group(
				Form::textarea_editor('content', $this->blog_entry->content, array('class' => 'input-xxlarge'), true),
				array('content' => __('Content')),
				Arr::get($this->errors, 'content')) ?>
		</fieldset>

		<fieldset>
			<?= Form::csrf(); ?>
			<?= Form::button('save', __('Save'), array('type' => 'submit', 'class' => 'btn btn-success btn-large')) ?>
			<?= $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>
		</fieldset>

<?php

		echo Form::close();

		return ob_get_clean();
	}

}
