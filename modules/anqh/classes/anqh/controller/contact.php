<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Anqh Contact controller
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Controller_Contact extends Controller_Page {

	/**
	 * Controller default action
	 */
	public function action_index() {
		$this->view = new View_Page('Contact');

		$section = $this->section_contact();
		if (self::$user) {
			$section->name  = self::$user->username;
			$section->email = self::$user->email;
		}

		// Handle post
		$errors = array();
		if ($_POST && Security::csrf_valid()) {
			$name    = trim(Arr::get($_POST, 'name'));
			$email   = trim(Arr::get($_POST, 'email'));
			$subject = trim(Arr::get($_POST, 'subject'));
			$content = trim(Arr::get($_POST, 'content'));

			if (!Valid::email($email)) {
				$errors['email'] = __('Please check the email address');
			}

			if (!$content) {
				$errors['content'] = __('Please say something');
			}

			// Send feedback
			if (!$errors) {
				$topic = __('Feedback') . ': ' . $subject;
				$mail  = $content . "\n\n" . Request::$client_ip . ' - ' . Request::host_name();
				if (Anqh_Email::send(Kohana::$config->load('site.email_contact'), array($email, $name), $topic, $mail, false, array($email, $name))) {
					$this->view->add(View_Page::COLUMN_MAIN, new View_Alert(
					__('Thank you! We will try to return back to you as soon as possible.'),
					__('Feedback sent!'),
					View_Alert::SUCCESS
					));
				} else {
					$errors['content'] = __('Could not send feedback');
				}
			}

			if ($errors) {
				$section->errors  = $errors;
				$section->name    = $name;
				$section->email   = $email;
				$section->subject = $subject;
				$section->content = $content;
			}
		}


		$this->view->add(View_Page::COLUMN_MAIN, $section);
	}


	/**
	 * Contact form.
	 *
	 * @return  View_Contact_Form
	 */
	public function section_contact() {
		return new View_Contact_Form();
	}

}
