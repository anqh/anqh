<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * Email
 *
 * @package    Anqh
 * @author     Antti Qvickström
 * @copyright  (c) 2013 Antti Qvickström
 * @license    http://www.opensource.org/licenses/mit-license.php MIT license
 */
class Anqh_Email extends Email {

	/**
	 * Send an email message.
	 *
	 * @param   string|array  $to        recipient email (and name), or an array of To, Cc, Bcc names
	 * @param   string|array  $from      sender email (and name)
	 * @param   string        $subject   message subject
	 * @param   string        $message   body
	 * @param   boolean       $html      send email as HTML
	 * @param   string|array  $reply_to  reply-to address (and name)
	 * @return  integer       number of emails sent
	 */
	public static function send($to, $from, $subject, $message, $html = false, $reply_to = null) {

		// Connect to SwiftMailer
		(Email::$mail === NULL) and email::connect();

		// Determine the message type
		$html = ($html === TRUE) ? 'text/html' : 'text/plain';

		// Create the message
		$message = Swift_Message::newInstance($subject, $message, $html, 'utf-8');

		if (is_string($to)) {

			// Single recipient
			$message->setTo($to);

		} elseif (is_array($to)) {

			if (isset($to[0]) AND isset($to[1])) {

				// Create To: address set
				$to = array('to' => $to);

			}

			foreach ($to as $method => $set) {
				if (!in_array($method, array('to', 'cc', 'bcc'), true)) {

					// Use To: by default
					$method = 'to';

				}

				// Create method name
				$method = 'add' . ucfirst($method);

				if (is_array($set)) {

					// Add a recipient with name
					$message->$method($set[0], $set[1]);

				} else {

					// Add a recipient without name
					$message->$method($set);

				}

			}
		}

		if (is_string($from)) {

			// From without a name
			$message->setFrom($from);

		} elseif (is_array($from)) {

			// From with a name
			$message->setFrom($from[0], $from[1]);

		}

		if (is_string($reply_to)) {

			// Reply to without a name
			$message->setReplyTo($reply_to);

		} elseif (is_array($reply_to)) {

			// Reply to with a name
			$message->setReplyTo($reply_to[0], $reply_to[1]);

		}

		return Email::$mail->send($message);
	}

}
