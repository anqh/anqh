<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Alert message
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Alert extends View_Base {

	/** Default alert class */
	const ALERT   = 'alert-warning';

	/** Error/danger class */
	const ERROR   = 'alert-danger';

	/** Success class */
	const SUCCESS = 'alert-success';

	/** Information class*/
	const INFO    = 'alert-info';

	/**
	 * @var  string  Alert content
	 */
	public $content;

	/**
	 * @var  string  Alert header
	 */
	public $header;


	/**
	 * Create new alert.
	 *
	 * @param  string  $content
	 * @param  string  $header
	 * @param  string  $class
	 */
	public function __construct($content, $header = null, $class = self::ALERT) {
		$this->content = $content;
		$this->header  = $header;
		$this->class   = $class;
	}


	/**
	 * Generate header based on alert type.
	 */
	public function generate_header() {
		$headers = array();
		switch ($this->class) {
			case self::ALERT:
			case self::ERROR:
				$headers = array(
					__('Frak!'),
					__('Hold the press!'),
					__('Noooooo!'),
					__('Ouch!'),
					__('Uh oh!'),
					__('Umm..'),
					__('Whoops!'),
				);
				break;

			case self::SUCCESS:
				$headers = array(
					__('Aww yiss!'),
					__('Great success!'),
					__('Mission accomplished!'),
					__('Most excellent!'),
					__('We dunit!'),
					__('Wohoo!'),
					__('Yay!'),
				);
				break;

			case self::INFO:
				$headers = array(
					__('Hear ye, hear ye!'),
					__('Hey!'),
					__('Oi!'),
					__('Pssst!'),
					__('This just in!'),
				);
				break;

		}

		return Arr::rand($headers);
	}


	/**
	 * Render alert.
	 *
	 * @return  string
	 */
	public function render() {
		ob_start();

		// Section attributes
		$attributes = array(
			'class' => 'alert alert-block ' . $this->class,
		);

		if ($this->header === true):
			$this->header = self::generate_header();
		endif;

?>

<div <?= HTML::attributes($attributes) ?>>
	<?php if ($this->header): ?><strong><?= HTML::chars($this->header) ?></strong><?php endif; ?>

	<?= $this->content ?>
</div>

<?php

		return ob_get_clean();
	}

}
