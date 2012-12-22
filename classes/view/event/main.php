<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * View_Event_Main
 *
 * @package    Events
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Event_Main extends View_Article {

	/**
	 * @var  Model_Event
	 */
	public $event;


	/**
	 * Create new article.
	 *
	 * @param  Model_Event  $event
	 */
	public function __construct(Model_Event $event) {
		parent::__construct();

		$this->event = $event;
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		if ($this->event->dj):

?>

<div class="dj">
	<h3><?php echo __('Line-up') ?></h3>

	<?php echo Text::auto_p(HTML::chars($this->event->dj)) ?>
</div>

<?php

		endif;

		if ($this->event->info):

?>

<div class="extra-info">
	<h3><?php echo __('Extra info') ?></h3>

	<?php echo BB::factory($this->event->info)->render() ?>
</div>

<?php

		endif;

		return ob_get_clean();
	}

}
