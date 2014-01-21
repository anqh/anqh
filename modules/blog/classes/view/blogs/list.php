<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Blogs List.
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Blogs_List extends View_Section {

	/**
	 * @var  Model_Blog_Entry[]
	 */
	public $blog_entries = null;


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		if (!$this->blog_entries):
			return '';
		endif;

		ob_start();

?>

<ul class="list-unstyled">

	<?php foreach ($this->blog_entries as $entry): ?>
	<li><?= __(':blog by :author', array(
				':blog' => HTML::anchor(Route::model($entry), HTML::chars($entry->name)),
				':author' => HTML::user($entry->author())
		)) ?></li>
	<?php endforeach; ?>

</ul>

<?php

		return ob_get_clean();
	}

}
