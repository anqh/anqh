<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venue_Info
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Venue_Info extends View_Section {

	/**
	 * @var  Model_Venue
	 */
	public $venue;


	/**
	 * Create new view.
	 *
	 * @param  Model_Venue  $venue
	 */
	public function __construct($venue) {
		parent::__construct();

		$this->venue = $venue;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		if ($this->venue->homepage || $this->venue->description || $this->venue->info || $this->venue->hours || $this->venue->tags):

?>

	<article class="information">
		<header>
			<h4><?php echo __('Basic information') ?></h4>
		</header>

		<dl>
			<?= empty($this->venue->homepage)    ? '' : '<dt>' . __('Homepage')      . '</dt><dd>' . HTML::anchor($this->venue->homepage) . '</dd>' ?>
			<?= empty($this->venue->description) ? '' : '<dt>' . __('Description')   . '</dt><dd>' . HTML::chars($this->venue->description) . '</dd>' ?>
			<?= empty($this->venue->info)        ? '' : '<dt>' . __('Extra info')    . '</dt><dd>' . HTML::chars($this->venue->info) . '</dd>' ?>
			<?= empty($this->venue->hours)       ? '' : '<dt>' . __('Opening hours') . '</dt><dd>' . HTML::chars($this->venue->hours) . '</dd>' ?>
		</dl>
	</article>

	<?php	endif; ?>

	<article class="contact">
		<header>
			<h4><?= __('Contact information') ?></h4>
		</header>

		<?php if ($this->venue->address || $this->venue->city_name): ?>
		<dl class="address">
			<dt><?= __('Address') ?></dt>
			<dd>
				<address>
					<?= HTML::chars($this->venue->address) ?><br />
					<?= HTML::chars($this->venue->city_name) ?> <?= HTML::chars($this->venue->zip) ?>
				</address>
			</dd>

			<?php if ($this->venue->latitude && $this->venue->longitude): ?>
			<dd>
				<?= HTML::anchor('#map', __('Toggle map')) ?>
			</dd>
			<?php endif; ?>

		</dl>

		<div id="map" style="display: none"><?= __('Map loading') ?></div>
			<?php $options = array(
				'marker'     => HTML::chars($this->venue->name),
				'infowindow' => HTML::chars($this->venue->address) . '<br />' . HTML::chars($this->venue->city_name),
				'lat'        => $this->venue->latitude,
				'long'       => $this->venue->longitude
			); ?>

		<script>
			head.ready('anqh', function() {
				$('.contact a[href=#map]').on('click', function toggleMap(event) {
					event.preventDefault();

					$('#map').toggle('fast', function() {
						$('#map').googleMap(<?= json_encode($options) ?>);
					});
				});
			});
		</script>

		<?php endif; ?>

	</article>

<?php

		return ob_get_clean();
	}

}
