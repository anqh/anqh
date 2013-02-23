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

<ul>
	<li><?= HTML::anchor('#introduction', 'Introduction') ?></li>
	<li><?= HTML::anchor('#anqh', 'Anqh') ?></li>
	<li>
		<?= HTML::anchor('#api', 'API') ?>
		<ul>
			<li><?= HTML::anchor('#api-overview', 'Overview') ?></li>
			<li><?= HTML::anchor('#api-examples', 'Examples') ?></li>
			<li>
				<?= HTML::anchor('#api-events', 'Events') ?>
				<ul>
					<li><?= HTML::anchor('#api-events-browse', 'Browse') ?></li>
					<li><?= HTML::anchor('#api-events-event', 'Event') ?></li>
					<li><?= HTML::anchor('#api-events-search', 'Search') ?></li>
				</ul>
			</li>
			<li>
				<?= HTML::anchor('#api-users', 'Users') ?>
				<ul>
					<li><?= HTML::anchor('#api-users-search', 'Search') ?></li>
				</ul>
			</li>
			<li>
				<?= HTML::anchor('#api-venues', 'Venues') ?>
				<ul>
					<li><?= HTML::anchor('#api-venues-search', 'Search') ?></li>
				</ul>
			</li>
		</ul>
	</li>
</ul>

<?php

		return ob_get_clean();
	}

}
