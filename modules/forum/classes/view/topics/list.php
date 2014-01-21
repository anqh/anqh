<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Topics list.
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Topics_List extends View_Section {

	/**
	 * @var  Model_Forum_Topic[]
	 */
	public $topics = null;


	/**
	 * Create new view.
	 *
	 * @param  Model_Forum_Topic[]  $topics
	 */
	public function __construct($topics = null) {
		parent::__construct();

		$this->topics = $topics;
		$this->title  = __('New posts');
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		if (!$this->topics):
			return '';
		endif;

		ob_start();

?>

<ul class="list-unstyled">

		<?php foreach ($this->topics as $topic): ?>
		<li>
			<?= HTML::anchor(Route::model($topic, '?page=last#last'), Forum::topic($topic), array('title' => HTML::chars($topic->name))) ?>
		</li>
		<?php endforeach; ?>

</ul>

<?php

		return ob_get_clean();
	}

}
