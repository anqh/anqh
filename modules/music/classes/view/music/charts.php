<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Music_Charts
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class View_Music_Charts extends View_Section {

	/**
	 * @var  array
	 */
	public $tracks;


	/**
	 * Create new view.
	 *
	 * @param  array  $tracks
	 */
	public function __construct($tracks = null) {
		parent::__construct();

		$this->tracks = $tracks;
	}


	/**
	 * Build charts list.
	 *
	 * @return  Model_Music_Track[]
	 */
	protected function _charts() {
		$tracks = array();

		foreach ($this->tracks['this'] as $rank => $track_id) {
			$track = Model_Music_Track::factory($track_id);
			if (!$track->loaded()) {
				continue;
			}

			$tracks[] = array(
				'rank'   => $rank,
				'last'   => array_search($track_id, $this->tracks['last']),
				'track'  => $track
			);
		}

		return $tracks;
	}


	/**
	 * Render view.
	 *
	 * @return  string
	 */
	public function content() {
		$tracks = $this->_charts();

		ob_start();

		foreach ($tracks as $track):
			$view = new View_Music_Track($track['track']);
			$view->rank      = $track['rank'] + 1;
			$view->rank_last = $track['last'] === false ? false : $track['last'] + 1;
			echo $view;
		endforeach;

		return ob_get_clean();
	}


	/**
	 * Get rank change.
	 *
	 * @param   array  $track
	 * @return  string
	 */
	protected function _change($track) {
		if ($track['last'] === false) {

			// No previous rank
			return '<small class="new">' . __('New') . '</small>';

		} else {

			// Rank changed
			$change = $track['last'] - $track['rank'];

			if ($change < 0) {
				return '<sub title="' . ($track['last'] + 1) . '">&#9660;' . abs($change) . '</sub>';
			} else if ($change > 0) {
				return '<sup title="' . ($track['last'] + 1) . '">&#9650;' . $change . '</sup>';
			} else  {
				return '<small class="unchanged">-</small>';
			}
		}
	}

}
