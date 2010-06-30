<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Blog entries list
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<ul>

	<?php foreach ($entries as $entry): ?>
	<li><?= HTML::anchor(Route::model($entry), $entry->name) ?></li>
	<?php endforeach; ?>

</ul>
