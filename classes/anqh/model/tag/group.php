<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Tag Group model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Tag_Group extends AutoModeler_ORM {

	protected $_table_name = 'tag_groups';

	protected $_data = array(
		'id'          => null,
		'name'        => null,
		'description' => null,
		'author_id'   => null,
		'created'     => null,
	);

	protected $_rules = array(
		'name' => array('not_empty', 'AutoModeler::unique' => array(':model', ':value', ':field')),
	);

	/**
	 * @var  Database_Result  Tags of current group
	 */
	protected $_tags;

	/**
	 * Load tag group
	 *
	 * @param  integer|string  $id
	 */
	public function __construct($id = null) {
		parent::__construct();

		if ($id !== null) {
			$this->load(DB::select()->where(is_numeric($id) ? 'id' : 'name', '=', $id));
		}
	}


	/**
	 * Get current group's tags.
	 *
	 * @return  Database_Result
	 */
	public function tags() {
		return $this->find_related('tags');
	}

}
