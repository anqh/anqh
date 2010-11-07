<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Similar venues
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>
<ul>

<?php
foreach ($venues as $similar):
	if ($venue->id != $similar->id):
?>
	<li>
		<?php if ($admin) echo HTML::anchor(
			Route::get('venue')->uri(array('id' => Route::model_id($venue), 'action' => 'combine', 'param' => $similar->id)) . '?token=' . Security::csrf(),
			__('Merge'),
			array('class' => 'action small venue-delete')) ?>
		<?php echo HTML::anchor(Route::model($similar), $similar->name) ?>, <?php echo HTML::chars($similar->city_name) ?>
	</li>
<?php
	endif;
endforeach;
?>
</ul>
