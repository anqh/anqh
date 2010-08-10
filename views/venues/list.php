<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Venue list
 *
 * @package    Venues
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ul>

	<?php foreach ($venues as $venue): ?>
	<li>
		<?php echo HTML::anchor(Route::model($venue), $venue->name, array('class' => 'venue')) ?>
	</li>
	<?php endforeach; ?>

</ul>
