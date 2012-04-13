<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Blogs_Index
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Blogs_Index extends View_Section {

	/**
	 * @var  Model_Blog_Entry[]
	 */
	public $blog_entries;


	/**
	 * Create new view.
	 *
	 * @param  Model_Blog_Entry[]  $blog_entries
	 */
	public function __construct($blog_entries = null) {
		parent::__construct();

		$this->blog_entries = $blog_entries;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		if ($this->blog_entries && count($this->blog_entries)):

			// List blog entries
			foreach ($this->blog_entries as $blog_entry):
				/** @var  Model_Blog_Entry  $blog_entry */

				$author = $blog_entry->author();

?>

<article class="row blog-entry">
	<div class="span1"><?= HTML::avatar($author['avatar'], $author['username']) ?></div>

	<div class="span7">
		<header>
			<h4><?= HTML::anchor(Route::model($blog_entry), HTML::chars($blog_entry->name)) ?></h4>
			<p><?= __('By :user :ago', array(
				':user'  => HTML::user($author),
				':ago'   => HTML::time(Date::fuzzy_span($blog_entry->created), $blog_entry->created)
			)) ?></p>
		</header>
	</div>
</article>

<?php

			endforeach;

		else:

			// No blog entries available
			echo new View_Alert(__('Alas, the quill seems to be dry, no blog entries found.'), View_Alert::INFO);

		endif;

		return ob_get_clean();
	}

}
