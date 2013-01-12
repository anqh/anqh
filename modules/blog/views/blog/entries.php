<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Blog entries
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
foreach ($entries as $entry): $author = $entry->author(); ?>

<article>
	<header>
		<?php echo HTML::avatar($author['avatar'], $author['username']) ?>
		<h4><?php echo HTML::anchor(Route::model($entry), HTML::chars($entry->name), array('title' => $entry->name)) ?></h4>
		<span class="details">
		<?php echo __('By :user :ago', array(
			':user'  => HTML::user($author),
			':ago'   => HTML::time(Date::fuzzy_span($entry->created), $entry->created)
		)) ?>
		</span>
	</header>
</article>

<?php endforeach;
