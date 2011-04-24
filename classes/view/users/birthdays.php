<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Birthdays view.
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2011 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Users_Birthdays extends View_Section {

	/**
	 * @var  array
	 */
	private $_birthdays = null;

	/**
	 * @var  integer  Number of days
	 */
	public $days = 7;

	/**
	 * @var  integer  Number of birthdays to display
	 */
	public $limit = 10;

	/**
	 * @var  integer  Show birthdays of date
	 */
	public $stamp = null;


	/**
	 * Initialize birthdays.
	 */
	public function __construct() {
		parent::__construct();

		$this->stamp = strtotime('today', time());

		$this->title = __('Birthdays');

		$this->tabs = array(
			array(
				'tab' => HTML::anchor(Route::url('users'), __('Show more')),
			),
		);
	}


	/**
	 * Var method for birthdays.
	 *
	 * @return  array
	 */
	protected function _birthdays() {
		if ($this->_birthdays === null) {
			$today    = strtotime('today');
			$stamp    = strtotime('today', $this->stamp);
			$stamp_to = strtotime('+' . $this->days . ' days', $this->stamp);
			$friends  = self::$_user ? self::$_user->find_friends() : null;

			// Load birthdays
			$this->_birthdays = array();
			while ($stamp < $stamp_to) {

				// Load all birthdays from date
				$birthdays         = Model_User::find_by_birthday($stamp);
				$today_count       = $stamp == $today ? count($birthdays) : 0;
				$birthdays_friends = $friends ? array_filter(Arr::get_once($birthdays, $friends)) : array();
				$friend_count      = count($birthdays_friends);

				// Show only some birthdays
				if ($stamp != $today) {

					// Include only friends if not today
					$birthdays = $birthdays_friends;

				} else {

					// Fill with random if less friends than limit
					$birthdays_random = $this->limit ? array() : $birthdays;
					if ($friend_count < $this->limit) {
						if ($limit = min(count($birthdays), $this->limit - $friend_count)) {
							$birthdays_random = Arr::extract($birthdays, array_rand($birthdays, $limit));
						}
					}

					// Friends first
					$birthdays = $birthdays_friends + $birthdays_random;

				}

				// Build day's birthdays
				if ($birthdays) {
					$year  = date('Y', $stamp);
					$users = array();
					foreach ($birthdays as $user_id => $dob) {
						$users[] = array(
							'user' => HTML::user($user_id),
							'age'  => $year - date('Y', strtotime($dob)),
						);
					}

					// Build date
					$span = $stamp - $today;
					if ($span == 0) {
						$date = __('Today');
					} else if ($span == Date::DAY) {
						$date = __('Tomorrow');
					} else if ($span < Date::DAY * 7) {
						$date = date('l', $stamp);
					} else {
						$date = Date::format(Date::DM_SHORT, $stamp);
					}

					// Build array
					$_birthdays = array(
						'date'  => $date,
						'users' => $users,
					);
					if ($today_count) {
						$_birthdays['link'] = ($this->limit ? HTML::separator() . HTML::anchor(Route::url('users'), __('Show all')) : '') . ' (' . $today_count . ')';
					}

					$this->_birthdays[] = $_birthdays;
				}

				$stamp += Date::DAY;
			}

		}

		return $this->_birthdays;
	}


	/**
	 * Render content.
	 *
	 * @return  string
	 */
	public function content() {
		if ($birthdays = $this->_birthdays()) {
			ob_start();

?>

<dl>
	<?php foreach ($birthdays as $birthday) { ?>
	<dt><?php echo Arr::get($birthday, 'date') ?> <?php echo Arr::get($birthday, 'link') ?></dt>
	<dd>
		<ul>
			<?php foreach ($birthday['users'] as $user) { ?>
			<li><?php echo __($user['age'] == 1 ? ':age year' : ':age years', array(':age' => $user['age'])) ?> <?php echo $user['user'] ?></li>
			<?php } ?>
		</ul>
	</dd>
	<?php } ?>
</dl>

<?php

			return ob_get_clean();
		}

		return '';
	}

}
