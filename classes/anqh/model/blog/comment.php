<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Blog Comment model
 *
 * @package    Blog
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Blog_Comment extends Model_Comment {

	/**
	 * Create new model
	 *
	 * @param  Jelly_Meta  $meta
	 */
	public static function initialize(Jelly_Meta $meta) {
		$meta->fields(array(
			'blog_entry' => new Field_BelongsTo
		));

		parent::initialize($meta);
	}

}
