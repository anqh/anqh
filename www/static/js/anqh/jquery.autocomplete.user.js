/**
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

		if (multiple) {
			$field
					.select2({
						minimumInputLength: options.minLength,
						multiple: multiple,
						containerCss: { width: '100%' },
						tags: multiple || undefined,
						ajax: {
							url: Anqh.APIURL + '/v1/users/search',
							dataType: 'jsonp',
							data: function (term, page) {
								return {
									q: term,
									user: options.user,
									limit: options.limit,
									field: options.field,
									order: options.order
								};
							},
							results: function (data, page) {
								return {
									results: data.users || [],
									text: 'username'
								};
							}
						},
						createSearchChoice: function (term) {
							return { id: term, username: term };
						},
						formatResult: function (user) {

							// Optgroup?
							if (!~~user.id) {
								return '<i class="text-muted">' + user.username + '</i>';
							}

							return (user.avatar
									? '<img src="' + user.avatar + '" alt="Avatar" width="22" height="22" align="middle"> '
									: '') + (user.username || '');
						},
						formatSelection: function (user) {
							return user.username || '';
						},
						initSelection: function ($element, callback) {
							var tags = $.map(split($element.val()), function (username) {
								return { id: username, username: username };
							});

							callback(tags);
						}
					})
					.on('select2-selecting', function (event) {
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

		} else {

			$field
					.on('typeahead:selected', function (event, selection, name) {
						switch (options.action) {

							// Fill form
							case 'form':
								if (multiple) {
/*
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
											.click(function () {
												$(this).parent().remove();
												return false;
											})
											.appendTo(span);

									span.insertBefore($field);
									$field.val('');
									return false;
*/
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
					.typeahead([
						{
							limit:    options.limit,
							name:     'users',
							valueKey: 'username',
							remote: {
								url:        Anqh.APIURL + '/v1/users/search',
								dataType:   'jsonp',
								beforeSend: function() {
									$field.closest('.form-group').toggleClass('loading', true);
								},
								replace: function(url, uriEncodedQuery) {
									return url += '?' + $.param({
										q:      decodeURIComponent(uriEncodedQuery),
										user:   options.user,
										limit:  options.limit,
										search: options.search,
										field:  options.field,
										order:  options.order
									});
								},
								filter: function(parsedResponse) {
									$field.closest('.form-group').toggleClass('loading', false);

									return parsedResponse.users || [];
								}
							},
							template: function(user) {
								return '<img src="' + user.avatar + '" alt="Avatar" width="22" height="22" align="middle"> ' + user.username;
							}
/*						{ minLength: options.minLength },
						{
							displayKey: 'username',
							source: users.ttAdapter(),
							updater: function (item) {
								console.log('updater', item);
							},
							matcher: function (item) {
								console.log('matcher', item);
							},
							highlighter: function (item) {
								console.log('highlighter', item);
							}*/
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
					]);

		}
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
