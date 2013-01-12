<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Side tabs
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<section id="<?php echo $id ?>" class="mod tabs">
	<ul>
		<?php	$t = 0; $selected = 0; foreach ($tabs as $tab): $selected = !empty($tab['selected']) ? $t : $selected; $t++; ?>
		<li><?php echo HTML::anchor($tab['href'], $tab['title']) ?></li>
		<?php	endforeach; ?>
	</ul>
	<?php	foreach ($tabs as $tab) echo $tab['tab']; ?>
</section>
<?php

echo HTML::script_source('
head.ready("jquery-ui", function() {
	$("#' . $id . '")
		.tabs({
			"selected": ' . $selected . ',
			"collapsible": true,
			"fx": {
				"height": "toggle",
				"opacity": "toggle",
				"duration": "fast"
			}
		})
	.show();
});');
