/**
 * Various generic JavaScripts for Anqh
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

// Anqh 'namespace'
var Anqh = {

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
				closeText: '✕',
/*				buttons: {
					'✓ Yes, do it!': function() { $(this).dialog('close'); action(); },
					'✕ No, cancel': function() { $(this).dialog('close'); }
				},*/
				buttons: [
					{ 'text': '✓ Yes, do it!', 'class': 'btn btn-danger', click: function() { $(this).dialog('close'); action(); } },
					{ 'text': '✕ No, cancel',  'class': 'btn', click: function() { $(this).dialog('close'); } }
				]
			});
		} else {
			$('#confirm-dialog').dialog('open');
		}
	}

};


// Google Maps
$.fn.googleMap = function(options) {

	// Asynchronous loading
	if (!Anqh.geocoder) {
		Anqh.geocoder = new google.maps.Geocoder();
	}

	var defaults = {
		lat: 60.1695,
		long: 24.9355,
		zoom: 14,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		marker: false,
		infowindow: false,
		mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU }
	};

	options = $.extend(defaults, options || {});

	// Geocode address if given
	if (options.address && options.city && options.address != '' && options.city != '') {
		var geocode = options.address + ", " + options.city;
		Anqh.geocoder.geocode({ address: geocode }, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK && results.length) {
				options.lat = results[0].geometry.location.lat();
				options.long = results[0].geometry.location.lng();
				options.marker = true;
			}
		});
	}

	var center = new google.maps.LatLng(options.lat, options.long);
	Anqh.map = new google.maps.Map(this.get(0), $.extend(options, { center: center }));

	// Add marker
	if (options.marker) {
		var marker = new google.maps.Marker({
			position: center,
			map: Anqh.map,
			title: options.marker ? '' : options.marker
		});
		if (options.infowindow) {
			var infowindow = new google.maps.InfoWindow({
				content: options.infowindow
			});
			google.maps.event.addListener(marker, 'click', function() {
				infowindow.open(Anqh.map, marker);
			});
		}
	}

};


// Hovercards
$.fn.hovercard = function() {

	if ($('#hovercard').length == 0) {
		$('body').append('<div id="hovercard"></div>');
		$('#hovercard').data('cache', []);
	}

	$(this).tooltip({
		effect: 'slide',
		predelay: 500,
		tip: '#hovercard',
		lazy: false,
		position: 'center left',
		onBeforeShow: function() {
			hovercard(this);
		}
	}).dynamic({
		top: {
			direction: 'up',
			bounce: true
		}
	});

	function hovercard(tip) {
		var $tip = tip.getTip();
		var href = tip.getTrigger().attr('href');
		var cache = $tip.data('cache');
		if (!cache[href]) {
			$tip.text('Loading...');
			$.get(href + '/hover', function(response) {
				tip.hide();
				$tip.html(cache[href] = response);
				tip.show();
			});
			$tip.data('cache', cache);
			return;
		}
		$tip.html(cache[href]);
	}

	return this;
};


// Theme switcher
$.fn.skinswitcher = function() {

	$(this).click(function() {
		switchskin($(this).attr('rel'));
		$.ajax({ url: $(this).attr('href') });
		return false;
	});

	function switchskin(skin) {
		//$('link[@rel*=style][title=' + skin + ']').first().disabled = false;
		$('link[@rel*=style][title]').each(function(i) {
			this.disabled = true;
			if ($(this).attr('title') == skin) {
				this.disabled = false;
			}
		});
	}

	return this;
};


// Open ajax dialog
$.fn.dialogify = function() {
	var href = this.attr('href') || this.attr('data-href');
	if (!href) { return false; }

	var title = this.attr('data-dialog-title');
	var width = this.attr('data-dialog-width') || 300;
	var height = this.attr('data-dialog-height') || 'auto';
	$('<div style="display:none"></div>')
		.appendTo('body')
		.dialog({
			modal: true,
			title: title,
			width: width,
			height: height,
			closeText: '✕',
			open: function() {
				$(this).load(href);
			},
			close: function() {
				$(this).remove();
			}
		});

	return false;
};


