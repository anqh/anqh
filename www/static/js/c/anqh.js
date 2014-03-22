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

	// Hover card, disabled until hover out fixed, use only one?
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
	}, 'a.hoverablee');


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

});
;/**
 * Form helper
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2009-2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($) {

	/**
	 * Input placeholder hint
	 *
	 * @author  Antti Qvickström (password patch)
	 * @author  Remy Sharp (original)
	 * @url     http://remysharp.com/2007/01/25/jquery-tutorial-text-box-hints/
	 */
	$.fn.hint = function (blurClass) {
		if ('placeholder' in document.createElement('input')) return;

		blurClass = blurClass || 'blur';

	  return this.each(function () {

	    // Get jQuery version of 'this' and capture the rest of the variable to allow for reuse
	    var $input = $(this),
	      placeholder = $input.attr('placeholder'),
	      isPassword = $input.attr('type') == 'password',
	      $form = $(this.form),
	      $win = $(window);

	    // Clear hint
	    function remove() {
	    	if (isPassword) {
	    		$password.remove();
	    		$input.show();
	    	} else {
	      	if ($input.val() === placeholder && $input.hasClass(blurClass)) {
	        	$input.val('').removeClass(blurClass);
	      	}
	    	}
	    }

	    // Only apply logic if the element has the attribute
	    if (placeholder) {
	    	if (isPassword) {

	    		// Add text input to handle placeholder
    			$input.attr('placeholder', null);
    			var $password = $input.clone();
    			var display = $input.css('display');
    			$password.hide()
    				.attr({
   						type: 'text',
   						id: this.id + '-hint',
   						name: $input.attr('name') + '-hint'
    				})
    				.addClass(blurClass)
    				.val(placeholder)
    				.insertAfter($input)
    				.focus(function() {
    					$password.hide();
    					$input.show().focus();
    				});
    			$input.blur(function() {
    				if (this.value === '') {
	    				$input.hide();
	    				$password.css('display', display);
    				}
    			});
    			if ($input.val() === '') {
    				$input.hide();
 	  				$password.css('display', display);
    			}

	    	} else {

		      // On blur, set value to placeholder attr if text is blank
		      $input.blur(function () {
		        if (this.value === '') {
		          $input.addClass(blurClass).val(placeholder);
		        }
		      }).focus(remove).blur();

	    	}

	      // Clear the pre-defined text when form is submitted
	      $form.submit(remove);

	      // Handles Firefox's autocomplete
	      $win.unload(remove);
	    }
	  });
	};
})(jQuery);
;/**
 * Google Maps helper
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($) {

	$.fn.googleMap = function(options) {
		var defaults = {
			lat:        60.1695,
			long:       24.9355,
			zoom:       14,
			mapTypeId:  google.maps.MapTypeId.ROADMAP,
			marker:     false,
			infowindow: false,
			mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU }
		};

		options = $.extend(defaults, options || {});

		// Geocode address if given
		if (options.city) {
			var geocoder = new google.maps.Geocoder()
			  , geocode  = (options.address ? options.address + ", " : '') + options.city;
			geocoder.geocode({ address: geocode }, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK && results.length) {
					options.lat    = results[0].geometry.location.lat();
					options.long   = results[0].geometry.location.lng();
					options.marker = true;
				}
			});
		}

		var center = new google.maps.LatLng(options.lat, options.long)
		  , map    = new google.maps.Map(this.get(0), $.extend(options, { center: center }));

		// Add marker
		if (options.marker) {
			var marker = new google.maps.Marker({
				position: center,
				map:      map,
				title:    options.marker ? '' : options.marker
			});
			if (options.infowindow) {
				var infowindow = new google.maps.InfoWindow({
					content: options.infowindow
				});
				google.maps.event.addListener(marker, 'click', function() {
					infowindow.open(map, marker);
				});
			}
		}

	};

})(jQuery);
;/**
 * Ajax dialog.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($) {

	$.fn.dialogify = function() {
		var href = this.attr('href') || this.attr('data-href');
		if (!href) {
			return false;
		}

		var title  = this.attr('data-dialog-title'),
		    width  = this.attr('data-dialog-width') || 300,
		    height = this.attr('data-dialog-height') || 'auto';
		$('<div style="display:none"></div>')
			.appendTo('body')
			.dialog({
				modal:     true,
				title:     title,
				width:     width,
				height:    height,
				closeText: '☓',
				open: function() {
					$(this).load(href);
				},
				close: function() {
					$(this).remove();
				}
			});

		return false;
	};

})(jQuery);
;/**
 * Ajaxified requests.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($) {

	$.fn.ajaxify = function(url, data, type, success) {
		var $target = $(this);

		type = (type == 'post' || type == 'POST') ? 'POST' : 'GET';
		$.ajax({
			type:    type,
			url:     url,
			data:    data,
			timeout: 2500,
			success: function(data) {
				$target.slideUp('fast', function _replace() {
					$target.replaceWith(data).slideDown('fast');
				});

				if (typeof success == 'function') {
					success(data);
				}
			},
			error: function(req, err) {
				if (err === 'error') {
					err = req.statusText;
				}
				alert('Fail: ' + err);
				$target.loading(true);
			},
			beforeSend: function() {
				$target.loading();
			}
		});

		return this;
	};

})(jQuery);
;/**
 * Event autocomplete.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($, Anqh) {

	$.fn.autocompleteEvent = function(options) {
		var $field = $(this);
		var cache = {};
		var lastXhr;

		var defaults = {
			eventId:   'event_id',
			limit:     25,
			minLength: 3,
			action:    'form',
			search:    'name',
			field:     'id:name:city:stamp_begin:url',
			order:     'stamp_begin.desc',
			position:  { collision: 'fit' }
		};
		options = $.extend(defaults, options || {});

		$field
			.on('change', function() {
				if (options.action == 'form') {
					$('input[name=' + options.eventId + ']') && $('input[name=' + options.eventId + ']').val('');
				}
			})
			.on('typeahead:selected', function(event, selection, name) {
					switch (options.action) {

						// Fill form
						case 'form':
							$('input[name=' + options.eventId + ']') && $('input[name=' + options.eventId + ']').val(selection.id);
							break;

						// Navigate URL
						case 'redirect':
							window.location = selection.url;
							break;

						// Execute action
						default:
							if (typeof options.action == 'function') {
								options.action(event, selection);
							}

					}
			})
			.typeahead([
				{
					name:     'events',
					valueKey: 'name',
					remote: {
						url:      Anqh.APIURL + '/v1/events/search',
						dataType: 'jsonp',
						replace:  function(url, uriEncodedQuery) {
							return url += '?' + $.param({
								q:      decodeURIComponent(uriEncodedQuery),
								limit:  25,
								filter: options.filter,
								search: options.search,
								field:  options.field,
								order:  options.order
							});
						},
						filter: function(parsedResponse) {
							return parsedResponse.events || [];
						}
					},
					template: function(event) {
						return $.datepicker.formatDate('dd.mm.yy', new Date(event.stamp_begin * 1000)) + ' ' + event.name + ', ' + event.city;
					}
				}
			]);

		return;
		$(this)
			.autocomplete({
				minLength: options.minLength,
				position:  options.position,

				source: function(request, response) {
					if (request.term in cache) {
						response(cache[request.term]);

						return;
					}

					lastXhr = $.ajax({
						url: Anqh.APIURL + '/v1/events/search',
						dataType: 'jsonp',
						success: function(data, status, xhr) {
							cache[request.term] = $.map(data.events, function(item) {
								return {
									'label': item.name,
									'stamp': item.stamp_begin,
									'city':  item.city,
									'value': item.name,
									'id':    item.id,
									'url':   item.url
								}
							});

							if (xhr === lastXhr) {
								response(cache[request.term]);
							}
						}
					});
				},

				select: function(event, ui) {
					switch (options.action) {

						// Fill form
						case 'form':
							$('input[name=' + options.eventId + ']') && $('input[name=' + options.eventId + ']').val(ui.item.id);
							field.val(ui.item.value);
							break;

						// Navigate URL
						case 'redirect':
							window.location = ui.item.url;
							break;

						// Execute action
						default:
							if (typeof options.action == 'function') {
								options.action(event, ui);
							}

					}
				}

			})
			.data('autocomplete')._renderItem = function(ul, item) {
				return $('<li></li>')
					.data('item.autocomplete', item)
					.append('<a>' + $.datepicker.formatDate('dd.mm.yy', new Date(item.stamp * 1000)) + ' ' + item.label + ', ' + item.city + '</a>')
					.appendTo(ul);
			};
	};

})(jQuery, Anqh);
;/**
 * GeoCoder autocomplete.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($) {

	// Geocoder autocomplete
	$.fn.autocompleteGeo = function(options) {
		var defaults = {
			map:       'map',
			country:   'fi',
			lang:      'en',
			latitude:  'latitude',
			longitude: 'longitude',
			limit:     10,
			minLength: 3,
			type:      'locality'
		};
		options = $.extend(defaults, options || {});

		var geocoder;

		var autocomplete = $(this)
			.autocomplete({
				minLength: options.minLength,

				source: function(request, response) {
					if (!geocoder) {
						geocoder = new google.maps.Geocoder();
					}

					geocoder.geocode({ address: request.term, region: options.country }, function(results, status) {
						if (status == google.maps.GeocoderStatus.OK) {
							var count = 0;

							response($.map(results, function(item) {
								if (count < options.limit && item.types.indexOf(options.type) > -1) {
									count++;
									var place = item.formatted_address.split(',');
									return {
										label:     item.formatted_address,
										value:     place[0].replace(/^[\d ]+/, ''),
										city:      place.shift(),
										description: place.join(','),
										latitude:  item.geometry.location.lat(),
										longitude: item.geometry.location.lng()
									};
								}
							}));
						}
					});
				},

				select: function(event, ui) {
					$('input[name=' + options.latitude + ']').val(ui.item.latitude);
					$('input[name=' + options.longitude + ']').val(ui.item.longitude);
				}

			})
			.data('autocomplete');

		autocomplete._renderItem = function(ul, item) {
			var $map = $('<img />')
				.attr({
					width: 100,
					height: 100,
					alt: 'Google Maps',
					src: 'http://maps.googleapis.com/maps/api/staticmap?center=' + item.latitude + ',' + item.longitude + '&zoom=10&size=100x100&sensor=false'
				});
			return	$('<li class="geocoded" />')
				.data('item.autocomplete', item)
				.append('<a><strong>' + item.city + '</strong><br />' + item.description + '</a>')
				.append($map)
				.appendTo(ul);
		};
		autocomplete._renderMenu = function(ul, items) {
			ul.addClass('geocoded');
			var self = this;
			$.each(items, function(index, item) {
				self._renderItem(ul, item);
			});
		};

	};

})(jQuery);
;/**
 * User autocomplete.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($, Anqh, undefined) {

	$.fn.autocompleteUser = function(options) {
		var $field = $(this);
		var cache  = {};
		var lastXhr;

		var defaults = {
			user:      0,
			userId:    'user_id',
			limit:     15,
			minLength: 2,
			maxUsers:  1,
			action:    'form',
			search:    'username',
			field:     'id:username:avatar:url',
			order:     'username.asc',
			position:  { collision: 'fit' }
		};
		options = $.extend(defaults, options || {});

		// Multiple users in one select
		var multiple = options.maxUsers > 1;

		function split(val) {
			return val.split(/,\s*/);
		}

		$field
				.select2({
					minimumInputLength: options.minLength,
					multiple:           multiple,
					containerCss:       { width: '100%' },
					tags:               multiple || undefined,
					ajax: {
						url:      Anqh.APIURL + '/v1/users/search',
						dataType: 'jsonp',
						data:     function(term, page) {
							return {
								q:     term,
								user:  options.user,
								limit: options.limit,
								field: options.field,
								order: options.order
							};
						},
						results: function(data, page) {
							return {
								results: data.users || [],
								text:    'username'
							};
						}
					},
					createSearchChoice: function(term) {
						return { id: term, username: term };
					},
					formatResult: function(user) {

						// Optgroup?
						if (!~~user.id) {
							return '<i class="text-muted">' + user.username + '</i>';
						}

						return (user.avatar ? '<img src="' + user.avatar + '" alt="Avatar" width="22" height="22" align="middle"> ' : '') + (user.username || '');
					},
					formatSelection: function(user) {
						return user.username || '';
					},
					initSelection: function($element, callback) {
						var tags = $.map(split($element.val()), function(username) {
							return { id: username, username: username };
						});

						callback(tags);
					}
				})
				.on('select2-selecting', function(event) {
					switch (options.action) {

						// Fill form
						case 'form':
							var $userId = $('input[name=' + options.userId + ']');

							if ($userId.length && ~~event.val) {
								$userId.val(event.val);
							}
							break;

					}
				});

		return;

		$field
			.on('typeahead:selected', function(event, selection, name) {
				switch (options.action) {

					// Fill form
					case 'form':
						if (multiple) {

							// Multiple users, one input
							var terms = split(this.value);
							terms.pop();
							terms.push(selection.value);
							terms.push('');
							$field.val(terms.join(', '));
							return false;

						} else if (options.maxUsers > 1) {

							// Multiple users, tokenized
							// @todo  Values to post
							var span = $('<span>')
								.attr({ 'user-id': selection.id })
								.text(selection.value);
							var link = $('<a>')
								.attr({ 'href': '#remove' })
								.text('x')
								.click(function() {
									$(this).parent().remove();
									return false;
								})
								.appendTo(span);

							span.insertBefore($field);
							$field.val('');
							return false;

						} else {

							// Single user
							$('input[name=' + options.userId + ']') && $('input[name=' + options.userId + ']').val(selection.id);
							$field.val(selection.value);

						}
						break;

					// Navigate URL
					case 'redirect':
						var location = $field.attr('data-redirect') || selection.url;
						$.each(selection, function _replace(key, value) {
							location = location.replace(':' + key, value);
						});
						window.location = location;
						break;

					// Execute action
					default:
						if (typeof options.action == 'function') {
							options.action(event, selection);
						}
				}
			})
			.typeahead(
				{
					minLength: options.minLength
				},
				{
					displayKey: 'username',
					source:     users.ttAdapter(),
					updater: function(item) {
						console.log('updater', item);
					},
					matcher: function(item) {
						console.log('matcher', item);
					},
					highlighter: function(item) {
						console.log('highlighter', item);
					}
/*					remote: {
						url:     Anqh.APIURL + '/v1/users/search',
						replace: function(url, uriEncodedQuery) {
							console.log(url, uriEncodedQuery);
							return url += '?query=' + uriEncodedQuery;
						},
						filter: function(parsedResponse) {
							return $.map(parsedResponse.users || [], function(user) {
								return {
									'label': item.username,
									'value': item.username,
									'image': item.avatar,
									'id':    item.id,
									'url':   item.url
								};
							});
						}
					}*/
				}
			);
