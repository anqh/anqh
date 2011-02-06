<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Comment model
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_Model_Comment extends Jelly_Model implements Permission_Interface {

	/**
	 * @var  array
	 */
	public static $editable_fields = array('comment', 'private');


	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta
			->sorting(array('id' => 'DESC'))
			->fields(array(
				'id' => new Field_Primary,
				'comment' => new Field_String(array(
					'rules' => array(
						'max_length' => array(300),
						'not_empty'  => null,
					),
				)),
				'private' => new Field_Boolean,
				'author' => new Field_BelongsTo(array(
					'column'  => 'author_id',
					'foreign' => 'user',
					'rules' => array(
						'not_empty' => null,
					),
				)),
				'user' => new Field_BelongsTo,
				'created' => new Field_Timestamp(array(
					'auto_now_create' => true,
				)),
			));
	}


	/**
	 * Check permission
	 *
	 * @param   string      $permission
	 * @param   Model_User  $user
	 * @return  boolean
	 */
	public function has_permission($permission, $user) {
		switch ($permission) {
			case self::PERMISSION_CREATE:
		    return (bool)$user;

			case self::PERMISSION_DELETE:
			case self::PERMISSION_UPDATE:
		    return $user && in_array($user->id, array($this->user->id, $this->author->id));

			case self::PERMISSION_READ:
		    return $user && (!$this->private || in_array($user->id, array($this->user->id, $this->author->id)));

		}

		return false;
	}

}
