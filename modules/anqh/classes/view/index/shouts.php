<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Shouts view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011-2014 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Index_Shouts extends View_Section {

	/**
	 * @var  boolean
	 */
	protected $_can_shout = false;

	/**
	 * @var  integer  Visible shouts
	 */
	public $limit = 10;


	/**
	 * Create new shouts view.
	 */
	public function __construct() {
		parent::__construct();

		$this->id    = 'shouts';
		$this->title = HTML::anchor(Route::url('shouts'), __('Shouts'));

		$this->_can_shout = Permission::has(new Model_Shout, Model_Shout::PERMISSION_CREATE, Visitor::$user);
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		$shouts = array();
		foreach (Model_Shout::find_latest($this->limit) as $shout):
			$shouts[] = array(
				'created' => $shout->created,
			  'user_id' => $shout->author_id,
			  'shout'   => $shout->shout
			);
		endforeach;

		if ($shouts):
			ob_start();

?>

<ul class="list-unstyled">

	<?php foreach (array_reverse($shouts) as $shout): ?>
	<li>
		<?= HTML::time(Date::format('HHMM', $shout['created']), array('datetime' => $shout['created'], 'class' => 'muted')) ?>
		<?= HTML::user($shout['user_id']) ?>:
		<?= Text::smileys(Text::auto_link_urls(HTML::chars($shout['shout']))) ?>
	</li>
	<?php endforeach; ?>

</ul>

<?php if ($this->_can_shout): ?>
<form <?= $this->aside ? 'class="ajaxify"' : '' ?> action="<?= Route::url('shouts', array('action' => 'shout')) ?>" method="post">
	<input class="form-control" type="text" name="shout" maxlength="300" placeholder="<?= __('Shout, and ye shall be heard..') ?>" />
	<?= Form::CSRF() ?>
</form>
<?php
			endif;

			return ob_get_clean();
		endif;

		return '';
	}

}
