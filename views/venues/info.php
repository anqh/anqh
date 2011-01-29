<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venue info
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php	if ($venue->homepage || $venue->description || $venue->info || $venue->hours || $venue->tags): ?>
<article class="information">
	<header>
		<h3><?php echo __('Basic information') ?></h3>
	</header>

	<dl>
		<?php echo empty($venue->homepage)    ? '' : '<dt>' . __('Homepage')      . '</dt><dd>' . HTML::anchor($venue->homepage) . '</dd>' ?>
		<?php echo empty($venue->description) ? '' : '<dt>' . __('Description')   . '</dt><dd>' . HTML::chars($venue->description) . '</dd>' ?>
		<?php echo empty($venue->info)        ? '' : '<dt>' . __('Extra info')    . '</dt><dd>' . HTML::chars($venue->info) . '</dd>' ?>
		<?php echo empty($venue->hours)       ? '' : '<dt>' . __('Opening hours') . '</dt><dd>' . HTML::chars($venue->hours) . '</dd>' ?>
		<?php echo !count($venue->tags)       ? '' : '<dt>' . __('Tags')          . '</dt><dd>' . implode(', ', $venue->tags->as_array('id', 'name')) . '</dd>' ?>
	</dl>
</article>
<?php	endif; ?>

<article class="contact">
	<header>
		<h3><?php echo __('Contact information') ?></h3>
	</header>

	<?php if ($venue->address || $venue->city_name): ?>
	<dl class="address">
		<dt><?php echo __('Address') ?></dt>
		<dd>
			<address>
				<?php echo HTML::chars($venue->address) ?><br />
				<?php echo HTML::chars($venue->city_name) ?> <?php echo HTML::chars($venue->zip) ?>
			</address>
		</dd>

		<?php if ($venue->latitude && $venue->longitude): ?>
		<dd>
			<?php echo HTML::anchor('#map', __('Toggle map')) ?>
		</dd>
		<?php endif; ?>

	</dl>

	<div id="map" style="display: none"><?php echo __('Map loading') ?></div>
		<?php
$options = array(
	'marker'     => HTML::chars($venue->name),
	'infowindow' => HTML::chars($venue->address) . '<br />' . HTML::chars($venue->city_name),
	'lat'        => $venue->latitude,
	'long'       => $venue->longitude
);
Widget::add('foot', HTML::script_source('
head.ready("anqh", function() {
	$(".contact a[href=#map]").click(function() {
		$("#map").toggle("fast", function() {
			$("#map").googleMap(' .  json_encode($options) . ');
		});

		return false;
	});
});
'));
?>
	<?php endif; ?>

</article>

<article class="foursquare">
	<header>
		<h3><?php echo __('Foursquare') ?></h3>
	</header>

	<?php if (!$foursquare): ?>

		<?php echo __('This venue has not been linked to Foursquare yet.'); ?>

	<?php else: ?>

		<?php echo HTML::anchor(Arr::path($foursquare, 'short_url'),
			HTML::image(Arr::path($foursquare, 'primarycategory.iconurl'), array(
				'alt'   => HTML::chars(Arr::path($foursquare, 'primarycategory.nodename')),
				'title' => HTML::chars(Arr::path($foursquare, 'primarycategory.nodename'))
			)) . ' ' . HTML::chars(Arr::path($foursquare, 'primarycategory.nodename'))) ?><br />

		<?php if ($mayor = Arr::path($foursquare, 'stats.mayor.user')) echo __('Mayor: :mayor, :city', array(
			':mayor' => HTML::anchor(
				'http://foursquare.com/user/' . Arr::get($mayor, 'id'),
				HTML::chars(Arr::get($mayor, 'firstname')) . ' ' . HTML::chars(Arr::get($mayor, 'lastname'))),
			':city'  => HTML::chars($mayor['homecity']))) ?><br />

		<?php echo __('Check-ins: :checkins', array(':checkins' => '<var>' . Arr::path($foursquare, 'stats.checkins') . '</var>')) ?><br />
		<?php echo __('Here now: :herenow', array(':herenow' => '<var>' . Arr::path($foursquare, 'stats.herenow') . '</var>')) ?><br />

		<?php if ($tips = Arr::path($foursquare, 'tips')): ?>
			<h4><?php echo __('Tips (:tips)', array(':tips' => '<var>' . count($tips) . '</var>')) ?></h4>
			<dl>
			<?php foreach (array_slice($tips, 0, 5) as $tip): ?>
				<dt><?php echo HTML::anchor(
					'http://foursquare.com/user/' . Arr::path($tip, 'user.id'),
					HTML::chars(Arr::path($tip, 'user.firstname')) . ' ' . HTML::chars(Arr::path($tip, 'user.lastname'))),
				', ', HTML::chars(Arr::path($tip, 'user.homecity')) ?>:</dt>
				<dd><?php echo Text::auto_p(HTML::chars(Arr::path($tip, 'text'))) ?></dd>
			<?php endforeach ?>
			</dl>
		<?php endif;

	endif;

	if ($admin):
		echo HTML::anchor('#map', __('Link to Foursquare'), array('class' => 'action', 'id' => 'link-foursquare'));

		echo Form::open(Route::get('venue')->uri(array('id' => Route::model_id($venue), 'action' => 'foursquare')), array('id' => 'form-foursquare-link', 'style' => 'display: none'));
?>
		<fieldset>
			<ul>
				<?php echo $venue->input('city_name', 'form/anqh') ?>
				<?php echo $venue->input('name', 'form/anqh', array('attributes' => array('placeholder' => __('Fill city first')))) ?>
				<?php echo $venue->input('address', 'form/anqh', array('attributes' => array('placeholder' => __('Fill venue first')))) ?>
				<?php echo $venue->input('foursquare_id', 'form/anqh', array('attributes' => array('readonly' => 'readonly'))) ?>
				<?php echo $venue->input('foursquare_category_id', 'form/anqh', array('attributes' => array('readonly' => 'readonly'))) ?>
			</ul>
		</fieldset>
		<fieldset>
			<?php echo Form::hidden('city_id', $venue->city->loaded() ? $venue->city->id : 0) ?>
			<?php echo Form::hidden('latitude', Arr::pick($venue->latitude, $venue->city->loaded() ? $venue->city->latitude : 0)) ?>
			<?php echo Form::hidden('longitude', Arr::pick($venue->longitude, $venue->city->loaded() ? $venue->city->longitude : 0)) ?>

			<?php echo Form::csrf() ?>
			<?php echo Form::submit_wrap('save', __('Link'), null, false) ?>

		</fieldset>
<?php
		echo Form::close();

		echo HTML::script_source('
head.ready("anqh", function() {

	$("#link-foursquare").click(function() {
		$(this).hide();
		$("#form-foursquare-link").show("fast");
		$("#map").show("fast", function() {
			$("#map").googleMap(' .  json_encode($options) . ');
		});
	});

	$("#field-city-name").autocompleteCity({ latitude: "latitude", longitude: "longitude" });

	$("#field-name").foursquareVenue({
		venueId: "foursquare_id",
		categoryId: "foursquare_category_id",
		latitudeSearch: "latitude",
		longitudeSearch: "longitude",
	});

});
');

	endif; ?>
</article>
