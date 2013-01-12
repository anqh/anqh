<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Generic_Rating
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Generic_Rating extends View_Base {

	/**
	 * @var  integer  Rate count
	 */
	public $count;

	/**
	 * @var  boolean
	 */
	public $rate;

	/**
	 * @var  boolean
	 */
	public $score;

	/**
	 * @var  integer
	 */
	public $total;


	/**
	 * Create new view.
	 *
	 * @param  integer  $total  Total rate score
	 * @param  integer  $count  Total rate count
	 * @param  boolean  $score  Show score
	 */
	public function __construct($total, $count, $score = false) {
		parent::__construct();

		$this->total = $total;
		$this->count = $count;
		$this->score = $score;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function render() {
		ob_start();

		$rating = $this->count ? $this->total / $this->count : 0;
?>

<span class="rating">
	<?php for ($r = 1; $r <= 5; $r++): ?>
	<i class="<?= $rating >= $r - .5 ? 'icon-star' : 'icon-star-empty' ?> icon-white" title="<?= $r ?>"></i>
	<?php endfor; ?>
	<?php if ($this->score): ?>
	<var title="<?php echo __($this->count == 1 ? ':rates rating' : ':rates ratings', array(':rates' => $this->count)) ?>"><?php echo Num::format($rating, 2) ?></var>
	<?php endif; ?>
</span>

<?php

		return ob_get_clean();
	}

}
