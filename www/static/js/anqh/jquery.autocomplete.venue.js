/**
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
