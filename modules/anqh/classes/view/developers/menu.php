<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Developers_Menu
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Developers_Menu extends View_Section {

	/**
	 * Create new view.
	 */
	public function __construct() {
		parent::__construct();

		$this->title = 'Menu';
	}


	/**
	 * Content.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

?>

<ol>
	<li><?= HTML::anchor('#introduction', 'Introduction') ?></li>
	<li><?= HTML::anchor('#anqh', 'Anqh') ?></li>
	<li>
		<?= HTML::anchor('#api', 'API') ?>
		<ol>
			<li><?= HTML::anchor('#api-overview', 'Overview') ?></li>
			<li><?= HTML::anchor('#api-examples', 'Examples') ?></li>
			<li>
				<?= HTML::anchor('#api-events', 'Events') ?>
				<ol>
					<li><?= HTML::anchor('#api-events-browse', 'Browse') ?></li>
					<li><?= HTML::anchor('#api-events-event', 'Event') ?></li>
					<li><?= HTML::anchor('#api-events-search', 'Search') ?></li>
				</ol>
			</li>
			<li><?= HTML::anchor('#api-venus', 'Venues') ?></li>
			<li><?= HTML::anchor('#api-members', 'Members') ?></li>
		</ol>
	</li>
</ol>

<?php

		return ob_get_clean();
	}

}
