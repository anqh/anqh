bbCodeSettings = {
	nameSpace: 'bbcode',
	onTab: { keepDefault: false, replaceWith: '	' },
	//previewParserPath: 	'', // path to your BBCode parser
	markupSet:  [
		{ name: 'Bold', text: '<i class="icon-bold icon-white"></i>', className: 'btn btn-link button-bold', key: 'B', openWith: '[b]', closeWith: '[/b]' },
		{ name: 'Italic', text: '<i class="icon-italic icon-white"></i>', className: 'btn btn-link button-italic', key: 'I', openWith: '[i]', closeWith: '[/i]' },
//		{ name: 'Underline', className: 'btn btn-small button-underline', key: 'U', openWith: '[u]', closeWith: '[/u]' },
//		{ name: 'Size', text: '<i class="icon-text-height"></i>', className: 'btn btn-link button-size', key: 'S', openWith: '[size=[![Text size]!]]', closeWith: '[/size]',
//			dropMenu : [
//				{ name: 'Small', text: '<i class="icon-zoom-in"></i>', className: 'btn btn-linkbutton-small', openWith: '[size=50]', closeWith: '[/size]' },
//				{ name: 'Normal', text: '<i class="icon-font"></i>', className: 'btn btn-linkbutton-normal', openWith: '[size=100]', closeWith: '[/size]' },
//				{ name: 'Big', text: '<i class="icon-zoom-out"></i>', className: 'btn btn-linkbutton-big', openWith: '[size=200]', closeWith: '[/size]' }
//			]},
		{ name: 'Small', text: '<i class="icon-minus icon-white"></i>', className: 'btn btn-link button-small', openWith: '[size=50]', closeWith: '[/size]' },
		{ name: 'Big', text: '<i class="icon-plus icon-white"></i>', className: 'btn btn-link button-big', openWith: '[size=200]', closeWith: '[/size]' },
//		{ separator: '&bull;' },
		{ name: 'Picture', text: '<i class="icon-picture icon-white"></i>', className: 'btn btn-link button-picture', key: 'P', replaceWith: '[img][![URL]!][/img]' },
		{ name: 'Link', text: '<i class="icon-bookmark icon-white"></i>', className: 'btn btn-link button-link', key: 'L', openWith: '[url=[![URL]!]]', closeWith: '[/url]', placeHolder: 'http://' },
//		{ separator: '&bull;' },
		{ name: 'Align left', text: '<i class="icon-align-left icon-white"></i>', className: 'btn btn-link button-align-left', openWith: '[left]', closeWith: '[/left]' },
		{ name: 'Align center', text: '<i class="icon-align-center icon-white"></i>', className: 'btn btn-link button-align-center', openWith: '[center]', closeWith: '[/center]' },
		{ name: 'Align right', text: '<i class="icon-align-right icon-white"></i>', className: 'btn btn-link button-align-right', openWith: '[right]', closeWith: '[/right]' },
		{ name: 'Bulleted list', text: '<i class="icon-list icon-white"></i>', className: 'btn btn-link button-list-bullets', openWith: '[list]\n[*]', closeWith: '\n[/list]' },
//		{ name: 'Numbered list', text: '<i class="icon-bold"></i>', className: 'btn btn-link button-list-numbers', openWith: '[list=[![Starting number]!]]\n', closeWith: '\n[/list]' },
//		{ name: 'List item',     text: '<i class="icon-bold"></i>', className: 'btn btn-link button-list-item',    openWith: '[*] ' },
//		{ separator: '&bull;' },
//		{ name: 'Quote', text: '<i class="icon-leaf"></i>', className: 'btn btn-link button-quote', openWith: '[quote]', closeWith: '[/quote]' },
//		{ name: 'Code',  className: 'btn btn-link button-code',  openWith: '[code]', closeWith: '[/code]' },
//		{ separator: '&bull;' },
		{ name: 'Remove BBCode', text: '<i class="icon-trash icon-white"></i>', className: 'btn btn-link button-clear', replaceWith: function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, ''); } }
		//{ name: 'Preview', className: "preview", call: 'preview' }
	]
};
