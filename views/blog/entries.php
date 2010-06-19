<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Blog entries
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php foreach ($entries as $entry): ?>

<article>
	<header>
		<?php echo HTML::avatar($entry->author->avatar, $entry->author->username) ?>
		<h4><?php echo HTML::anchor(Route::model($entry), HTML::chars($entry->name), array('title' => $entry->name)) ?></h4>
		<span class="details">
		<?php echo __('By :user :ago', array(
			':user'  => HTML::user($entry->author),
			':ago'   => HTML::time(Date::fuzzy_span($entry->created), $entry->created)
		)) ?>
		</span>
	</header>
</article>

<?php endforeach; ?>
