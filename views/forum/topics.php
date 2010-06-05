<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum Topics
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php foreach ($topics as $topic): ?>

<article class="topic-<?php echo $topic->id ?>">
	<header>
		<h4 class="unit size5of6"><?php echo HTML::anchor(Route::model($topic, '?page=last#last'), HTML::chars($topic->name)) ?></h4>
		<ul class="details unit size1of6">
			<!-- <li class="unit size1of2"><?php echo HTML::icon_value(array(':views' => $topic->num_reads), ':views view', ':views views', 'views') ?></li> -->
			<li class="unit size1of1"><?php echo HTML::icon_value(array(':replies' => $topic->num_posts - 1), ':replies reply', ':replies replies', 'posts') ?></li>
		</ul>
	</header>
	<footer>
		<?php if (isset($area)):
				echo __('In :area.', array(
					':area' => HTML::anchor(Route::model($topic->area), HTML::chars($topic->area->name), array('title' => strip_tags($topic->area->description)))
				));
			endif;
			echo __('Last post by :user :ago ago', array(
				':user'  => HTML::user(false, $topic->last_poster),
				':ago'   => HTML::time(__(Date::fuzzy_span($topic->last_posted)), $topic->last_posted)
			)); ?>
	</footer>
</article>

<?php endforeach; ?>
