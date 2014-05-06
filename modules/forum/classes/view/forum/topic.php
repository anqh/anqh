<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Forum Topic view.
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Forum_Topic extends View_Section {

	/**
	 * @var  string  View class
	 */
	public $class = 'topic speech';

	/**
	 * @var  Model_Forum_Topic
	 */
	public $forum_topic;

	/**
	 * @var  View_Generic_Pagination
	 */
	public $pagination;

	/**
	 * @var  boolean  Private topic
	 */
	public $private = false;


	/**
	 * Create new view.
	 *
	 * @param  Model_Forum_Topic        $forum_topic
	 * @param  View_Generic_Pagination  $pagination
	 * @param  boolean                  $private
	 */
	public function __construct(Model_Forum_Topic $forum_topic, View_Generic_Pagination $pagination, $private = false) {
		parent::__construct();

		$this->forum_topic = $forum_topic;
		$this->pagination  = $pagination;
		$this->private     = $private;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		ob_start();

		$offset   = $this->pagination->offset;
		$previous = null;
		foreach ($this->forum_topic->posts($offset, $this->pagination->items_per_page) as $post):

			// Ignore
			if (!Permission::has($post, Model_Forum_Post::PERMISSION_READ)) {
				continue;
			}

			// Time difference between posts
			$current    = $post->created;
			$difference = ($previous && $current - $previous > Date::YEAR) ? Date::fuzzy_span($previous, $current) : false;
			if ($difference):

?>

		<div class="alert alert-warning post-old">
			&iexcl; <?= __('Previous post :ago', array(':ago' => $difference)) ?> !
		</div>

<?php

			endif;
			$previous = $current;

			$post = new View_Forum_Post($post, $this->forum_topic);
			$post->nth     = ++$offset;
			$post->private = $this->private;

			echo $post;

		endforeach;

?>

<script>
head.ready('anqh', function() {

	$('section.topic')

		// Edit post
		.on('click', 'a.post-edit', function _editPost(e) {
			e.preventDefault();

			var href = $(this).attr('href')
			  , post = href.match(/([0-9]*)\/edit/);

			$('#post-' + post[1] + ' .ago').fadeOut();
			$.get(href, function _loaded(data) {
				$('#post-' + post[1] + ' .media-body').replaceWith(data);
			});
		})

		// Quote post
		.on('click', 'a.post-quote', function _quotePost(e) {
			e.preventDefault();

			var href    = $(this).attr('href')
			  , post    = href.match(/([0-9]*)\/quote/)
			  , $article = $(this).closest('article');

			$('#post-' + post[1] + ' .ago').fadeOut();
			$.get(href, function _loaded(data) {
				$article.after(data);

				// Scroll form to view
				var $quote = $article.next('article');
				if ($quote.offset().top + $quote.outerHeight() > $(window).scrollTop() + $(window).height()) {
					window.scrollTo(0, $quote.offset().top + $quote.outerHeight() - $(window).height() );
				}

			});
		})

		// Save post
		.on('submit', '.post form', function(e) {
			e.preventDefault();

			var post = $(this).closest('article');

			post.loading();
			$.post($(this).attr('action'), $(this).serialize(), function(data) {
				post.replaceWith(data);
			});
		})

		// Cancel quote
		.on('click', '.quote a.cancel', function cancelQuote(e) {
			e.preventDefault();

			var $quote   = $(this).closest('article')
			  , $article = $quote.prev('article');

			$quote.slideUp(null, function slided() { $quote.remove(); });
			$article.find('.ago').fadeIn();
		})

		// Cancel edit
		.on('click', '.post-edit a.cancel', function cancelEdit(e) {
			e.preventDefault();

			var $post = $(this).closest('article');

			if (!$post.hasClass('quote')) {
				$.get($(this).attr('href'), function loaded(data) {
					$post.replaceWith(data);
				});
			}
		});

	// Delete post
	$('a.post-delete').each(function deletePost() {
		var $action = $(this);
		$action.data('action', function addAction() {
			var post = $action.attr('href').match(/([0-9]*)\/delete/);
			if (post) {
				$('#post-' + post[1]).slideUp();
				$.get($action.attr('href'));
			}
		});
	});

});
</script>

	<?php

		return ob_get_clean();
	}

}
