<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum topic
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
?>

<?php foreach ($posts as $post):

	// Ignore
	if (!Permission::has($post, Model_Forum_Post::PERMISSION_READ, $user)) continue;

	// Time difference between posts
	$current = strtotime($post->created);
	$difference = (isset($previous) && $current - $previous > Date::YEAR) ? Date::fuzzy_span($previous, $current) : false;
	if ($difference):
?>

<div class="divider post-old"><?php echo __('Previous post :ago', array(':ago' => $difference)) ?></div>

<?php endif;
	$previous = $current;

	echo View::factory('forum/post', array(
		'topic'  => $topic,
		'post'   => $post,
		'number' => $first++,
		'user'   => $user));

endforeach; ?>

<?php
echo HTML::script_source('
head.ready("anqh", function() {

	$("a.post-edit").live("click", function(e) {
		e.preventDefault();
		var href = $(this).attr("href");
		var post = href.match(/([0-9]*)\\/edit/);
		$("#post-" + post[1] + " .actions").fadeOut();
		$.get(href, function(data) {
			$("#post-" + post[1] + " .post-content").html(data);
		});
	});

	$("a.post-delete").each(function(i) {
		var action = $(this);
		action.data("action", function() {
			var post = action.attr("href").match(/([0-9]*)\\/delete/);
			if (post) {
				$("#post-" + post[1]).slideUp();
				$.get(action.attr("href"));
			}
		});
	});

	$("a.post-quote").live("click", function(e) {
		e.preventDefault();
		var href = $(this).attr("href");
		var post = href.match(/([0-9]*)\\/quote/);
		var article = $(this).closest("article");
		$("#post-" + post[1] + " .actions").fadeOut();
		$.get(href, function(data) {
			article.append(data);
			var quote = article.find("#quote");
			if (quote.offset().top + quote.outerHeight() > $(window).scrollTop() + $(window).height()) {
				window.scrollTo(0, quote.offset().top + quote.outerHeight() - $(window).height() );
			}
		});
	});

	$("section.post-content form").live("submit", function(e) {
		e.preventDefault();
		var post = $(this).closest("article");
		$.post($(this).attr("action"), $(this).serialize(), function(data) {
			post.replaceWith(data);
		});
	});

	$("section.post-content form a").live("click", function(e) {
		e.preventDefault();
		var post = $(this).closest("article");
		$.get($(this).attr("href"), function(data) {
			post.replaceWith(data);
		});
	});

	$("#quote form a").live("click", function(e) {
		e.preventDefault();
		var quote = $("#quote");
		var article = quote.closest("article");
		quote.slideUp(null, function() { quote.remove(); });
		article.find(".actions").fadeIn();
	});

});
');
