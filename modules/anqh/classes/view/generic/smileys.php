<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Smiley selector.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_Smileys extends View_Base {

	/**
	 * @var  string
	 */
	public $dom_target;

	/**
	 * Create new view.
	 *
	 * @param  Model_
	 */
	public function __construct($dom_target = null) {
		parent::__construct();

		$this->dom_target = $dom_target;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function render() {
		if ($config = Kohana::$config->load('site.smiley')) {
			$url         = URL::base() . $config['dir'] . '/';
			$placeholder = URL::site('/static/img/ajax.gif');

			ob_start();

?>

<div class="smileys collapse" data-target="<?= $this->dom_target ?>">

<?php

			foreach ($config['smileys'] as $name => $smiley) {
				echo HTML::image($placeholder, array(
						'class'         => 'smiley lazy',
						'data-original' => $url . $smiley['src'],
						'alt'           => $name,
						'title'         => $name
					)
				);
			}

?>

</div>

<script>
	head.ready('vendor', function _smileys() {
		var $smileys = $('.smileys')
		  , target   = $smileys.attr('data-target');

		$.fn.smiley = function(smiley) {
			var $input = $(this)
			  , input  = $(this).get(0);

			if (document.selection) {

				// IE
				$input.focus();
				var range = document.selection.createRange();
				range.text = smiley;
				$input.focus();

			} else if (input.selectionStart || input.selectionStart == '0') {

			 // Others
				var from = input.selectionStart
				  , to   = input.selectionEnd
				  , text = input.value;
				input.value = text.substring(0, from) + smiley + text.substring(to, text.length);
				$input.focus();
				input.selectionStart = input.selectionEnd = from + smiley.length;

			} else {

				// Fallback
				input.value += smiley;
				$input.focus();

			}
		};

		$smileys.on('click', 'img', function _smiley() {
			$(target).smiley($(this).attr('title'));
		});
	});
</script>

<?php

			return ob_get_clean();
		} else {
			return __('No smilies available :(');
		}
	}

}
