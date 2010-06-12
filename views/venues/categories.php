<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venue categories
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ul>
<?php foreach ($categories as $category): ?>

	<li class="group">

		<h3><?php echo HTML::anchor(Route::model($category), $category->name) ?></h3>
		<sup><?php echo HTML::chars($category->description) ?></sup>
		<ul>
		<?php $city = false; foreach ($category->venues as $venue): if ($venue->city_name == $city) continue; $city = $venue->city_name; ?>
			<li><?php echo HTML::anchor(Route::model($category) . '#' . mb_strtolower($venue->city_name), $venue->city_name) ?></li>
		<?php endforeach;	?>
		</ul>

	</li>
<?php endforeach; ?>

</ul>
