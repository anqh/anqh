<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Blogs List.
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
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
		if (!$this->blog_entries):
			return '';
		endif;

		ob_start();

?>

<div class="ui small feed">

	<?php foreach ($this->blog_entries as $entry): ?>
	<div class="event">
		<div class="content">
			<div class="summary"><?= HTML::anchor(Route::model($entry), HTML::chars($entry->name)) ?></div>
		</div>
	</div>
	<?php endforeach; ?>

</div>

<?php

		return ob_get_clean();
	}

}
