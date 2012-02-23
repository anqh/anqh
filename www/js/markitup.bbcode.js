bbCodeSettings = {
	nameSpace: 'bbcode btn-group',
	onTab: { keepDefault: false, replaceWith: '	' },
	//previewParserPath: 	'', // path to your BBCode parser
	markupSet:  [
		{ name: 'Bold', text: '<i class="icon-bold"></i>', className: 'btn btn-small button-bold', key: 'B', openWith: '[b]', closeWith: '[/b]' },
		{ name: 'Italic', text: '<i class="icon-italic"></i>', className: 'btn btn-small button-italic', key: 'I', openWith: '[i]', closeWith: '[/i]' },
//		{ name: 'Underline', className: 'btn btn-small button-underline', key: 'U', openWith: '[u]', closeWith: '[/u]' },
//		{ name: 'Size', text: '<i class="icon-text-height"></i>', className: 'btn btn-small button-size', key: 'S', openWith: '[size=[![Text size]!]]', closeWith: '[/size]',
//			dropMenu : [
//				{ name: 'Small', text: '<i class="icon-zoom-in"></i>', className: 'button-small', openWith: '[size=50]', closeWith: '[/size]' },
//				{ name: 'Normal', text: '<i class="icon-font"></i>', className: 'button-normal', openWith: '[size=100]', closeWith: '[/size]' },
//				{ name: 'Big', text: '<i class="icon-zoom-out"></i>', className: 'button-big', openWith: '[size=200]', closeWith: '[/size]' }
//			]},
		{ name: 'Small', text: '<i class="icon-zoom-in"></i>', className: 'btn btn-small button-small', openWith: '[size=50]', closeWith: '[/size]' },
		{ name: 'Big', text: '<i class="icon-zoom-out"></i>', className: 'btn btn-small button-big', openWith: '[size=200]', closeWith: '[/size]' },
//		{ separator: '&bull;' },
		{ name: 'Picture', text: '<i class="icon-picture"></i>', className: 'btn btn-small button-picture', key: 'P', replaceWith: '[img][![URL]!][/img]' },
		{ name: 'Link', text: '<i class="icon-bookmark"></i>', className: 'btn btn-small button-link', key: 'L', openWith: '[url=[![URL]!]]', closeWith: '[/url]', placeHolder: 'http://' },
//		{ separator: '&bull;' },
		{ name: 'Bulleted list', text: '<i class="icon-list"></i>', className: 'btn btn-small button-list-bullets', openWith: '[list]\n[*]', closeWith: '\n[/list]' },
//		{ name: 'Numbered list', text: '<i class="icon-bold"></i>', className: 'btn btn-small button-list-numbers', openWith: '[list=[![Starting number]!]]\n', closeWith: '\n[/list]' },
//		{ name: 'List item',     text: '<i class="icon-bold"></i>', className: 'btn btn-small button-list-item',    openWith: '[*] ' },
//		{ separator: '&bull;' },
//		{ name: 'Quote', text: '<i class="icon-leaf"></i>', className: 'btn btn-small button-quote', openWith: '[quote]', closeWith: '[/quote]' },
//		{ name: 'Code',  className: 'btn btn-small button-code',  openWith: '[code]', closeWith: '[/code]' },
//		{ separator: '&bull;' },
		{ name: 'Clean', text: '<i class="icon-trash"></i>', className: 'btn btn-small button-clear', replaceWith: function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, ''); } }
		//{ name: 'Preview', className: "preview", call: 'preview' }
	]
};
