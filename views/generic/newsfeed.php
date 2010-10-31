<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Newsfeed
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

if (!empty($tabs)):
?>
<section id="newsfeed-tabs" class="tabs">
	<ul id="newsfeed-tabs" class="tabs">
		<li><?php echo HTML::anchor('#newsfeed-all', __('All')) ?></li>
		<li><?php echo HTML::anchor(Route::get('default')->uri(array('action' => '')) . '?newsfeed=friends', __('Friends')) ?></li>
	</ul>

	<div id="newsfeed-all">
<?php endif; ?>

		<ul>
			<?php foreach ($newsfeed as $item): ?>

				<li class="group">
					<?php echo HTML::avatar($item['user']->avatar, $item['user']->username, isset($mini) && $mini) ?>
					<?php echo HTML::user($item['user']) ?>
					<small class="ago"><?php echo HTML::time(Date::short_span($item['stamp'], true, true), $item['stamp']) ?></small>
					<?php echo $item['text'] ?>
				</li>
			<?php endforeach; ?>

		</ul>

<?php if (!empty($tabs)): ?>
	</div>
</section>

<?php
	echo HTML::script_source('
$("#newsfeed-tabs").tabs({
	selected: ' . (isset($tab) && $tab == 'friends' ? 1 : 0) . ',
	fx: {
		height: "toggle",
		opacity: "toggle",
		duration: "fast"
	},
	ajaxOptions: {
		error: function(xhr, status, index, anchor) {
			$(anchor.hash).html("' . __('Frak, error loading newsfeed :(') . '");
		}
	}
});
');
endif;
