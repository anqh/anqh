<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venues by city
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
if (count($venues)):

	$category = $city = false;
	foreach ($venues as $venue):

		// City header
		if ($venue->city_name != $city):
			if ($city) echo '</ul></article>';
			$city = $venue->city_name;
?>
<article class="first">
	<header class="first">
		<h3><?php echo HTML::chars($city) ?></h3>
	</header>
	<ul>
<?php
			Text::alternate();
		endif;

?>
		<li class="grid4 <?php echo Text::alternate('first', '') ?>">
			<?php echo HTML::anchor(Route::model($venue), $venue->name) ?>
			<?php if ($venue->category->loaded()) echo ' (' . HTML::chars($venue->category->name) . ')' ?>
		</li>
<?php
	endforeach;

else: ?>

<article class="empty">
	<?php echo __('No venues yet.') ?>
</article>

<?php
endif;
