<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Tag model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Tag extends AutoModeler_ORM {

	protected $_table_name = 'tags';

	protected $_data = array(
		'id'           => null,
		'tag_group_id' => null,
		'name'         => null,
		'description'  => null,
		'author_id'    => null,
		'created'      => null,
	);

	protected $_rules = array(
		'tag_group_id' => array('not_empty', 'digit'),
		'name'         => array('not_empty'),
	);


	/**
	 * Get tag group.
	 *
	 * @return  Model_Tag_Group
	 */
	public function group() {
		return $this->find_parent('tag_group');
	}

}
