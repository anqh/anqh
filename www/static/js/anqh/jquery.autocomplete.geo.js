/**
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
