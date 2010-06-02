<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forum Topic model builder
 *
 * @package    Forum
 * @author     Antti Qvickström
 * @copyright  (c) 2010 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Model_Builder_Forum_Topic extends Jelly_Builder {

	/**
	 * Find topics with latest posts
	 *
	 * @param   integer  $limit
	 * @param   integer  $page
	 * @return  Jelly_Builder
	 */
	public function latest($limit = 20, $page = 1) {
		return $this->order_by('last_post_id', 'DESC')->offset(($page - 1) * $limit)->limit($limit);
	}

}
