<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Blogs List.
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Blogs_List extends View_Section {

	/**
	 * @var  string  Section class
	 */
	public $class = 'blogentries cut';

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
		if (!$this->blog_entries) {
			return '';
		}

		ob_start();

?>

<ul class="unstyled">

	<?php foreach ($this->blog_entries as $entry): ?>
	<li><?= HTML::anchor(Route::model($entry), HTML::chars($entry->name)) ?></li>
	<?php endforeach; ?>

</ul>

<?php

		return ob_get_clean();
	}

}
