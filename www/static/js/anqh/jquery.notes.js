/**
 * Image notes.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
(function ($) {

	$.fn.notes = function(n) {
		var notes       = n || {}
		  , $image      = $(this)
		  , imageOffset = $image.position()
		  , imageWidth  = $image.width()
		  , imageHeight = $image.height();

		$(notes).each(function() {
			add(this);
		});

		$(window).resize(function() {
			$('.note').remove();

			imageOffset = $image.position();
	    imageWidth  = $image.width();
	    imageHeight = $image.height();
			$(notes).each(function() {
				add(this);
			});
		});


		function add(note_data) {
			var scaleX = imageWidth / note_data.imageWidth || 1
			  , scaleY = imageHeight / note_data.imageHeight || 1
			  , noteX  = parseInt(imageOffset.left) + parseInt(note_data.x)
				, noteY  = parseInt(imageOffset.top) + parseInt(note_data.y);

			var $note = $('<div class="note" id="note-' + note_data.id + '" />').css({
				left: noteX * scaleX + 'px',
				top:  noteY * scaleY + 'px'
			});
			var $area = $('<div class="notea" />').css({
				width:  note_data.width * scaleX + 'px',
				height: note_data.height * scaleY + 'px'
			});
			var $text = $('<div class="notet label label-default" />')
				.append(note_data.url ? $('<a href="' + note_data.url + '" class="hoverable">' + note_data.name + '</a>') : note_data.name);

			$note
				.append($area)
				.append($text);
			$image.after($note);

			$('[data-note-id=' + note_data.id + ']') && $('[data-note-id=' + note_data.id + ']').hover(
				function _show() { $area.css({ visibility: 'visible' }); },
				function _hide() { $area.css({ visibility: 'hidden' }); }
			);
		}

	};

})(jQuery);
