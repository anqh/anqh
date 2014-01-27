bbCodeSettings = {
	nameSpace: 'bbcode',
	onTab: { keepDefault: false, replaceWith: '	' },
	//previewParserPath: 	'', // path to your BBCode parser
	markupSet:  [
		{ name: 'Bold', text: '<i class="fa fa-bold"></i>', className: 'btn btn-link button-bold', key: 'B', openWith: '[b]', closeWith: '[/b]' },
		{ name: 'Italic', text: '<i class="fa fa-italic"></i>', className: 'btn btn-link button-italic', key: 'I', openWith: '[i]', closeWith: '[/i]' },
//		{ name: 'Underline', className: 'btn btn-small button-underline', key: 'U', openWith: '[u]', closeWith: '[/u]' },
//		{ name: 'Size', text: '<i class="fa fa-text-height"></i>', className: 'btn btn-link button-size', key: 'S', openWith: '[size=[![Text size]!]]', closeWith: '[/size]',
//			dropMenu : [
//				{ name: 'Small', text: '<i class="fa fa-zoom-in"></i>', className: 'btn btn-linkbutton-small', openWith: '[size=50]', closeWith: '[/size]' },
//				{ name: 'Normal', text: '<i class="fa fa-font"></i>', className: 'btn btn-linkbutton-normal', openWith: '[size=100]', closeWith: '[/size]' },
//				{ name: 'Big', text: '<i class="fa fa-zoom-out"></i>', className: 'btn btn-linkbutton-big', openWith: '[size=200]', closeWith: '[/size]' }
//			]},
//		{ name: 'Small', text: '<i class="fa fa-minus"></i>', className: 'btn btn-link button-small', openWith: '[size=50]', closeWith: '[/size]' },
//		{ name: 'Big', text: '<i class="fa fa-plus"></i>', className: 'btn btn-link button-big', openWith: '[size=200]', closeWith: '[/size]' },
//		{ separator: '&bull;' },
		{ name: 'Picture', text: '<i class="fa fa-picture-o"></i>', className: 'btn btn-link button-picture', key: 'P', replaceWith: '[img][![URL]!][/img]' },
		{ name: 'Link', text: '<i class="fa fa-link"></i>', className: 'btn btn-link button-link', key: 'L', openWith: '[url=[![URL]!]]', closeWith: '[/url]', placeHolder: 'http://' },
//		{ separator: '&bull;' },
		{ name: 'Align left', text: '<i class="fa fa-align-left"></i>', className: 'btn btn-link button-align-left', openWith: '[left]', closeWith: '[/left]' },
		{ name: 'Align center', text: '<i class="fa fa-align-center"></i>', className: 'btn btn-link button-align-center', openWith: '[center]', closeWith: '[/center]' },
		{ name: 'Align right', text: '<i class="fa fa-align-right"></i>', className: 'btn btn-link button-align-right', openWith: '[right]', closeWith: '[/right]' },
		{ name: 'Bulleted list', text: '<i class="fa fa-list-ul"></i>', className: 'btn btn-link button-list-bullets', openWith: '[list]\n[*]', closeWith: '\n[/list]' },
		{ name: 'Numbered list', text: '<i class="fa fa-list-ol"></i>', className: 'btn btn-link button-list-numbers', openWith: '[list=[![Starting number]!]]\n', closeWith: '\n[/list]' },
//		{ name: 'List item',     text: '<i class="fa fa-bold"></i>', className: 'btn btn-link button-list-item',    openWith: '[*] ' },
//		{ separator: '&bull;' },
//		{ name: 'Quote', text: '<i class="fa fa-leaf"></i>', className: 'btn btn-link button-quote', openWith: '[quote]', closeWith: '[/quote]' },
//		{ name: 'Code',  className: 'btn btn-link button-code',  openWith: '[code]', closeWith: '[/code]' },
//		{ separator: '&bull;' },
//		{ name: 'Remove BBCode', text: '<i class="fa fa-trash fa fa-white"></i>', className: 'btn btn-link button-clear', replaceWith: function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, ''); } }
//		{ name: 'Preview', className: "preview", call: 'preview' }
		{ name: 'Smileys', text: '<i class="fa fa-smile-o"></i>', className: 'btn btn-link button-list-numbers', beforeInsert: function(cb) {
			var $smileys = $(cb.textarea).closest('form').find('.smileys');

			if ($smileys.length) {
				$smileys.find('.lazy').lazyload({
					event:          'lazy',
					skip_invisible: false
				});

				$smileys
					.on('shown.bs.collapse', function() {
						$smileys.find('.lazy').trigger('lazy');
					})
					.collapse('toggle');
			}
		} }
	]
};
