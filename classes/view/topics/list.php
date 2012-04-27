<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Topics_List
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Topics_List extends View_Section {

	/**
	 * @var  string  Section class
	 */
	public $class = 'topics cut';

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
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		if (!$this->topics) {
			return '';
		}

		ob_start();

?>

<ul class="unstyled">

		<?php foreach ($this->topics as $topic): ?>
		<li>
			<?php echo HTML::anchor(Route::model($topic, '?page=last#last'), HTML::chars($topic->name), array('title' => '[' . Date::short_span($topic->last_posted, false) . '] ' . $topic->name)) ?>
		</li>
		<?php endforeach; ?>

</ul>

<?php

		return ob_get_clean();
	}

}
