/**
 * User autocomplete.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($, Anqh) {

	$.fn.autocompleteUser = function(options) {
		var $field = $(this);
		var cache = {};
		var lastXhr;

		var defaults = {
			user:      0,
			userId:    'user_id',
			limit:     15,
			minLength: 2,
			maxUsers:  1,
			tokenized: false,
			action:    'form',
			search:    'username',
			field:     'id:username:avatar:url',
			order:     'username.asc',
			position:  { collision: 'fit' }
		};
		options = $.extend(defaults, options || {});

		// Facebook style tokenized list
		if (options.tokenized) {
			var width = $field.width();
			$field.wrap('<div class="tokenized" />');
			$field.parent()
				.width(width)
				.click(function() {
					$field.focus();
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
								$field.val(ui.item.value);

							}
							break;

						// Navigate URL
						case 'redirect':
							var location = $field.attr('data-redirect') || ui.item.url;
							$.each(ui.item, function _replace(key, value) {
								location = location.replace(':' + key, value);
							});
							window.location = location;
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
					.append('<a>' + (item.image ? '<img src="' + item.image + '" alt="Avatar" width="22" height="22" align="middle" />' : '') + item.label + '</a>')
					.appendTo(ul);
			};

	};

})(jQuery);
