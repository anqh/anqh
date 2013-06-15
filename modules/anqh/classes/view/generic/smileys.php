<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Generic_Smileys
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
	head.ready('bbcode', function _smileys() {
		var $smileys = $('.smileys')
		  , target   = $smileys.attr('data-target');

		$smileys.on('click', 'img', function _smiley() {
			$(target).smiley($(this).attr('title'));
// Does not work with input fields
//			$.markItUp({ target: target, replaceWith: $(this).attr('title') });
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
