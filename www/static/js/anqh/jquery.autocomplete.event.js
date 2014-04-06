/**
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
					limit:    options.limit,
					name:     'events',
					valueKey: 'name',
					remote: {
						url:        Anqh.APIURL + '/v1/events/search',
						dataType:   'jsonp',
						beforeSend: function() {
							$field.closest('.form-group').toggleClass('loading', true);
						},
						replace: function(url, uriEncodedQuery) {
							return url += '?' + $.param({
								q:      decodeURIComponent(uriEncodedQuery),
								limit:  options.limit,
								filter: options.filter,
								search: options.search,
								field:  options.field,
								order:  options.order
							});
						},
						filter: function(parsedResponse) {
							$field.closest('.form-group').toggleClass('loading', false);

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
