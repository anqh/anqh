<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * NewsfeedItem
 *
 * @abstract
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2010-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
abstract class Anqh_NewsfeedItem implements NewsfeedItem_Interface {

	/**
	 * Add a Newsfeed item.
	 *
	 * @static
	 * @param   Model_User  $user
	 * @param   string      $class  e.g. 'user'
	 * @param   string      $type   e.g. 'login'
	 * @param   array       $data   Data to be user with item
	 * @return  boolean
	 */
	protected static function add(Model_User $user, $class, $type, array $data = null) {
		$item = new Model_NewsfeedItem;

		// Update item if previous added less than time window specified time
		$update = DB::update($item->get_table_name())
			->set(array('stamp' => time()))
			->where('user_id', '=', $user->id)
			->and_where('class', '=', $class)
			->and_where('type', '=', $type)
			->and_where('stamp', '>', strtotime(Newsfeed::UPDATE_WINDOW));
		$data
			? $update->and_where('data', '=', @json_encode($data))
			: $update->and_where('data', 'IS', null);

		// Update any?
		if ($update->execute()) {
			return true;
		}

		// No new enough item found, insert
		$item->set_fields(array(
			'user_id' => $user->id,
			'class'   => $class,
			'type'    => $type,
			'data'    => $data,
			'stamp'   => time(),
		));
		$item->save();

		return $item->loaded();
	}

}
