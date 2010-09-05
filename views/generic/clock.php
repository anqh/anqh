<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Clock
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

// Sun
if ($user && $user->latitude && $user->longitude) {
	$latitude = $user->latitude;
	$longitude = $user->longitude;
} else {
	$latitude = 60.1829;
	$longitude = 24.9549;
}
$sun = date_sun_info(time(), $latitude, $longitude);
$sunrise = __('Sun rises at :sunrise, sun sets at :sunset', array(
	':sunrise' => Date::format(Date::TIME, $sun['sunrise']),
	':sunset'  => Date::format(Date::TIME, $sun['sunset'])
));

// Weather
if ($user && $user->city_name) {
	$location = $user->city_name;
} else {
	$location = 'Helsinki';
}
$weather = Weather::get_weather($location);
$today = $weather['wind'] . ', ' . $weather['humidity'];
$tomorrow = array();
$next = array();
$d = 0;
foreach ($weather['forecast'] as $day => $forecast) {
	$min = ($forecast['low'] > 0 ? '+' : '') . $forecast['low'] . '&deg;';
	$max = ($forecast['high'] > 0 ? '+' : '') . $forecast['high'] . '&deg;';
	switch ($d) {
		case 0: $today = __('Min: :min, Max: :max', array(':min' => $min, ':max' => $max)) . ', ' . $today; break;
		case 1: $tomorrow = $max . ' ' . HTML::chars($forecast['condition']); break;
		default: $next[] = $day . ' ' . $max . ' ' . HTML::chars($forecast['condition']);
	}
	$d++;
}
?>

<time class="clock" title="<?= HTML::chars($sunrise) ?>">
	<span class="day"><?php echo __(':day, week :week', array(':day' => date('l'), ':week' => date('W'))) ?></span><br />
	<span class="time"><?php echo Date::format(Date::TIME) ?></span><br />
	<span class="date"><?php echo Date::format(Date::DMY_LONG) ?></span>
</time>

<br />

<?php if ($weather): ?>
<p class="weather">
	<?php echo HTML::chars($weather['postal_code']), ' ', __('today') ?><br />
	<var title="<?php echo $today ?>"><?php echo ($weather['temperature'] > 0 ? '+' : ''), $weather['temperature'] ?>&deg; <?php echo $weather['condition'] ?></var><br />
	<?php echo __('Tomorrow') ?><br />
	<var title="<?php echo implode(', ', $next) ?>"><?php echo $tomorrow ?></var>
</p>
<?php endif; ?>