/*
		$(this)
			.autocomplete({
				minLength: options.minLength,
				position:  options.position,

				source: function(request, response) {
					var term = multiple ? lastTerm(request.term) : request.term;

					if (term in cache) {
						response(cache[term]);
						return;
					}

					lastXhr = $.ajax({
						url: Anqh.APIURL + '/v1/users/search',
						dataType: 'jsonp',
						data: {
							'q':     term,
							'user':  options.user,
							'limit': options.limit,
							'field': options.field,
							'order': options.order
						},
						success: function(data, status, xhr) {
							cache[term] = $.map(data.users, function(item) {
								return {
									'label': item.username,
									'value': item.username,
									'image': item.avatar,
									'id':    item.id,
									'url':   item.url
								};
							});

							if (xhr === lastXhr) {
								response(cache[term]);
							}
						}
					});
				},

				// Custom minLength check for multiple terms
				search: function() {
					if (multiple) {
						var terms = split(this.value);

						if (terms.length > options.maxUsers || terms.pop().length < this.minLength) {
							return false;
						}
					}
				},

				// Don't insert value on focus
				focus: function() {
					if (multiple) return false;
				},

				select: function(event, ui) {
				}
			})
			.data('autocomplete')._renderItem = function(ul, item) {
				return $('<li></li>')
					.data('item.autocomplete', item)
					.append('<a>' + (item.image ? '<img src="' + item.image + '" alt="Avatar" width="22" height="22" align="middle" />' : '') + item.label + '</a>')
					.appendTo(ul);
			};
*/
	};

})(jQuery, Anqh);
;/**
 * Venue autocomplete.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($, Anqh) {

	$.fn.autocompleteVenue = function(options) {
		var defaults = {

		};
	};

	$.fn.autocompleteVenue = function(options) {
		var $field = $(this);

		var defaults = {
			venueId:   'venue_id',
			cityName:  'city_name',
			latitude:  'latitude',
			longitude: 'longitude',
			limit:     25,
			minLength: 1,
			action:    'form',
			source:    [],
			position:  { collision: 'fit' }

		};
		options = $.extend(defaults, options || {});

		$(this)
			.autocomplete({
				minLength: options.minLength,
				position:  options.position,
				source:    options.source,

				select: function(event, ui) {
					switch (options.action) {

						// Fill form
						case 'form':
							$('input[name=' + options.venueId + ']') && $('input[name=' + options.venueId + ']').val(ui.item.id);
							$('input[name=' + options.cityName + ']') && $('input[name=' + options.cityName + ']').val(ui.item.city);
							$('input[name=' + options.latitude + ']') && $('input[name=' + options.latitude + ']').val(ui.item.latitude);
							$('input[name=' + options.longitude + ']') && $('input[name=' + options.longitude + ']').val(ui.item.longitude);
							$field.val(ui.item.value);
							break;

						// Navigate URL
						case 'redirect':
							window.location = ui.item.url;
							break;

						// Execute action
						default:
							if (typeof options.action == 'function') {
								options.action(event, ui);
							}

					}
				}

			})
			.data('autocomplete')._renderItem = function(ul, item) {
				return $("<li></li>")
					.data('item.autocomplete', item)
					.append('<a>' + item.label + ', ' + item.city + '</a>')
					.appendTo(ul);
			};
	};


	// Foursquare autocomplete
	$.fn.foursquareVenue = function(options) {
		var defaults = {
			address:         'address',
			latitudeSearch:  'city_latitude',
			longitudeSearch: 'city_longitude',
			latitude:        'latitude',
			longitude:       'longitude',
			venueId:         'venue_id',
			categoryId:      'category_id',
			map:             'map',
			limit:           10,
			position:        { collision: 'fit' }

		};

		options = $.extend(defaults, options || {});

		$(this)
			.autocomplete({
				minLength: 2,
				position:  options.position,
				source:    function(request, response) {
					$.ajax({
						url:      Anqh.APIURL + '/v1/venues/foursquare',
						dataType: 'jsonp',
						type:     'get',
						data:     {
							method:  'venues',
							ll:      $('input[name=' + options.latitudeSearch + ']').val() + ',' + $('input[name=' + options.longitudeSearch + ']').val(),
							limit:   options.limit,
							intent:  'match',
							query:   request.term
						},
						success: function(data) {
							if (!data.venues) {
								return false;
							}

							response($.map(data.venues.groups[0].venues, function(item) {
								return {
									'id':       item.id,
									'label':    item.name + ', ' + item.address,
									'value':    item.name,
									'address':  item.address,
									'city':     item.city,
									'zip':      item.zip,
									'lat':      item.geolat,
									'long':     item.geolong,
									'category': item.primarycategory ? item.primarycategory.id : 0
								}
							}));
						}
					});
				},
				select: function(event, ui) {
					$('input[name=' + options.venueId + ']') && $('input[name=' + options.venueId + ']').val(ui.item.id);
					ui.item.category && $('input[name=' + options.categoryId + ']') && $('input[name=' + options.categoryId + ']').val(ui.item.category);
					ui.item.address && $('input[name=' + options.address + ']') && $('input[name=' + options.address + ']').val(ui.item.address);
					$('input[name=' + options.latitude + ']') && $('input[name=' + options.latitude + ']').val(ui.item.lat);
					$('input[name=' + options.longitude + ']') && $('input[name=' + options.longitude + ']').val(ui.item.long);
					$('#' + options.map).googleMap({ marker: true, lat: ui.item.lat, long: ui.item.long });
				}
			});
	};

})(jQuery, Anqh);
;/**
 * Image notes.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($) {

	$.fn.notes = function(n) {
		var notes       = n || {}
		  , $image      = $(this)
		  , imageOffset = $image.position()
		  , imageWidth  = $image.width()
		  , imageHeight = $image.height();

		$(notes).each(function() {
			add(this);
		});

		$(window).resize(function() {
			$('.note').remove();

			imageOffset = $image.position();
	    imageWidth  = $image.width();
	    imageHeight = $image.height();
			$(notes).each(function() {
				add(this);
			});
		});


		function add(note_data) {
			var scaleX = imageWidth / note_data.imageWidth || 1
			  , scaleY = imageHeight / note_data.imageHeight || 1
			  , noteX  = parseInt(imageOffset.left) + parseInt(note_data.x)
				, noteY  = parseInt(imageOffset.top) + parseInt(note_data.y);

			var $note = $('<div class="note" id="note-' + note_data.id + '" />').css({
				left: noteX * scaleX + 'px',
				top:  noteY * scaleY + 'px'
			});
			var $area = $('<div class="notea" />').css({
				width:  note_data.width * scaleX + 'px',
				height: note_data.height * scaleY + 'px'
			});
			var $text = $('<div class="notet label label-default" />')
				.append(note_data.url ? $('<a href="' + note_data.url + '" class="hoverable">' + note_data.name + '</a>') : note_data.name);

			$note
				.append($area)
				.append($text);
			$image.after($note);

			$('[data-note-id=' + note_data.id + ']') && $('[data-note-id=' + note_data.id + ']').hover(
				function _show() { $area.css({ visibility: 'visible' }); },
				function _hide() { $area.css({ visibility: 'hidden' }); }
			);
		}

	};

})(jQuery);
