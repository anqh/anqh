bbCodeSettings = {
	nameSpace: 'bbcode',
	onTab: { keepDefault:false, replaceWith: '	' },
	//previewParserPath: 	'', // path to your BBCode parser
	markupSet:  [
		{ name: 'Bold', className: 'button-bold', key: 'B', openWith: '[b]', closeWith: '[/b]' },
		{ name: 'Italic', className: 'button-italic', key: 'I', openWith: '[i]', closeWith: '[/i]' },
		{ name: 'Underline', className: 'button-underline', key: 'U', openWith: '[u]', closeWith: '[/u]' },
		{ name: 'Size', className: 'button-size', key: 'S', openWith: '[size=[![Text size]!]]', closeWith: '[/size]',
			dropMenu : [
				{ name: 'Small', className: 'button-small', openWith: '[size=50]', closeWith: '[/size]' },
				{ name: 'Normal', className: 'button-normal', openWith: '[size=100]', closeWith: '[/size]' },
				{ name: 'Big', className: 'button-big', openWith: '[size=200]', closeWith: '[/size]' }
			]},
		{ separator: '&bull;' },
		{ name: 'Picture', className: 'button-picture', key: 'P', replaceWith: '[img][![URL]!][/img]' },
		{ name: 'Link', className: 'button-link', key: 'L', openWith: '[url=[![URL]!]]', closeWith: '[/url]', placeHolder: 'http://' },
		{ separator: '&bull;' },
		{ name: 'Bulleted list', className: 'button-list-bullets', openWith: '[list]\n', closeWith: '\n[/list]' },
		{ name: 'Numbered list', className: 'button-list-numbers', openWith: '[list=[![Starting number]!]]\n', closeWith: '\n[/list]' },
		{ name: 'List item', className: 'button-list-item', openWith: '[*] ' },
		{ separator: '&bull;' },
		{ name: 'Quote', className: 'button-quote', openWith: '[quote]', closeWith: '[/quote]' },
		{ name: 'Code', className: 'button-code', openWith: '[code]', closeWith: '[/code]' },
		{ separator: '&bull;' },
		{ name: 'Clean', className: 'button-clear', replaceWith: function(markitup) { return markitup.selection.replace(/\[(.*?)\]/g, ''); } }
		//{ name: 'Preview', className: "preview", call: 'preview' }
	]
}
