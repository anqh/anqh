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
		$this->title  = __('New posts');
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

<div class="ui small feed">

	<?php foreach ($this->topics as $topic): ?>
	<div class="event">
		<div class="content">
			<small class="date"><?= HTML::time(Date::short_span($topic->last_posted, true), $topic->last_posted) ?></small>
			<div class="summary">
				<?= HTML::anchor(Route::model($topic, '?page=last#last'), Forum::topic($topic), array('title' => $topic->name)) ?>
			</div>
		</div>
	</div>
	<?php endforeach; ?>

</div>

<?php

		return ob_get_clean();
	}

}