// Ajaxified mod requests
$.fn.ajaxify = function(url, data, type) {
	var $target = $(this);
	type = (type == 'post' || type == 'POST') ? 'POST' : 'GET';
	$.ajax({
		type: type,
		url: url,
		data: data,
		timeout: 2500,
		success: function(data) {
			$target
				.slideUp('fast', function() {
					$target
						.replaceWith(data)
						.slideDown('fast');
			});
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


// Ajax loader
$.fn.loading = function(loaded) {
	if (loaded) {
		$(this).find('div.loading').remove();
	} else {
		$(this).append('<div class="loading"></div>');
	}

	return this;
};


// Slideshow
$.fn.slideshow = function() {
	var $images = $(this).find('a');
	$images.click(function() {
		var $link = $(this);
		if ($link.hasClass('active')) { return false; }

		var url = $(this).attr('href');
		var wrap = $('#slideshow-image').fadeTo('fast', 0.5).loading();
		var loader = new Image();
		loader.onload = function() {
			wrap.fadeTo('fast', 1).loading(true);
			wrap.find('img').attr('src', url);
		};
		loader.src = url;

		$images.removeClass('active');
		$(this).addClass('active');

		return false;
	});
};


// City autocomplete
$.fn.autocompleteCity = function(options) {
	var defaults = {
		'map':       'map',
		'cityId':    'city_id',
		'country':   'FI',
		'lang':      'en',
		'address':   'address',
		'latitude':  'latitude',
		'longitude': 'longitude',
		'limit':     10,
		'minLength': 2
	};
	options = $.extend(defaults, options || {});

	$(this)
		.autocomplete({
			source: function(request, response) {
				$.ajax({
					url: Anqh.geoNamesURL + '/searchJSON',

					dataType: 'jsonp',

					data: {
						'username':        Anqh.geoNamesUser,
						'lang':            options.lang,
						'featureClass':    'P',
						'countryBias':     options.country,
						'style':           'full',
						'maxRows':         10,
						'name_startsWith': request.term
					},

					success: function(data) {
						response($.map(data.geonames, function(item) {
							return {
								'id':    item.geonameId,
								'label': item.name + (item.adminName1 ? ', ' + item.adminName1 : '') + ', ' + item.countryName,
								'value': item.name,
								'lat':   item.lat,
								'long':  item.lng
							};
						}));
					}
				})
			},

			minLength: options.minLength,

			select: function(event, ui) {
				$('input[name=' + options.cityId + ']').val(ui.item.id);
				$('input[name=' + options.latitude + ']').val(ui.item.lat);
				$('input[name=' + options.longitude + ']').val(ui.item.long);
				$('#' + options.map).googleMap({ lat: ui.item.lat, long: ui.item.long });
			}
		});
};

$.fn.autocompleteEvent = function(options) {
	var field = $(this);
	var cache = {};
	var lastXhr;

	var defaults = {
		'eventId':   'event_id',
		'limit':     25,
		'minLength': 3,
		'action':    'form',
		'search':    'name',
		'field':     'id:name:city:stamp_begin:url',
		'order':     'stamp_begin.desc'
	};
	options = $.extend(defaults, options || {});

	$(this)
		.autocomplete({
			minLength: options.minLength,

			source: function(request, response) {
				if (request.term in cache) {
					response(cache[request.term]);
					return;
				}

				lastXhr = $.getJSON(
					'/api/v1/events/search',
					{
						'q':      request.term,
						'limit':  25,
						'filter': options.filter,
						'search': options.search,
						'field':  options.field,
						'order':  options.order
					},
					function(data, status, xhr) {
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
				);
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
			return $("<li></li>")
				.data('item.autocomplete', item)
				.append('<a>' + $.datepicker.formatDate('dd.mm.yy', new Date(item.stamp * 1000)) + ' ' + item.label + ', ' + item.city + '</a>')
				.appendTo(ul);
		};
};


// Geocoder autocomplete
$.fn.autocompleteGeo = function(options) {
	var defaults = {
		map:       'map',
		cityId:    'city_id',
		country:   'FI',
		lang:      'en',
		address:   'address',
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
									value:     place[0],
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


// User autocomplete
$.fn.autocompleteUser = function(options) {
	var field = $(this);
	var cache = {};
	var lastXhr;

	var defaults = {
		'user':      0,
		'userId':    'user_id',
		'limit':     15,
		'minLength': 2,
		'maxUsers':  1,
		'tokenized': false,
		'action':    'form',
		'search':    'username',
		'field':     'id:username:avatar:url',
		'order':     'username.asc'
	};
	options = $.extend(defaults, options || {});

	// Facebook style tokenized list
	if (options.tokenized) {
		var width = field.width();
		field.wrap('<div class="tokenized" />');
		field.parent()
			.width(width)
			.click(function() {
				field.focus();
			});
	}

	// Multiple users in one select
	var multiple = (options.maxUsers > 1 && !options.tokenized);

	function split(val) {
		return val.split(/,\s*/);
	}

	function lastTerm(term) {
		return split(term).pop();
	}

	$(this)
		.autocomplete({
			minLength: options.minLength,

			source: function(request, response) {
				var term = multiple ? lastTerm(request.term) : request.term;

				if (term in cache) {
					response(cache[term]);
					return;
				}

				lastXhr = $.getJSON(
					'/api/v1/user/search',
					{
						'q':     term,
						'user':  options.user,
						'limit': options.limit,
						'field': options.field,
						'order': options.order
					},
					function(data, status, xhr) {
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
				);
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
				switch (options.action) {

					// Fill form
					case 'form':
						if (multiple) {

							// Multiple users, one input
							var terms = split(this.value);
							terms.pop();
							terms.push(ui.item.value);
							terms.push('');
							this.value = terms.join(', ');
							return false;

						} else if (options.maxUsers > 1) {

							// Multiple users, tokenized
							// @todo  Values to post
							var span = $('<span>')
								.attr({ 'user-id': ui.item.id })
								.text(ui.item.value);
							var link = $('<a>')
								.attr({ 'href': '#remove' })
								.text('x')
								.click(function() {
									$(this).parent().remove();
									return false;
								})
								.appendTo(span);

							span.insertBefore(field);
							this.value = '';
							return false;

						} else {

							// Single user
							$('input[name=' + options.userId + ']') && $('input[name=' + options.userId + ']').val(ui.item.id);
							field.val(ui.item.value);

						}
						break;

					// Navigate URL
					case 'redirect':
						window.location = ui.item.url;
						break;

				}
			}
		})
		.data('autocomplete')._renderItem = function(ul, item) {
			return $('<li></li>')
				.data('item.autocomplete', item)
				.append('<a>' + (item.image ? '<img src="' + item.image + '" alt="Avatar" width="22" height="22" align="middle" />' : '') + item.label + '</a>')
				.appendTo(ul);
		};

};


// Foursquare autocomplete
$.fn.foursquareVenue = function(options) {
	var defaults = {
		address: 'address',
		latitudeSearch: 'city_latitude',
		longitudeSearch: 'city_longitude',
		latitude: 'latitude',
		longitude: 'longitude',
		venueId: 'venue_id',
		categoryId: 'category_id',
		map: 'map',
		limit: 10
	};

	options = $.extend(defaults, options || {});
	$(this)
		.autocomplete({
			source: function(request, response) {
				$.ajax({
					url: '/api/v1/venues/foursquare',
					dataType: 'jsonp',
					type: 'get',
					data: {
						method: 'venues',
						geolat: $('input[name=' + options.latitudeSearch + ']').val(),
						geolong: $('input[name=' + options.longitudeSearch + ']').val(),
						l: options.limit,
						q: request.term
					},
					success: function(data) {
						if (!data.venues) {
							return false;
						}

						response($.map(data.venues.groups[0].venues, function(item) {
							console.debug(item);
							return {
								id: item.id,
								label: item.name + ', ' + item.address,
								value: item.name,
								address: item.address,
								city: item.city,
								zip: item.zip,
								lat: item.geolat,
								long: item.geolong,
								category: item.primarycategory ? item.primarycategory.id : 0
							}
						}));
					}
				})
			},
			minLength: 2,
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


// Image notes
$.fn.notes = function(n) {
	var notes;
	var image = this;
	var imageOffset = $(image).position();

	if (undefined != n) {
		notes = n;
	}

	$(notes).each(function() {
		add(this);
	});

	$(window).resize(function() {
		$('.note').remove();

		$(notes).each(function() {
			add(this);
		});
	});


	function add(note_data){
		var note_left = parseInt(imageOffset.left) + parseInt(note_data.x);
		var note_top  = parseInt(imageOffset.top) + parseInt(note_data.y);

		var note = $('<div class="note" id="note-' + note_data.id + '"></div>').css({
			'left': note_left + 'px',
			'top': note_top + 'px'
		});
		var area = $('<div class="notea"></div>').css({
			'width': note_data.width + 'px',
			'height': note_data.height + 'px'
		});
		var text = $('<div class="notet label label-inverse"></div>');
		if (note_data.url) {
			text.append($('<a href="' + note_data.url + '" class="hoverable">' + note_data.name + '</a>'));
		} else {
			text.append(note_data.name);
		}

		note
			.append(area)
			.append(text);
		image.after(note);

		$('[data-note-id=' + note_data.id + ']') && $('[data-note-id=' + note_data.id + ']').hover(
			function() {
				area.css({ 'visibility': 'visible' });
			},
			function() {
				area.css({ 'visibility': 'hidden' });
			}
		);
	}

};


// Initialize
$(function() {

	// Form input hints
	/*
	$('input:text, textarea, input:password').hint('hint');
	*/


	// Ellipsis ...
	/*
	$('.cut li').ellipsis();
	*/


	// Tooltips
	/*
	$('a[title], var[title], time[title]')
		.tooltip({
			effect: 'slide',
			position: 'top center'
		})
		.dynamic({
			bottom: { offset: [-10, 0] }
		});
	*/


	// Hover card
	if ('popover' in $.fn) {
		var hoverTimeout;
		$(document).on({
			mouseenter: function() {
				var $this = $(this);

				// Load ajax only once
				if (!$this.data('hovercard')) {
					$this.data('hovercard', true);

					// Use deferred to wait for ajax and delay before showing popover
					var dfd = $.Deferred();
					$.get($this.attr('href')+ '/hover', function cardLoaded(card) {
						var $card = $(card);

						// Create the popover when we have the contents
						$this.popover({
							title:   $card.find('header').remove().text(),
							content: $card.html(),
							delay:   { show: 500, hide: 500 },
							placement: function() {
								return $this.offset().left > window.innerWidth / 2 ? 'left' : 'right';
							}
						});

						// Ajax done
						dfd.resolve();

					});

					// Initial manual delay
					hoverTimeout = setTimeout(function delay() {
						dfd.done(function show() {
							$this.popover('show');
						});
					}, 500);

				}

			},
			mouseleave: function() {

				// Don't show the popover if not hovering anymore
				clearInterval(hoverTimeout);

			}
		}, 'a.hoverable');
	} else {
		$('a.hoverable').hovercard();
	}


	// Theme
	$('#dock a.theme, .menu-theme a').skinswitcher();

	// Delete comment
	$("a.comment-delete").each(function(i) {
		var action = $(this);
		action.data("action", function() {
			var comment = action.attr("href").match(/([0-9]*)\/delete/);
			if (comment) {
				$.get(action.attr("href"), function() {
					$("#comment-" + comment[1]).slideUp();
				});
			}
		});
	});


	// Set comment as private
	$("a.comment-private").live("click", function(e) {
		e.preventDefault();
		var href = $(this).attr("href");
		var comment = href.match(/([0-9]*)\/private/);
		$(this).fadeOut();
		if (comment) {
			$.get(href, function() {
				$("#comment-" + comment[1]).addClass("private");
			});
		}
		return false;
	});


	// Submit comment with ajax
	$("section.comments form").live("submit", function(e) {
		e.preventDefault();
		var comment = $(this).closest("section.comments");
		$.post($(this).attr("action"), $(this).serialize(), function(data) {
			comment.replaceWith(data);
		});

		return false;
	});


	// Delete item confirmation
	$('a[class*="-delete"]').live('click', function(e) {
		e.preventDefault();

		var $this = $(this);
		if ($this.data('action')) {
			Anqh.confirm_delete($this.data('confirm') ? $this.data('confirm') : $this.text(), function() { $this.data('action')(); });
		} else if ($this.is('a')) {
			Anqh.confirm_delete($this.data('confirm') ? $this.data('confirm') : $this.text(), function() { window.location = $this.attr('href'); });
		} else {
			Anqh.confirm_delete($this.data('confirm') ? $this.data('confirm') : $this.text(), function() { $this.parent('form').submit(); });
		}
	});


	// Ajaxify actions
	$('a.ajaxify').live('click', function() {
		$(this).closest('section, article').ajaxify($(this).attr('href'));

		return false;
	});
	$('form.ajaxify').live('submit', function() {
		var $form = $(this);
		$(this).closest('section.mod,section').ajaxify($form.attr('action'), $form.serialize(), $form.attr('method'));

		return false;
	});


	// Ajax dialogs
	$('a.dialogify').live('click', function(e) {
		e.preventDefault();

		$(this).dialogify();
	});


	// Ajax tabs
	/*
	$('body').delegate('.tabs a', 'click', function() {
		$(this).closest('section.mod').ajaxify($(this).attr('href'), null, 'GET');

		return false;
	});
	*/


	// Slideshows, scrollables
	$('div.scrollable').scrollable();
	$('div.slideshow').slideshow();


	// Sticky elements
	$('.sticky').each(function sticky() {
		var $this      = $(this)
		  , $container = $this.parent()
			, limit      = $container.offset().top + $container.outerHeight() - $this.outerHeight();

		$this.scrollToFixed({
			marginTop: $('#header').outerHeight(),
			limit:     limit,
			preFixed:  function sticked() { $this.addClass('sticked'); },
			postFixed: function unsticked() { $this.removeClass('sticked'); }
		});

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

});
