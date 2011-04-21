<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Image Comment model
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Image_Comment extends Model_Comment implements Permission_Interface {

	protected $_table_name = 'image_comments';

	protected $_data = array(
		'id'        => null,
		'comment'   => null,
		'private'   => 0,
		'author_id' => null,
		'user_id'   => null,
		'created'   => null,
		'image_id'  => null,
	);


	/**
	 * Add new comment
	 *
	 * @param   integer      $author_id
	 * @param   Model_Image  $image
	 * @param   string       $comment
	 * @param   boolean      $private
	 * @return  Model_Image_Comment
	 */
	public function add($author_id, Model_Image $image = null, $comment, $private = false) {
		$this->image_id = $image->id;

		return parent::add($author_id, $image->author_id, $comment, $private);
	}


	/**
	 * Get comment image
	 *
	 * @return  Model_Image
	 */
	public function image() {
		try {
			return $this->image_id ? Model_Image::factory($this->image_id) : null;
		} catch (AutoModeler_Exception $e) {
			return null;
		}
	}

}
