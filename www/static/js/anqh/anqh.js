/**
 * Various generic JavaScripts for Anqh.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

Anqh = Anqh || {};

// Google Maps Geocoder
Anqh.geocoder = null;

// Google Maps Map
Anqh.map = null;


// Ajax loader
$.fn.loading = function(loaded) {
	if (loaded) {
		$(this).find('div.loading').remove();
	} else {
		$(this).append('<div class="loading"></div>');
	}

	return this;
};


// Initialize
$(function() {

	var
			hoverTimeout,
			hovercards = {};
	$(document).on({

		'mouseenter.hoverable': function() {
			var
					$this = $(this),
					href  = $this.attr('href');

			function popover(element, title, content) {
				element
						.removeClass('hoverable')
						.popover({
							trigger:   'hover',
							delay:     500,
							html:      true,
							title:     title,
							content:   content,
							container: 'body',
							placement: 'auto right'
						})
						.popover('show')
						.on('hide.bs.popover', function() {

							// Don't hide hovercard if hovering it
							var
									$this = $(this),
									$tip  = $this.data('bs.popover').tip();
							if ($tip.is(':hover')) {
								$tip.mouseleave(function() {
									$this.popover('hide')
								});

								return false;
							}
						});
			}

			hoverTimeout = setTimeout(function () {
				if (hovercards[href]) {
					popover($this, hovercards[href].title, hovercards[href].content);
				} else {

					// Load hovercard contents with ajax
					$.get(href + '/hover', function (response) {
						var $card = $(response);

						hovercards[href] = {
							title:   $card.find('header').remove().text().replace('<', '&lt;').replace('>', '&gt;'),
							content: $card.html()
						};

						popover($this, hovercards[href].title, hovercards[href].content);
					});
				}
			}, 500);

		},

		'mouseleave.hoverable': function() {
			clearTimeout(hoverTimeout);
		}

	}, 'a.hoverable');


	// Delete comment
	$('a.comment-delete').each(function deleteComment() {
		var $this = $(this);
		$this.data('action', function deleteAction() {
			var comment = $this.attr('href').match(/([0-9]*)\/delete/);
			if (comment) {
				$.get($this.attr('href'), function deleted() {
					$('#comment-' + comment[1]).slideUp();
				});
			}
		});
	});


	// Set comment as private
	$(document).delegate('a.comment-private', 'click', function privateComment(e) {
		e.preventDefault();

		var href    = $(this).attr('href');
		var comment = href.match(/([0-9]*)\/private/);
		if (comment) {
			$.get(href, function() {
				$('#comment-' + comment[1]).addClass('private');
			});
			$(this).fadeOut();
		}

		return false;
	});
	$(document).delegate('button[name=private-toggle]', 'click', function privateComment() {
		$('input[name=private]').val(~~$(this).hasClass('active'));
		$('input[name=comment]').toggleClass('private', ~~$(this).hasClass('active')).focus();
	});


	// Submit comment with ajax
	$(document).delegate('section.comments form', 'submit', function sendComment(e) {
		e.preventDefault();

		var comment = $(this).closest('section.comments');
		$.post($(this).attr('action'), $(this).serialize(), function onSend(data) {
			comment.replaceWith(data);
		});

		return false;
	});


	// Delete item confirmation
	$(document).on('click', 'a[class*="-delete"]', function(e) {
		e.preventDefault();

		var
				$this  = $(this),
				title  = $this.data('confirm') || $this.attr('title') || $this.text() || 'Are you sure you want to do this?',
				$modal = $('#dialog-confirm'),
				callback;

		if ($this.data('action')) {
			callback = function() { $this.data('action')(); $modal.modal('hide'); };
		} else if ($this.is('a')) {
			callback = function() { window.location = $this.attr('href'); };
		} else {
			callback = function() { $this.parent('form').submit(); $modal.modal('hide'); };
		}

		// Clear old modal
		if ($modal.length) {
			$modal.remove();
		}

		// Create new modal
		var $header = $('<div class="modal-header" />')
				.append('<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>')
				.append('<h4 class="modal-title" id="dialog-confirm-title">' + title + '</h4>');
		var $body = $('<div class="modal-body" />')
				.append('Are you sure?');
		var $confirm = $('<button type="button" class="btn btn-danger" />')
				.append('Yes, do it!')
				.on('click', callback);
		var $footer = $('<div class="modal-footer" />')
				.append($confirm)
				.append('<button type="button" class="btn btn-default" data-dismiss="modal">No, cancel</button>');
		$modal = $('<div class="modal fade" id="dialog-confirm" tabindex="-1" role="dialog" aria-labelledby="dialog-confirm-title" aria-hidden="true" />')
				.append($('<div class="modal-dialog modal-sm" />')
						.append($('<div class="modal-content" />')
							.append($header)
							.append($body)
							.append($footer)));
		$('body').append($modal);

		$modal.modal('show');
	});


	// Preview post
	$(document).on('click', 'button[name=preview]', function _preview(e) {
		e.preventDefault();

		var $this    = $(this)
		  , $form    = $this.closest('form')
		  , form     = $form.serialize() + '&preview=1'
		  , $post    = $this.closest('article')
		  , preClass = $this.attr('data-content-class') || 'media-body'
		  , addClass = $this.attr('data-preview-class') || 'post'
		  , prepend  = $this.attr('data-prepend') || '.post-edit';

		// Add ajax loader
		$post.loading();

		// Remove previous preview
		$post.find('.preview').remove();

		// Submit form
		$.post($form.attr('action'), form, function _response(data) {

			// Find preview data from result
			var $preview = preClass !== '*' ? $(data).find('.' + preClass) : $(data);

			// Mangle
			$preview
				.removeClass(preClass).addClass('preview ' + addClass)
				.find('header small.pull-right').remove();

			// Add to view
			$post.find(prepend).prepend($preview);

			// Scroll
			var $header = $('#header');
			$('html, body').animate({
				scrollTop: $post.find(prepend).offset().top - ($header ? $header.height() : 0)
			}, 250);

			// Remove loader
			$post.loading(true);

		});

	});


	// Ajaxify actions
	$(document).on('click', 'a.ajaxify', function _ajaxify() {
		var parent = $(this).attr('data-ajaxify-target');

		$(this).closest('section, article, aside' + (parent ? ', ' + parent : '')).ajaxify($(this).attr('href'));

		return false;
	});

	$(document).on('submit', 'form.ajaxify', function() {
		var $form = $(this);
		$(this).closest('section, aside').ajaxify($form.attr('action'), $form.serialize(), $form.attr('method'));

		return false;
	});

	// Ajaxify nofitications
	$(document).on('click', 'a.notification', function _ajaxify() {
		var parent = $(this).attr('data-ajaxify-target');

		$(this).closest('section, article, aside' + (parent ? ', ' + parent : ''))
			.ajaxify($(this).attr('href'), null, 'get', function _counter(response) {
				var notifications  = $(response).find('li').length
				  , $notifications = $('#visitor a.notifications');

				if ($notifications) {
					if (notifications) {
						$notifications.find('span').text(notifications);
					} else {
						$notifications.remove();
					}
				}
			});

		return false;
	});

	// Ajax dialogs
	$(document).on('click', 'a.dialogify', function(e) {
		e.preventDefault();

		$(this).dialogify();
	});


	// Keyboard pagination navigation
	$(document).on('keydown', function onKeydown(event) {
		if (event.target.type === undefined) {
			var link;
			switch (event.which) {
				case $.ui.keyCode.LEFT:  link = $('.pager .previous:not(.disabled) a').first().attr('href'); break;
				case $.ui.keyCode.RIGHT: link = $('.pager .next:not(.disabled) a').first().attr('href'); break;
			}
			if (link) {
				event.preventDefault();

				window.location = link;
			}
		}
	});


	// User default picture
	$('section.image-slideshow a[data-image-id]').on('click', function() {
		var $changes = $('a.image-change');
		var $image = $(this);
		if ($changes.length) {
			$changes.each(function(i) {
				var $link = $(this);
				var change = $link.attr('data-change');
				$link.toggleClass('disabled', $image.hasClass(change));
				$link.attr('href', $link.attr('href').replace(new RegExp(change + '.*$'), change + '=' + $image.attr('data-image-id')));
			});
		}

		var $delete = $('a.image-delete');
		if ($delete.length) {
			$delete.toggleClass('disabled', $(this).hasClass('default'));
			$delete.attr('href', $delete.attr('href').replace(/delete.*$/, 'delete=' + $(this).attr('data-image-id')));
		}
	});


	// Carousels
	$('.carousel').carousel({ interval: false });


	// Lady load images
	$('img.lazy').lazyload({
		failure_limit: 100
	});


	// Notifications
	$('a.notifications').on('click', function _notifications(e) {
		var $this = $(this);

		$.get($this.attr('href'), function _loadNotifications(response) {
			$this.off('click').popover({
				content:   response,
				html:      true,
				placement: 'bottom',
				trigger:   'click'
			}).popover('show');
		});

		return false;
	});


	// Element visibility toggle
	$('[data-toggle=show]').on('click', function(e) {
		e.preventDefault();

		var $this    = $(this);
		var selector = $this.data('target');
		var parent   = $this.data('parent') || 'body';
		var $parent  = $(parent);

		$parent.find('.show').removeClass('show').addClass('hidden');
		$parent.find(selector).removeClass('hidden').addClass('show');
	});


	// Theme selector
	$('[data-toggle=theme]').on('click', function(e) {
		e.preventDefault();

		var theme = $(this).data('theme') || 'mixed';

		$('body').removeClass('theme-light theme-mixed theme-dark').addClass('theme-' + theme);

		$.post('/set', { theme: theme });

	});

});
