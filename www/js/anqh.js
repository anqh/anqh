/**
 * Various generic JavaScripts for Anqh
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */

// Google Maps
var map;
var geocoder;
$.fn.googleMap = function(options) {
	var defaults = {
		lat: 60.1695,
		long: 24.9355,
		zoom: 14,
		mapTypeId: google.maps.MapTypeId.ROADMAP,
		marker: false,
		infowindow: false
	};

	options = $.extend(defaults, options || {});
	var center = new google.maps.LatLng(options.lat, options.long);

	map = new google.maps.Map(this.get(0), $.extend(options, { center: center }));
	if (options.marker) {
		var marker = new google.maps.Marker({
		  position: center,
		  map: map,
			title: options.marker ? '' : options.marker
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


// Delete confirmation dialog
function confirm_delete(title, action) {
	if (title === undefined) title = 'Are you sure you want to do this?';
	if (action === undefined) action = function() { return true; };
	if ($('#dialog-confirm').length == 0) {
		$('body').append('<div id="dialog-confirm" title="' + title + '">Are you sure?</div>');
		$('#dialog-confirm').dialog({
			dialogClass: 'confirm-delete',
			modal: true,
			close: function(ev, ui) { $(this).remove(); },
			closeText: '✕',
			buttons: {
				'✓ Yes, do it!': function() { $(this).dialog('close'); action(); },
				'✕ No, cancel': function() { $(this).dialog('close'); }
			}
		});
	} else {
		$('#confirm-dialog').dialog('open');
	}
}


// Hovercards
$.fn.hovercard = function() {

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
}


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


$(function() {

	// Google Maps
	geocoder = new google.maps.Geocoder();


	// Form input hints
	$('input:text, textarea, input:password').hint('hint');


	// Ellipsis ...
	$('.cut li').ellipsis();


	// Tooltips
	$('a[title], var[title], time[title]').tooltip({
		effect: 'slide',
		position: 'top center'
	});


	// Hover card
	if ($('#hovercard').length == 0) {
		$('body').append('<div id="hovercard"></div>');
		$('#hovercard').data('cache', []);
	}
	$('a.user, .avatar a, a.event').hovercard();


	// Theme
	$('#dock a.theme').skinswitcher();

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
		var action = $(this);
		if (action.data('action')) {
			confirm_delete(action.text(), function() { action.data('action')(); });
		} else if (action.is('a')) {
			confirm_delete(action.text(), function() { window.location = action.attr('href'); });
		} else {
			confirm_delete(action.text(), function() { action.parent('form').submit(); });
		}
	});


	// Ajaxify actions
	$('a.ajaxify').live('click', function() {
		$link = $(this);
		$link.closest('section.mod').ajaxify($link.attr('href'));

		return false;
	});
	$('form.ajaxify').live('submit', function() {
		$form = $(this);
		$(this).closest('section.mod').ajaxify($form.attr('action'), $form.serialize(), $form.attr('method'));
	});


	// Slideshows, scrollables
	$('div.scrollable').scrollable();
	$('div.slideshow').slideshow();


	// User default picture
	$('section.image-slideshow a[data-image-id]').click(function() {
		var $default = $('a.image-default');
		if ($default.length) {
			$default.toggleClass('disabled', $(this).hasClass('default'));
			$default.attr('href', $default.attr('href').replace(/default.*$/, 'default=' + $(this).attr('data-image-id')));
		}

		var $delete = $('a.image-delete');
		if ($delete.length) {
			$delete.toggleClass('disabled', $(this).hasClass('default'));
			$delete.attr('href', $delete.attr('href').replace(/delete.*$/, 'delete=' + $(this).attr('data-image-id')));
		}
	});

});
