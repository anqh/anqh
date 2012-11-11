<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Music controller.
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Music extends Controller_Page {

	/**
	 * Action: add new track
	 */
	public function action_add() {
		return $this->_edit_track();
	}


	/**
	 * Action: delete track
	 */
	public function action_delete() {
		$this->history = false;

		// Load track
		$track_id = (int)$this->request->param('id');
		$track    = Model_Music_Track::factory($track_id);
		if (!$track->loaded()) {
			throw new Model_Exception($track, $track_id);
		}

		Permission::required($track, Model_Music_Track::PERMISSION_DELETE, self::$user);

		if (!Security::csrf_valid()) {
			$this->request->redirect(Route::model($track));
		}

		$track->delete();

		$this->request->redirect(Route::url('charts'));
	}


	/**
	 * Action: edit track
	 */
	public function action_edit() {
		$this->_edit_track((int)$this->request->param('id'));
	}


	/**
	 * Action: listen track
	 */
	public function action_listen() {
		$track_id = (int)$this->request->param('id');

		// Load track
		$track = Model_Music_Track::factory($track_id);
		if (!$track->loaded()) {
			throw new Model_Exception($track, $track_id);
		}
		Permission::required($track, Model_Music_Track::PERMISSION_READ, self::$user);

		// Update listen count
		$track->listen(self::$user, Request::$client_ip);

		$this->request->redirect($track->url);
	}


	/**
	 * Action: music track
	 */
	public function action_track() {
		$track_id = (int)$this->request->param('id');

		// Load track
		$track = Model_Music_Track::factory($track_id);
		if (!$track->loaded()) {
			throw new Model_Exception($track, $track_id);
		}
		Permission::required($track, Model_Music_Track::PERMISSION_READ, self::$user);


		// Build page
		$this->view           = new View_Page($track->name);
		$this->view->subtitle = __('By :user :ago', array(
			':user'  => HTML::user($track->author()),
			':ago'   => HTML::time(Date::fuzzy_span($track->created), $track->created)
		));

		// Set actions
		$this->page_actions[] = array(
			'link'   => Route::model($track, 'listen'),
			'text'   => '<i class="icon-play icon-white"></i> ' . __('Listen'),
			'class'  => 'btn btn-primary',
			'target' => '_blank',
			'rel'    => 'nofollow',
		);
		if ($track->forum_topic_id) {
			$this->page_actions[] = array(
				'link'  => Route::url('forum_topic', array('id' => $track->forum_topic_id)),
				'text'  => '<i class="icon-comment icon-white"></i> ' . __('Forum') . ' &raquo;',
			);
		}
		if (Permission::has($track, Model_Music_Track::PERMISSION_UPDATE, self::$user)) {
			$this->page_actions[] = array(
				'link'  => Route::model($track, 'edit'),
				'text'  => '<i class="icon-edit icon-white"></i> ' . __('Edit'),
			);
		}

		// Share
		if (Kohana::$config->load('site.facebook')) {
			Anqh::open_graph('type', $track->type == Model_Music_Track::TYPE_MIX ? 'album' : 'song');
			Anqh::open_graph('title', $track->name);
			Anqh::open_graph('url', URL::site(Route::model($track), true));
			Anqh::open_graph('description', $track->description);
			if (Valid::url($track->cover)) {
				Anqh::open_graph('image', $track->cover);
			}
		}
		Anqh::share(true);

		// Content
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_track_main($track));
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_share());
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_track_info($track));

	}


	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->view = new View_Page(__('Charts'));

		// Set actions
		if (Permission::has(new Model_Music_Track, Model_Music_Track::PERMISSION_CREATE, self::$user)) {
			$this->page_actions[] = array(
				'link'  => Route::url('music_add', array('music' => 'mixtape')),
				'text'  => '<i class="icon-plus-sign icon-white"></i> ' . __('Add new mixtape'),
				'class' => 'btn btn-primary'
			);
			$this->page_actions[] = array(
				'link'  => Route::url('music_add', array('music' => 'track')),
				'text'  => '<i class="icon-plus-sign icon-white"></i> ' . __('Add new track'),
				'class' => 'btn btn-primary'
			);
		}

		$this->view->add(View_Page::COLUMN_MAIN, '<div class="row">');

		// Top charts
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_charts(
			Model_Music_Track::factory()->find_top_weekly(Model_Music_Track::TYPE_MIX, 10),
			__('Top :top Mixtapes', array(':top' => 10))
		));

		$this->view->add(View_Page::COLUMN_MAIN, $this->section_charts(
			Model_Music_Track::factory()->find_top_weekly(Model_Music_Track::TYPE_TRACK, 10),
			__('Top :top Tracks', array(':top' => 10))
		));

		$this->view->add(View_Page::COLUMN_MAIN, '</div>');


		// New
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_list(
			Model_Music_Track::factory()->find_new(Model_Music_Track::TYPE_MIX, 10),
			__('New mixtapes')
		));

		$this->view->add(View_Page::COLUMN_SIDE, $this->section_list(
			Model_Music_Track::factory()->find_new(Model_Music_Track::TYPE_TRACK, 10),
			__('New tracks')
		));
	}


	/**
	 * Edit track.
	 *
	 * @param   integer  $track_id
	 *
	 * @throws  Model_Exception
	 */
	protected function _edit_track($track_id = null) {
		$this->history = false;

		if ($track_id) {

			// Editing old
			$track = new Model_Music_Track($track_id);
			if (!$track->loaded()) {
				throw new Model_Exception($track, $track_id);
			}
			Permission::required($track, Model_Music_Track::PERMISSION_UPDATE, self::$user);

			$cancel = Route::model($track);

			// Set actions
			if (Permission::has($track, Model_Music_Track::PERMISSION_DELETE, self::$user)) {
				$this->page_actions[] = array(
					'link' => Route::model($track, 'delete') . '?token=' . Security::csrf(),
					'text' => '<i class="icon-trash icon-white"></i> ' . __('Delete'),
					'class' => 'btn-danger music-delete'
				);
			}

			$this->view = new View_Page(HTML::chars($track->name));

		} else {

			// Creating new
			$track = new Model_Music_Track();
			Permission::required($track, Model_Music_Track::PERMISSION_CREATE, self::$user);

			$cancel   = Request::back(Route::url('charts'), true);
			$newsfeed = true;

			$this->view = new View_Page($this->request->param('music') === 'mixtape' ? __('New mixtape') : __('New track'));
			$track->author_id = self::$user->id;
			$track->type      = $this->request->param('music') === 'mixtape' ? Model_Music_Track::TYPE_MIX : Model_Music_Track::TYPE_TRACK;
			$track->created   = time();

		}

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			try {
				$track->set_fields(Arr::intersect($_POST, Model_Music_Track::$editable_fields));
				$track->save();

				// Set tags
				$track->set_tags(Arr::get($_POST, 'tag'));

				// Newsfeed
				if (isset($newsfeed) && $newsfeed) {
					NewsfeedItem_Music::track(self::$user, $track);

					$track->add_forum_topic();
				}

				$this->request->redirect(Route::model($track));
			} catch (Validation_Exception $e) {
				$errors = $e->array->errors('validation');
			}
		}

		// Form
		$section = $this->section_track_edit($track);
		$section->cancel = $cancel;
		$section->errors = $errors;
		$this->view->add(View_Page::COLUMN_TOP, $section);
	}


	/**
	 * Get charts view.
	 *
	 * @param   array   $tracks
	 * @param   string  $title
	 * @return  View_Music_Charts
	 */
	public function section_charts($tracks, $title) {
		$view = new View_Music_Charts($tracks);
		$view->title = $title;
		$view->class = 'span4';

		return $view;
	}


	/**
	 * Get list view.
	 */
	public function section_list($tracks, $title) {
		$view = new View_Music_List($tracks);
		$view->title = $title;

		return $view;
	}


	/**
	 * Get side info view.
	 *
	 * @param   Model_Music_Track  $track
	 * @return  View_Music_Info
	 */
	public function section_track_info(Model_Music_Track $track) {
		return new View_Music_Info($track);
	}


	/**
	 * Get main info view.
	 *
	 * @param   Model_Music_Track  $track
	 * @return  View_Music_Main
	 */
	public function section_track_main(Model_Music_Track $track) {
		return new View_Music_Main($track);
	}


	/**
	 * Get track edit view.
	 *
	 * @param   Model_Music_Track  $track
	 * @return  View_Music_Edit
	 */
	public function section_track_edit(Model_Music_Track $track) {
		return new View_Music_Edit($track);
	}

}
