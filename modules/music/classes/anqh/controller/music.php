<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Music controller.
 *
 * @package    Music
 * @author     Antti Qvickström
 * @copyright  (c) 2012-2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Music extends Controller_Page {
	public $page_id = 'charts';


	/**
	 * Action: add new track
	 */
	public function action_add() {
		return $this->_edit_track();
	}


	/**
	 * Action: browse tracks
	 */
	public function action_browse() {
		$type  = $this->request->param('music') == 'mixtapes' ? Model_Music_Track::TYPE_MIX : Model_Music_Track::TYPE_TRACK;
		$genre = $this->request->param('genre');

		// Load requested music
		$limit  = 25;
		$music  = Model_Music_Track::factory();
		$count  = $music->count_by_type($type, $genre);

		// Build page
		$this->view = View_Page::factory($type == Model_Music_Track::TYPE_MIX ? __('Mixtapes') : __('Tracks'));

		// Set actions
		$this->_set_page_actions();
		$this->view->tab = $this->request->param('music');

		// Filters
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_filters($this->request->param('music'), $genre));

		// Pagination
		$this->view->add(View_Page::COLUMN_MAIN, $pagination = $this->section_pagination($limit, $count));
		$this->view->subtitle = __($pagination->total_pages == 1 ? ':pages page' : ':pages pages', array(':pages' => Num::format($pagination->total_pages, 0)));

		// Browse
		$tracks = $music->find_by_type($type, $genre, $limit, $pagination->offset);
		$this->view->add(View_Page::COLUMN_MAIN, $this->section_browse($tracks));

		// Pagination
		$this->view->add(View_Page::COLUMN_MAIN, $pagination);

		// New
		$this->view->add(View_Page::COLUMN_SIDE, $this->section_list(
			$music->find_new(Model_Music_Track::TYPE_MIX, 10),
			__('New mixtapes')
		));

		$this->view->add(View_Page::COLUMN_SIDE, $this->section_list(
			$music->find_new(Model_Music_Track::TYPE_TRACK, 10),
			__('New tracks')
		));
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
		$this->view->tab      = 'music';
		$this->view->subtitle = __('By :user :ago', array(
			':user'  => HTML::user($track->author()),
			':ago'   => HTML::time(Date::fuzzy_span($track->created), $track->created)
		));

		// Set actions
		$this->_set_page_actions(false);

		$this->view->actions[] = array(
			'link'   => Route::model($track, 'listen'),
			'text'   => '<i class="icon-play icon-white"></i> ' . __('Listen'),
			'class'  => 'btn btn-primary',
			'target' => '_blank',
			'rel'    => 'nofollow',
		);
		$this->view->tabs['music'] = array(
			'link'  => Route::model($track),
			'text'  => '<i class="icon-music icon-white"></i> ' . ($track->type == Model_Music_Track::TYPE_MIX ? __('Mixtape') : __('Track')),
		);
		if ($track->forum_topic_id) {
			$this->view->tabs[] = array(
				'link'  => Route::url('forum_topic', array('id' => $track->forum_topic_id)),
				'text'  => '<i class="icon-comment icon-white"></i> ' . __('Forum') . ' &raquo;',
			);
		}
		if (Permission::has($track, Model_Music_Track::PERMISSION_UPDATE, self::$user)) {
			$this->view->actions[] = array(
				'link'  => Route::model($track, 'edit'),
				'text'  => '<i class="icon-edit icon-white"></i> ' . __('Edit'),
			);
		}

		// Share
		Anqh::page_meta('type', $track->type == Model_Music_Track::TYPE_MIX ? 'album' : 'song');
		Anqh::page_meta('title', $track->name);
		Anqh::page_meta('url', URL::site(Route::model($track), true));
		Anqh::page_meta('description', $track->description);
		if (Valid::url($track->cover)) {
			Anqh::page_meta('image', $track->cover);
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
		$this->view->tab = 'charts';
		$this->_set_page_actions();

		$this->view->add(View_Page::COLUMN_TOP, '<div class="row-fluid">');

		// Top charts
		$this->view->add(View_Page::COLUMN_TOP, $this->section_charts(
			Model_Music_Track::factory()->find_top_weekly(Model_Music_Track::TYPE_MIX, 10),
			__('Top :top Mixtapes', array(':top' => 10))
		));

		$this->view->add(View_Page::COLUMN_TOP, $this->section_charts(
			Model_Music_Track::factory()->find_top_weekly(Model_Music_Track::TYPE_TRACK, 10),
			__('Top :top Tracks', array(':top' => 10))
		));

		$this->view->add(View_Page::COLUMN_TOP, '</div><div class="row-fluid">');


		// New
		$section = $this->section_list(
			Model_Music_Track::factory()->find_new(Model_Music_Track::TYPE_MIX, 10),
			__('New mixtapes')
		);
		$section->class = 'span6';
		$this->view->add(View_Page::COLUMN_TOP, $section);

		$section = $this->section_list(
			Model_Music_Track::factory()->find_new(Model_Music_Track::TYPE_TRACK, 10),
			__('New tracks')
		);
		$section->class = 'span6';
		$this->view->add(View_Page::COLUMN_TOP, $section);

		$this->view->add(View_Page::COLUMN_TOP, '</div>');
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

			$this->view = new View_Page(HTML::chars($track->name));

			// Set actions
			if (Permission::has($track, Model_Music_Track::PERMISSION_DELETE, self::$user)) {
				$this->view->actions[] = array(
					'link' => Route::model($track, 'delete') . '?token=' . Security::csrf(),
					'text' => '<i class="icon-trash icon-white"></i> ' . __('Delete'),
					'class' => 'btn-danger music-delete'
				);
			}

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

					// Create forum topic
					if ($track->add_forum_topic()) {
						self::$user->post_count++;
						self::$user->save();
					}
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
	 * Get browse view.
	 *
	 * @param   Model_Music_Track[]  $tracks
	 * @return  View_Music_Browse
	 */
	public function section_browse($tracks) {
		if (!$tracks) {
			return new View_Alert(__('Listen to the sound of silence.'), __('No music found'), View_Alert::INFO);
		}

		return new View_Music_Browse($tracks);
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
		$view->class = 'span6';

		return $view;
	}


	/**
	 * Get filters.
	 *
	 * @param   string  $music
	 * @param   array   $filter  Active filter
	 * @return  View_Generic_Filters
	 */
	public function section_filters($music, $filter = null) {
		$filters = array(
			'tag' => array(
				'name'    => __('Music'),
				'filters' => $this->_tags()
			),
		);

		$section = new View_Generic_Filters($filters, $filter);
		$section->type     = View_Generic_Filters::TYPE_URL;
		$section->base_url = Route::url('music_browse', array('music' => $music));

		return $section;
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
	 * Get pagination view.
	 *
	 * @param   integer  $limit
	 * @param   integer  $total
	 * @return  View_Generic_Pagination
	 */
	public function section_pagination($limit, $total) {
		return new View_Generic_Pagination(array(
			'items_per_page' => $limit,
			'total_items'    => max(1, $total),
		));
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


	/**
	 * Set common page actions.
	 */
	public function _set_page_actions($actions = true) {

		// Browsing
		$this->view->tabs['charts'] = array(
			'link'  => Route::url('charts'),
			'text'  => '<i class="icon-fire icon-white"></i> ' . __('Charts'),
		);
		$this->view->tabs['mixtapes'] = array(
			'link'  => Route::url('music_browse', array('music' => 'mixtapes')),
			'text'  => '<i class="icon-th-list icon-white"></i> ' . __('Browse mixtapes'),
		);
		$this->view->tabs['tracks'] = array(
			'link'  => Route::url('music_browse', array('music' => 'tracks')),
			'text'  => '<i class="icon-th-list icon-white"></i> ' . __('Browse tracks'),
		);

		// Content creation
		if ($actions && Permission::has(new Model_Music_Track, Model_Music_Track::PERMISSION_CREATE, self::$user)) {
			$this->view->actions[] = array(
				'link'  => Route::url('music_add', array('music' => 'mixtape')),
				'text'  => '<i class="icon-plus-sign icon-white"></i> ' . __('Add new mixtape'),
				'class' => 'btn btn-primary'
			);
			$this->view->actions[] = array(
				'link'  => Route::url('music_add', array('music' => 'track')),
				'text'  => '<i class="icon-plus-sign icon-white"></i> ' . __('Add new track'),
				'class' => 'btn btn-primary'
			);
		}

	}


	/**
	 * Get available tags.
	 *
	 * @return  array
	 */
	public function _tags() {
		$tags      = array();
		$tag_group = new Model_Tag_Group('Music');

		if ($tag_group->loaded() && count($tag_group->tags())) {
			foreach ($tag_group->tags() as $tag) {
				$tags[$tag->id()] = $tag->name();
			}
		}

		return $tags;
	}

}
