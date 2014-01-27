<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Blog entry edit.
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
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
	 * @var  string  View class
	 */
	public $class = 'blog-entry';

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
	<?= Form::input_wrap(
		'name',
		$this->blog_entry->name,
		array('class' => 'input-lg', 'placeholder' => __('Title')),
		null,
		Arr::get($this->errors, 'name')
	) ?>

	<?= Form::textarea_wrap(
			'content',
			$this->blog_entry->content,
			array('class' => 'input-lg', 'placeholder' => __('Content')),
			true,
			null,
			Arr::get($this->errors, 'content'),
			null,
			true
	) ?>
</fieldset>

<fieldset>
	<?= Form::csrf(); ?>
	<?= Form::button('save', __('Save'), array('type' => 'submit', 'class' => 'btn btn-primary btn-lg')) ?>
	<?= $this->cancel ? HTML::anchor($this->cancel, __('Cancel'), array('class' => 'cancel')) : '' ?>
</fieldset>

<?php

		echo Form::close();

		return ob_get_clean();
	}

}
