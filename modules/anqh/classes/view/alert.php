<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Alert message
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Alert extends View_Base {

	/** Default alert class */
	const ALERT   = '';

	/** Error/danger class */
	const ERROR   = 'alert-error';

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

?>

<div <?php echo HTML::attributes($attributes) ?>>
	<?php if ($this->header): ?><h4 class="alert-heading"><?php echo HTML::chars($this->header) ?></h4><?php endif; ?>

	<?php echo $this->content ?>
</div>

<?php

		return ob_get_clean();
	}

}
