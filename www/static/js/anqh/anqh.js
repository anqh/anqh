/**
 * Various generic JavaScripts for Anqh
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

head.ready([ 'jquery', 'semantic' ], function() {

	// Initialize dropdown menus
	$('.ui.dropdown').dropdown();

});

// Anqh 'namespace'
/*
var Anqh = {

	// Anqh API URL
	APIURL: '/api',

	// GeoNames API URL
	geoNamesURL: null,

	// GeoNames username
	geoNamesUser: null,

	// Google Maps Geocoder
	geocoder: null,

	// Google Maps Map
	map: null,

	// Delete confirmation dialog
	confirm_delete: function(title, action) {
		if (title === undefined) title = 'Are you sure you want to do this?';
		if (action === undefined) action = function() { return true; };
		if ($('#dialog-confirm').length == 0) {
			$('body').append('<div id="dialog-confirm" title="' + title + '">Are you sure?</div>');
			$('#dialog-confirm').dialog({
				dialogClass: 'confirm-delete',
				modal: true,
				close: function(ev, ui) { $(this).remove(); },
				closeText: '☓',
				buttons: [
					{ 'text': '✓ Yes, do it!', 'class': 'btn btn-danger', click: function() { $(this).dialog('close'); action(); } },
					{ 'text': '☓ No, cancel',  'class': 'btn btn-inverse', click: function() { $(this).dialog('close'); } }
				]
			});
		} else {
			$('#confirm-dialog').dialog('open');
		}
	}

};

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

	// Hover card
	var hoverTimeout;
	$(document).on({
		mouseenter: function() {
			var $this    = $(this)
				, $popover = $this.data('popover');

			// Initialize popover
			if (!$popover) {
				hoverTimeout = setTimeout(function _delay() {
					$.get($this.attr('href') + '/hover', function _loaded(response) {
						var $card = $(response);

						$this.popover({
							trigger:   'manual',
							html:      true,
							title:     $card.find('header').remove().text().replace('<', '&lt;').replace('>', '&gt;'),
							content:   $card.html(),
							container: 'body',
							placement: function() {
								var offset = $this.offset();

								return offset.top < 100 ? 'bottom' : (offset.left > window.innerWidth / 2 ? 'left' : 'right');
							}
						});

						$this.popover('show');
					});
				}, 500);
			} else {
				$this.popover('show');
			}
		},
		mouseleave: function() {
			var $this = $(this);

			if (!$this.data('popover')) {
				clearInterval(hoverTimeout);
			} else {
				$this.popover('hide');
			}
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
	$(document).delegate('button[name=private-toggle]', 'click', function privateComment(e) {
		$('input[name=private]').val(~~$(this).hasClass('active'));
		$('input[name=comment]').focus();
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
	$('a[class*="-delete"]').live('click', function(e) {
		e.preventDefault();

		var $this = $(this)
			, title = $this.data('confirm') || $this.attr('title') || $this.text();

		if ($this.data('action')) {
			Anqh.confirm_delete(title, function _confirm() { $this.data('action')(); });
		} else if ($this.is('a')) {
			Anqh.confirm_delete(title, function _confirm() { window.location = $this.attr('href'); });
		} else {
			Anqh.confirm_delete(title, function _confirm() { $this.parent('form').submit(); });
		}
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
				.find('.ago').remove();

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

		$(this).closest('section, article' + (parent ? ', ' + parent : '')).ajaxify($(this).attr('href'));

		return false;
	});

	$('form.ajaxify').live('submit', function() {
		var $form = $(this);
		$(this).closest('section').ajaxify($form.attr('action'), $form.serialize(), $form.attr('method'));

		return false;
	});

	// Ajaxify nofitications
	$(document).on('click', 'a.notification', function _ajaxify() {
		var parent = $(this).attr('data-ajaxify-target');

		$(this).closest('section, article' + (parent ? ', ' + parent : ''))
			.ajaxify($(this).attr('href'), null, 'get', function _counter(response) {
				var notifications  = $(response).find('li').length
				  , $notifications = $('.menuitem-notifications a.notifications');

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
	$('a.dialogify').live('click', function(e) {
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
	$('section.image-slideshow a[data-image-id]').click(function() {
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

});
*/
