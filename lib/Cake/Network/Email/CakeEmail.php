<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Network.Email
 * @since         CakePHP(tm) v 2.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

App::uses('Multibyte', 'I18n');
App::uses('AbstractTransport', 'Network/Email');
App::uses('File', 'Utility');
App::uses('String', 'Utility');
App::uses('View', 'View');

/**
 * CakePHP email class.
 *
 * This class is used for handling Internet Message Format based
 * based on the standard outlined in http://www.rfc-editor.org/rfc/rfc2822.txt
 *
 * @package       Cake.Network.Email
 */
class CakeEmail {

/**
 * Default X-Mailer
 *
 * @var string
 */
	const EMAIL_CLIENT = 'CakePHP Email';

/**
 * Line length - no should more - RFC 2822 - 2.1.1
 *
 * @var int
 */
	const LINE_LENGTH_SHOULD = 78;

/**
 * Line length - no must more - RFC 2822 - 2.1.1
 *
 * @var int
 */
	const LINE_LENGTH_MUST = 998;

/**
 * Type of message - HTML
 *
 * @var string
 */
	const MESSAGE_HTML = 'html';

/**
 * Type of message - TEXT
 *
 * @var string
 */
	const MESSAGE_TEXT = 'text';

/**
 * Holds the regex pattern for email validation
 *
 * @var string
 */
	const EMAIL_PATTERN = '/^((?:[\p{L}0-9.!#$%&\'*+\/=?^_`{|}~-]+)*@[\p{L}0-9-.]+)$/ui';

/**
 * Recipient of the email
 *
 * @var array
 */
	protected $_to = array();

/**
 * The mail which the email is sent from
 *
 * @var array
 */
	protected $_from = array();

/**
 * The sender email
 *
 * @var array
 */
	protected $_sender = array();

/**
 * The email the recipient will reply to
 *
 * @var array
 */
	protected $_replyTo = array();

/**
 * The read receipt email
 *
 * @var array
 */
	protected $_readReceipt = array();

/**
 * The mail that will be used in case of any errors like
 * - Remote mailserver down
 * - Remote user has exceeded his quota
 * - Unknown user
 *
 * @var array
 */
	protected $_returnPath = array();

/**
 * Carbon Copy
 *
 * List of email's that should receive a copy of the email.
 * The Recipient WILL be able to see this list
 *
 * @var array
 */
	protected $_cc = array();

/**
 * Blind Carbon Copy
 *
 * List of email's that should receive a copy of the email.
 * The Recipient WILL NOT be able to see this list
 *
 * @var array
 */
	protected $_bcc = array();

/**
 * Message ID
 *
 * @var bool|string
 */
	protected $_messageId = true;

/**
 * Domain for messageId generation.
 * Needs to be manually set for CLI mailing as env('HTTP_HOST') is empty
 *
 * @var string
 */
	protected $_domain = null;

/**
 * The subject of the email
 *
 * @var string
 */
	protected $_subject = '';

/**
 * Associative array of a user defined headers
 * Keys will be prefixed 'X-' as per RFC2822 Section 4.7.5
 *
 * @var array
 */
	protected $_headers = array();

/**
 * Layout for the View
 *
 * @var string
 */
	protected $_layout = 'default';

/**
 * Template for the view
 *
 * @var string
 */
	protected $_template = '';

/**
 * View for render
 *
 * @var string
 */
	protected $_viewRender = 'View';

/**
 * Vars to sent to render
 *
 * @var array
 */
	protected $_viewVars = array();

/**
 * Theme for the View
 *
 * @var array
 */
	protected $_theme = null;

/**
 * Helpers to be used in the render
 *
 * @var array
 */
	protected $_helpers = array('Html');

/**
 * Text message
 *
 * @var string
 */
	protected $_textMessage = '';

/**
 * Html message
 *
 * @var string
 */
	protected $_htmlMessage = '';

/**
 * Final message to send
 *
 * @var array
 */
	protected $_message = array();

/**
 * Available formats to be sent.
 *
 * @var array
 */
	protected $_emailFormatAvailable = array('text', 'html', 'both');

/**
 * What format should the email be sent in
 *
 * @var string
 */
	protected $_emailFormat = 'text';

/**
 * What method should the email be sent
 *
 * @var string
 */
	protected $_transportName = 'Mail';

/**
 * Instance of transport class
 *
 * @var AbstractTransport
 */
	protected $_transportClass = null;

/**
 * Charset the email body is sent in
 *
 * @var string
 */
	public $charset = 'utf-8';

/**
 * Charset the email header is sent in
 * If null, the $charset property will be used as default
 *
 * @var string
 */
	public $headerCharset = null;

/**
 * The application wide charset, used to encode headers and body
 *
 * @var string
 */
	protected $_appCharset = null;

/**
 * List of files that should be attached to the email.
 *
 * Only absolute paths
 *
 * @var array
 */
	protected $_attachments = array();

/**
 * If set, boundary to use for multipart mime messages
 *
 * @var string
 */
	protected $_boundary = null;

/**
 * Configuration to transport
 *
 * @var string|array
 */
	protected $_config = array();

/**
 * 8Bit character sets
 *
 * @var array
 */
	protected $_charset8bit = array('UTF-8', 'SHIFT_JIS');

/**
 * Define Content-Type charset name
 *
 * @var array
 */
	protected $_contentTypeCharset = array(
		'ISO-2022-JP-MS' => 'ISO-2022-JP'
	);

/**
 * Regex for email validation
 *
 * If null, filter_var() will be used. Use the emailPattern() method
 * to set a custom pattern.'
 *
 * @var string
 */
	protected $_emailPattern = self::EMAIL_PATTERN;

/**
 * The class name used for email configuration.
 *
 * @var string
 */
	protected $_configClass = 'EmailConfig';

/**
 * Constructor
 *
 * @param array|string $config Array of configs, or string to load configs from email.php
 */
	public function __construct($config = null) {
		$this->_appCharset = Configure::read('App.encoding');
		if ($this->_appCharset !== null) {
			$this->charset = $this->_appCharset;
		}
		$this->_domain = preg_replace('/\:\d+$/', '', env('HTTP_HOST'));
		if (empty($this->_domain)) {
			$this->_domain = php_uname('n');
		}

		if ($config) {
			$this->config($config);
		}
		if (empty($this->headerCharset)) {
			$this->headerCharset = $this->charset;
		}
	}

/**
 * From
 *
 * @param string|array $email Null to get, String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return array|CakeEmail
 * @throws SocketException
 */
	public function from($email = null, $name = null) {
		if ($email === null) {
			return $this->_from;
		}
		return $this->_setEmailSingle('_from', $email, $name, __d('cake_dev', 'From requires only 1 email address.'));
	}

/**
 * Sender
 *
 * @param string|array $email Null to get, String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return array|CakeEmail
 * @throws SocketException
 */
	public function sender($email = null, $name = null) {
		if ($email === null) {
			return $this->_sender;
		}
		return $this->_setEmailSingle('_sender', $email, $name, __d('cake_dev', 'Sender requires only 1 email address.'));
	}

/**
 * Reply-To
 *
 * @param string|array $email Null to get, String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return array|CakeEmail
 * @throws SocketException
 */
	public function replyTo($email = null, $name = null) {
		if ($email === null) {
			return $this->_replyTo;
		}
		return $this->_setEmailSingle('_replyTo', $email, $name, __d('cake_dev', 'Reply-To requires only 1 email address.'));
	}

/**
 * Read Receipt (Disposition-Notification-To header)
 *
 * @param string|array $email Null to get, String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return array|CakeEmail
 * @throws SocketException
 */
	public function readReceipt($email = null, $name = null) {
		if ($email === null) {
			return $this->_readReceipt;
		}
		return $this->_setEmailSingle('_readReceipt', $email, $name, __d('cake_dev', 'Disposition-Notification-To requires only 1 email address.'));
	}

/**
 * Return Path
 *
 * @param string|array $email Null to get, String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return array|CakeEmail
 * @throws SocketException
 */
	public function returnPath($email = null, $name = null) {
		if ($email === null) {
			return $this->_returnPath;
		}
		return $this->_setEmailSingle('_returnPath', $email, $name, __d('cake_dev', 'Return-Path requires only 1 email address.'));
	}

/**
 * To
 *
 * @param string|array $email Null to get, String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return array|CakeEmail
 */
	public function to($email = null, $name = null) {
		if ($email === null) {
			return $this->_to;
		}
		return $this->_setEmail('_to', $email, $name);
	}

/**
 * Add To
 *
 * @param string|array $email Null to get, String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return $this
 */
	public function addTo($email, $name = null) {
		return $this->_addEmail('_to', $email, $name);
	}

/**
 * Cc
 *
 * @param string|array $email Null to get, String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return array|CakeEmail
 */
	public function cc($email = null, $name = null) {
		if ($email === null) {
			return $this->_cc;
		}
		return $this->_setEmail('_cc', $email, $name);
	}

/**
 * Add Cc
 *
 * @param string|array $email Null to get, String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return $this
 */
	public function addCc($email, $name = null) {
		return $this->_addEmail('_cc', $email, $name);
	}

/**
 * Bcc
 *
 * @param string|array $email Null to get, String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return array|CakeEmail
 */
	public function bcc($email = null, $name = null) {
		if ($email === null) {
			return $this->_bcc;
		}
		return $this->_setEmail('_bcc', $email, $name);
	}

/**
 * Add Bcc
 *
 * @param string|array $email Null to get, String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return $this
 */
	public function addBcc($email, $name = null) {
		return $this->_addEmail('_bcc', $email, $name);
	}

/**
 * Charset setter/getter
 *
 * @param string $charset Character set.
 * @return string this->charset
 */
	public function charset($charset = null) {
		if ($charset === null) {
			return $this->charset;
		}
		$this->charset = $charset;
		if (empty($this->headerCharset)) {
			$this->headerCharset = $charset;
		}
		return $this->charset;
	}

/**
 * HeaderCharset setter/getter
 *
 * @param string $charset Character set.
 * @return string this->charset
 */
	public function headerCharset($charset = null) {
		if ($charset === null) {
			return $this->headerCharset;
		}
		return $this->headerCharset = $charset;
	}

/**
 * EmailPattern setter/getter
 *
 * @param string|bool|null $regex The pattern to use for email address validation,
 *   null to unset the pattern and make use of filter_var() instead, false or
 *   nothing to return the current value
 * @return string|$this
 */
	public function emailPattern($regex = false) {
		if ($regex === false) {
			return $this->_emailPattern;
		}
		$this->_emailPattern = $regex;
		return $this;
	}

/**
 * Set email
 *
 * @param string $varName Property name
 * @param string|array $email String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return $this
 */
	protected function _setEmail($varName, $email, $name) {
		if (!is_array($email)) {
			$this->_validateEmail($email);
			if ($name === null) {
				$name = $email;
			}
			$this->{$varName} = array($email => $name);
			return $this;
		}
		$list = array();
		foreach ($email as $key => $value) {
			if (is_int($key)) {
				$key = $value;
			}
			$this->_validateEmail($key);
			$list[$key] = $value;
		}
		$this->{$varName} = $list;
		return $this;
	}

/**
 * Validate email address
 *
 * @param string $email Email
 * @return void
 * @throws SocketException If email address does not validate
 */
	protected function _validateEmail($email) {
		if ($this->_emailPattern === null) {
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				return;
			}
		} elseif (preg_match($this->_emailPattern, $email)) {
			return;
		}
		throw new SocketException(__d('cake_dev', 'Invalid email: "%s"', $email));
	}

/**
 * Set only 1 email
 *
 * @param string $varName Property name
 * @param string|array $email String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @param string $throwMessage Exception message
 * @return $this
 * @throws SocketException
 */
	protected function _setEmailSingle($varName, $email, $name, $throwMessage) {
		$current = $this->{$varName};
		$this->_setEmail($varName, $email, $name);
		if (count($this->{$varName}) !== 1) {
			$this->{$varName} = $current;
			throw new SocketException($throwMessage);
		}
		return $this;
	}

/**
 * Add email
 *
 * @param string $varName Property name
 * @param string|array $email String with email,
 *   Array with email as key, name as value or email as value (without name)
 * @param string $name Name
 * @return $this
 * @throws SocketException
 */
	protected function _addEmail($varName, $email, $name) {
		if (!is_array($email)) {
			$this->_validateEmail($email);
			if ($name === null) {
				$name = $email;
			}
			$this->{$varName}[$email] = $name;
			return $this;
		}
		$list = array();
		foreach ($email as $key => $value) {
			if (is_int($key)) {
				$key = $value;
			}
			$this->_validateEmail($key);
			$list[$key] = $value;
		}
		$this->{$varName} = array_merge($this->{$varName}, $list);
		return $this;
	}

/**
 * Get/Set Subject.
 *
 * @param string $subject Subject string.
 * @return string|$this
 */
	public function subject($subject = null) {
		if ($subject === null) {
			return $this->_subject;
		}
		$this->_subject = $this->_encode((string)$subject);
		return $this;
	}

/**
 * Sets headers for the message
 *
 * @param array $headers Associative array containing headers to be set.
 * @return $this
 * @throws SocketException
 */
	public function setHeaders($headers) {
		if (!is_array($headers)) {
			throw new SocketException(__d('cake_dev', '$headers should be an array.'));
		}
		$this->_headers = $headers;
		return $this;
	}

/**
 * Add header for the message
 *
 * @param array $headers Headers to set.
 * @return $this
 * @throws SocketException
 */
	public function addHeaders($headers) {
		if (!is_array($headers)) {
			throw new SocketException(__d('cake_dev', '$headers should be an array.'));
		}
		$this->_headers = array_merge($this->_headers, $headers);
		return $this;
	}

/**
 * Get list of headers
 *
 * ### Includes:
 *
 * - `from`
 * - `replyTo`
 * - `readReceipt`
 * - `returnPath`
 * - `to`
 * - `cc`
 * - `bcc`
 * - `subject`
 *
 * @param array $include List of headers.
 * @return array
 */
	public function getHeaders($include = array()) {
		if ($include == array_values($include)) {
			$include = array_fill_keys($include, true);
		}
		$defaults = array_fill_keys(
			array(
				'from', 'sender', 'replyTo', 'readReceipt', 'returnPath',
				'to', 'cc', 'bcc', 'subject'),
			false
		);
		$include += $defaults;

		$headers = array();
		$relation = array(
			'from' => 'From',
			'replyTo' => 'Reply-To',
			'readReceipt' => 'Disposition-Notification-To',
			'returnPath' => 'Return-Path'
		);
		foreach ($relation as $var => $header) {
			if ($include[$var]) {
				$var = '_' . $var;
				$headers[$header] = current($this->_formatAddress($this->{$var}));
			}
		}
		if ($include['sender']) {
			if (key($this->_sender) === key($this->_from)) {
				$headers['Sender'] = '';
			} else {
				$headers['Sender'] = current($this->_formatAddress($this->_sender));
			}
		}

		foreach (array('to', 'cc', 'bcc') as $var) {
			if ($include[$var]) {
				$classVar = '_' . $var;
				$headers[ucfirst($var)] = implode(', ', $this->_formatAddress($this->{$classVar}));
			}
		}

		$headers += $this->_headers;
		if (!isset($headers['X-Mailer'])) {
			$headers['X-Mailer'] = self::EMAIL_CLIENT;
		}
		if (!isset($headers['Date'])) {
			$headers['Date'] = date(DATE_RFC2822);
		}
		if ($this->_messageId !== false) {
			if ($this->_messageId === true) {
				$headers['Message-ID'] = '<' . str_replace('-', '', String::UUID()) . '@' . $this->_domain . '>';
			} else {
				$headers['Message-ID'] = $this->_messageId;
			}
		}

		if ($include['subject']) {
			$headers['Subject'] = $this->_subject;
		}

		$headers['MIME-Version'] = '1.0';
		if (!empty($this->_attachments)) {
			$headers['Content-Type'] = 'multipart/mixed; boundary="' . $this->_boundary . '"';
		} elseif ($this->_emailFormat === 'both') {
			$headers['Content-Type'] = 'multipart/alternative; boundary="' . $this->_boundary . '"';
		} elseif ($this->_emailFormat === 'text') {
			$headers['Content-Type'] = 'text/plain; charset=' . $this->_getContentTypeCharset();
		} elseif ($this->_emailFormat === 'html') {
			$headers['Content-Type'] = 'text/html; charset=' . $this->_getContentTypeCharset();
		}
		$headers['Content-Transfer-Encoding'] = $this->_getContentTransferEncoding();

		return $headers;
	}

/**
 * Format addresses
 *
 * If the address contains non alphanumeric/whitespace characters, it will
 * be quoted as characters like `:` and `,` are known to cause issues
 * in address header fields.
 *
 * @param array $address Addresses to format.
 * @return array
 */
	protected function _formatAddress($address) {
		$return = array();
		foreach ($address as $email => $alias) {
			if ($email === $alias) {
				$return[] = $email;
			} else {
				$encoded = $this->_encode($alias);
				if ($encoded === $alias && preg_match('/[^a-z0-9 ]/i', $encoded)) {
					$encoded = '"' . str_replace('"', '\"', $encoded) . '"';
				}
				$return[] = sprintf('%s <%s>', $encoded, $email);
			}
		}
		return $return;
	}

/**
 * Template and layout
 *
 * @param bool|string $template Template name or null to not use
 * @param bool|string $layout Layout name or null to not use
 * @return array|$this
 */
	public function template($template = false, $layout = false) {
		if ($template === false) {
			return array(
				'template' => $this->_template,
				'layout' => $this->_layout
			);
		}
		$this->_template = $template;
		if ($layout !== false) {
			$this->_layout = $layout;
		}
		return $this;
	}

/**
 * View class for render
 *
 * @param string $viewClass View class name.
 * @return string|$this
 */
	public function viewRender($viewClass = null) {
		if ($viewClass === null) {
			return $this->_viewRender;
		}
		$this->_viewRender = $viewClass;
		return $this;
	}

/**
 * Variables to be set on render
 *
 * @param array $viewVars Variables to set for view.
 * @return array|$this
 */
	public function viewVars($viewVars = null) {
		if ($viewVars === null) {
			return $this->_viewVars;
		}
		$this->_viewVars = array_merge($this->_viewVars, (array)$viewVars);
		return $this;
	}

/**
 * Theme to use when rendering
 *
 * @param string $theme Theme name.
 * @return string|$this
 */
	public function theme($theme = null) {
		if ($theme === null) {
			return $this->_theme;
		}
		$this->_theme = $theme;
		return $this;
	}

/**
 * Helpers to be used in render
 *
 * @param array $helpers Helpers list.
 * @return array|$this
 */
	public function helpers($helpers = null) {
		if ($helpers === null) {
			return $this->_helpers;
		}
		$this->_helpers = (array)$helpers;
		return $this;
	}

/**
 * Email format
 *
 * @param string $format Formatting string.
 * @return string|$this
 * @throws SocketException
 */
	public function emailFormat($format = null) {
		if ($format === null) {
			return $this->_emailFormat;
		}
		if (!in_array($format, $this->_emailFormatAvailable)) {
			throw new SocketException(__d('cake_dev', 'Format not available.'));
		}
		$this->_emailFormat = $format;
		return $this;
	}

/**
 * Transport name
 *
 * @param string $name Transport name.
 * @return string|$this
 */
	public function transport($name = null) {
		if ($name === null) {
			return $this->_transportName;
		}
		$this->_transportName = (string)$name;
		$this->_transportClass = null;
		return $this;
	}

/**
 * Return the transport class
 *
 * @return AbstractTransport
 * @throws SocketException
 */
	public function transportClass() {
		if ($this->_transportClass) {
			return $this->_transportClass;
		}
		list($plugin, $transportClassname) = pluginSplit($this->_transportName, true);
		$transportClassname .= 'Transport';
		App::uses($transportClassname, $plugin . 'Network/Email');
		if (!class_exists($transportClassname)) {
			throw new SocketException(__d('cake_dev', 'Class "%s" not found.', $transportClassname));
		} elseif (!method_exists($transportClassname, 'send')) {
			throw new SocketException(__d('cake_dev', 'The "%s" does not have a %s method.', $transportClassname, 'send()'));
		}

		return $this->_transportClass = new $transportClassname();
	}

/**
 * Message-ID
 *
 * @param bool|string $message True to generate a new Message-ID, False to ignore (not send in email), String to set as Message-ID
 * @return bool|string|$this
 * @throws SocketException
 */
	public function messageId($message = null) {
		if ($message === null) {
			return $this->_messageId;
		}
		if (is_bool($message)) {
			$this->_messageId = $message;
		} else {
			if (!preg_match('/^\<.+@.+\>$/', $message)) {
				throw new SocketException(__d('cake_dev', 'Invalid format for Message-ID. The text should be something like "<uuid@server.com>"'));
			}
			$this->_messageId = $message;
		}
		return $this;
	}

/**
 * Domain as top level (the part after @)
 *
 * @param string $domain Manually set the domain for CLI mailing
 * @return string|$this
 */
	public function domain($domain = null) {
		if ($domain === null) {
			return $this->_domain;
		}
		$this->_domain = $domain;
		return $this;
	}

/**
 * Add attachments to the email message
 *
 * Attachments can be defined in a few forms depending on how much control you need:
 *
 * Attach a single file:
 *
 * ```
 * $email->attachments('path/to/file');
 * ```
 *
 * Attach a file with a different filename:
 *
 * ```
 * $email->attachments(array('custom_name.txt' => 'path/to/file.txt'));
 * ```
 *
 * Attach a file and specify additional properties:
 *
 * ```
 * $email->attachments(array('custom_name.png' => array(
 *		'file' => 'path/to/file',
 *		'mimetype' => 'image/png',
 *		'contentId' => 'abc123',
 *		'contentDisposition' => false
 * ));
 * ```
 *
 * Attach a file from string and specify additional properties:
 *
 * ```
 * $email->attachments(array('custom_name.png' => array(
 *		'data' => file_get_contents('path/to/file'),
 *		'mimetype' => 'image/png'
 * ));
 * ```
 *
 * The `contentId` key allows you to specify an inline attachment. In your email text, you
 * can use `<img src="cid:abc123" />` to display the image inline.
 *
 * The `contentDisposition` key allows you to disable the `Content-Disposition` header, this can improve
 * attachment compatibility with outlo3´ıBºsÄÙÒ`'\¯ı·ÄëşQ `?Õ%½ÿrOö•FUúpì‹}‘$  rèT€}öÙ'µÖQmW®\yPôÿˆ:=ı#é »`UÔ/£nïĞ¡Cº5İú¨«V­’Tö~a÷Ø'¤_ˆé'vÀĞXnŒ×ş¢ €7÷S{Ä~ê—öSU%ıù)±/ªqN ğèT”İvK¥çZ¦Ÿİ6¤[¼)êŒ¨=¥¼ƒW£n‹ÓºuëiÑÓÕ†[6n47ŒíZÆş ı]ÿwiğëAÿXşk€+ÏÀ~ªšÍŠêû¢•±/*ˆ àĞÉ„ÚÚÚÓ¢}¾mÛ¶i˜®LOCö’ª”~ƒ~Ëë¯¿¾2úøT»ï¾ûÅUµ/ø·Øü8÷³ ¤7„7Åºğ§èÆšğ¢H à]÷SßıÔ7ãp7idïœ9öES¢2öE¯‰ àŸ “)/¿ürú™ş\Ôéûí·_znzºõ{›bÙ•nÃ¾~ùòåË¢ÿ.jBÇTõ }´±83úQ­¤RuÒà¼6êÙX¾ëÂïD ;µŸJï­Ü‡{ç¼‡˜¯Ä¾èg±/ºL  ÛgóKfÍ;7µc£>õÙƒ>ø¹º[½·ó³/EÒoÊ¿:oŞ¼¥Ñõ‡C9d²h€·íºGûvìÒ£_öÍÕÓÉ¶Qkb}øKô;¢FÇú  Øµ½ÔûbuWÕV"+İ©mvì†Æ¾è!q  ¼3CDªÆ”)SˆÖ7ê„={š«»Õ{*W¤AeØµ*ÕSO=5;úQsÌ13EìÀ> =Ş%İ†ôÓ±èZÜì%™L­/×ˆ‰Ñï‰õáq± @£ì£R»:öPçEï*‘ŠórìÒcÍ.ıÑJq  ¼;tªÒC=ôhŸŒú—SN9åè\İi¢Ş+(+k£^ŠZñğÃOşÿ¢ştê©§¾" û€îÅ}À'cĞ­¸H{¿TWÙkÄ}iˆ5B2 ĞD{¨Ø;]‡'E}@"±Wz"öI·Æşè>q  ì8tªŞ=÷Ü“ÚÅÀ£Î:ë¬.Ñ;ËéPZé
ÂÅQ/Ş{ï½s¢O‹z4jæÙgŸ- )öûÕÛ|4úşQŠ½„Êj}HWL­*®‹¬ Ğ,{§ÔúÆ¾éëÑ?õ>©”ô¸³§c¿twôì“  v:¼Í-·ÜÒ&W÷l¯^ƒşpôæê†é©¨Cã[«š/¹õÖ[Ÿ‰şdÔ_†²J4@‰÷ ©•®JOÃôn±8 W÷ÆğÖÚË~ É­‹Z«»%û²X×Š…Å					throw new SocketException(__d('cake_dev', 'File not found: "%s"', $fileName));
				}
				if (is_int($name)) {
					$name = basename($fileInfo['file']);
				}
			}
			if (!isset($fileInfo['mimetype'])) {
				$fileInfo['mimetype'] = 'application/octet-stream';
			}
			$attach[$name] = $fileInfo;
		}
		$this->_attachments = $attach;
		return $this;
	}

/**
 * Add attachments
 *
 * @param string|array $attachments String with the filename or array with filenames
 * @return $this
 * @throws SocketException
w\U®8«©Š»u1Vé\UÔ«X«x«±V©Š¸â®n£uF*ßLUnuN*Şvup+X«uÅ]LUÛ±WmŠ»vø«TÂ®å®»·ŠµŠ¸Š¸ûb‡b—V˜«x«±V{b®§*ìUØ«±WR¸«X«cßk[ÌC×]ZáW`WmŠ»u+Š…]]Šºƒ
¸àKG|(u<p+«LRá¾*Ö*ØÅ&¸¥ÛaWV˜ØöÅ.8«[â®w,UÕÅZ®*İqCUÂ—`WW:˜¥Ø¡¿–)uqV©ŠµLUÛâ®Å¥Ø«GS“¦*Æõi™\ß1r–q@ME{eL“}=Ëeøƒ'Ë™b­b®ÅZÛ-8«MŠ¬5ÅVUEéŠP²â†#ç3û¤§^G"PÃ(I¯|Š[¨éĞâ­“J/|ULíJâ«ÅN*·ñ8«‹÷b­†/¶Àxâ­ò b«Hğ8«€¡ßp4öÅZRÏwÄ>G®*êr«»SKæ€–¨'4ªbÕÉêr|eWFê<e\m›Ç2­-£“±ÇŒ«ofàTœxÊ¶¶´ Š“ß2®ú£ ëW5¡=*<qã*Ğµa¾çéÇŒ«bÈ¾õÇŒªófX
Ÿ–2«>¢ŞçWF=kUÍdi±Û2«>¦ÔÇŒªámAïóÇŒ¡ÆÕ—©ØôßVÄB?e*fİ×¹Ãâ.îô¦Øø…4½¬İhœ|BŠRôôc\|B´Ø¶}¦#­ÄGÚ5ÇÄ+KRA«T
áñ
Óo‡¡?,!ZZ#”ëí€Ì”Ò:ıòµWaCNø«t 5Ù±U6m©]ĞÔôÂ«Šñß±Å\iAøb­;oWN”UJrİ«_^u4V;{äxB8[ÏSñ1úppál4ÍÑÛïÇ„-4Ò\Wí5>xğ„pº³
ÕÛo|<!i£$ô¨fûğp„ğ¹sÕÛïÃÂ…w)ÇG?~´·ÇPÍO<!x\~ÌÇéÃÂœ$œŠT‚}ñáK’Ÿh×çƒ„"–şıw,GÓ‡„&—3 yıñáE6L”‘ûñáM8z¥I@ùàáE-C-~ÓWç‡…ix–F[n¤+K’Óí¿¼-s—»¿¦¹ÊA<Í{npğ„ğ¶nîß~<!iw)©^M÷àáZlzÇö›ïÇ…iÃÔ®îß~<!³”õ§6ûğğ„ÓjóÖœÛïÁÂKd~œ<!4Œ…I’£8¥\Uh`zí…["‚Ÿv*´ø¾Tí\U ÊNûb®jõì0«¸Ñ|p%3OPªOÑ‚›¡“…`ÌØĞlñËq­ÃWã ØĞ_¸‹æ?,h/USµ>2<pP_µ#\=…€3ÜQNG"äá “œ¬qö‹añÊÂ.zò4Æ‚øå²n[£1 ¾9wúNß>8ĞOæl× }£ƒ„/WÆdmšB?xB<rµÚäìŒvûğˆ…Ë—ë'bä`á	üÁtosËw4ÃÂó²×@ı£ƒ„/æA®y9xBş`»Ô¸¦ÎIÁÂó¦k€7sQ‡„/æ
Ô’èìïòÇ„/æ
ã-Å@,pp…ñËo$àü,~œxBøåÌ÷+Oµßå_´¯pÇí‘óÆ‚|r½ŒÀm!-×¦Ø(/å’à.Íñøh19ŠÏJãz¾9Ê"È¼{>çÇD¢XÖ¤}øX:¤Upâ7z¶¦*Ğ@H=½ğ+D-vÅ[w;üñVº‰5ñÅVˆèiN¸¥ÄĞĞ
Uµ’UêÇ(–#e•6ndéÈŒ¯òñeÆW}fE;±#çƒòÑ^2´^OİßƒòÁ|BÑ¹Ÿ¨c÷ãùh¯oë2š7"<wÃù`¼eq¹—³¼r_—ã+~±?‰ûò?–	ã.ÏÈÇïÇòÁxÊô¼˜Ÿ¶iƒò±_»ëó×íœå"¾!lêUÙÉúqü¬SâÍr¤€ÇnõÇò±_­:­ÇóŸ¿å"¾!hê×IOˆïï‡ò‘_®:Íßg8şT/ˆZı3tîß~ÊE|BåÖ.ú‡8şR+âÎµvNîp~R+âÇW»Rä}8şR+â­«ÜšRF'å"¾!\5;ó<¼0şR+â?Ò·BŸoïƒò‘_¸ëg£Ÿ¿å"¾!lk7`}³÷àü¤WÄ.ı5xÇf4ùãùH¯ˆW^äõvéãå"¾!CM©]L¥Y‰ß-†œDØbgka,H®d€Á\ë’BÓâ;xáVÌŒ:â«x†Ü`UàìF*ïˆí]ñV”÷Û¦s M\UBjŸ³ŠB¾»Uc†ÛÆZU†òéúÈ@îpñ'Æ.{ëªN<Kã£P»?¶~ìx“ã6n¯:—lx—Æ+MÕÒŠú‡<bØ¸ºêd4Ç‰|b·ëW~ÙvÆÑâ—	®©ıá8Ú|b»Ô¹şvÚøÅ¶¸¹˜xãhñJÕìôãiñšúÕÏFs‡‰|eE½—Â(Gq¯Š·ë÷9œx“ã7õ»²*d¯ÑøÎYnÛ¤˜-1kÖ»ï!®6¾1]ëİ_SOŒï®^.ÁëøËş¹z½_øáâOŒÓê7c£×èÃÄ¾3Z½4%úŠöÁÄÆîåNòlx—Æ+^òìsúqâ_LßİNdãÄ¾1^—WP£ÆYõ‹‚y,QLmŒ²Z!&!wï¥uZü±BÚ¯p*1V¶­@Å]É—ít÷Å[S]É¦*Ù4Ş«Jk±ßo¦ıÆ*Ôz©´@®ù‘œ-ÇÄ¨<ÆÇ¢å¾3W‚¼y‰éNøËàµş"~¼1ñ—ÁoüC%+ÃcŒ¾
ßñ#ÿ &>2ø.1IM’˜øËà¹<Ã"2×™|ÿ â2Mañ‘à¸ù‰‡ìcã/‚ïñ±Œ¾™HıƒŒ¾_âV?±øãã'ÁoüFİÓâ3Ù>2ø-‰òcã/‚½|Å^«L|eğZ1‘û>2ø-2ûøËà»üKşF>2ø-Ÿ2wàqñ—Á-Çæ5cº0øËà¹¼ÅN‰ŒoøŒöCƒÆO‚Óy‰¿àñ—Áhyì|dø.ÿ ·Œ¾
Ùuç¥pÉT`¸iâ§©ÌYr@¥q'ñï×"Éirİzâ­mZU¾!†ÿ v*ÖÄ¶*Ø_Ï†*î5íŠ»Ûm«°¥1UHO& œ
ôÿ .õ€ éôäâ”é<2Jª¸ªñ¾*¼U° Å[¦*İ1VÆØ«…1Wb­â­W¶*Ş*ÕqCuÅ.úqWUØ¡Àâ®#¸b­×Ãp8«±Wb®ÅÅ-Ók
]_U½ñKU8UÕ®vØ«x«*Õ*ŞıñW|±W|ñWSkåŠ·LUÂ˜«ª{b®äqWW¹Å[¯†*êöÅ]Šº¸«¨;â®­:b­oŠ»pÅ¥1K{â®ïŠ»oh¶*á¶*êb®Å[ŒUÕÅ]Š»–*êâ®®*êâ®ëŠº£up«U8Ø««…]Jâ®À­â­ÔvÅ]Š»[ôaVÁÀ®Å]\Uºb®¦*êaV0+}qVëŠ´qWuÅ]Šº˜«ºb®¦*î#vß<UÕ±WSlb®®*êâ®Øb­V½1VêqVñV±VÏ¾*Ö*êöÅ\F*êb®8«@b­â®Å]Jâ­ôÅ]Zâ®ëŠ»j£o®*Õ1WWuqWoŠ»p8«x«=1V©ãŠ»*êxb­Ôâ®Å]Šº8««Š¸ûâ®¨8«±V«Š·Šº¸«¶Å]Š·\U­±WWj˜«tÅ]LUØ«±VéŠ»v*à1Wb­â®¦*Õ<1VÆ*İ0+©…\1VŠâ­oŠº»â­×u|1WTâ­oßow^¸«cÃº˜UÀSvØÄÓuqV»â­œUÕñÂ­ñVúğ+|«ŠµÛ®*ÕqWÿÕë47]¾*âqWb®®*ÕqVn¸«*ìUªb®Å\*İ1WSqÛv*ãŠµŠº¸«±Wb­šâ®8«±V±WSv*ìUÃlUÇÛv*âqWb­Su*êb­Óu1WSu1Wb­Óp8«Gv*Ö*Ş*ÕkŠ·ŠµŠµ×v*ß\UªSqÅ]\UÕÅ[««Š»kv*íñV·Å[ßv*Ñ4Å]ŠµCŠ·ÓuqWuÅ]Š»uqWWn¸«X«†*Ş*êb®5íŠµ¹Å[ÅZëŠ¸UÇk®*ØÛn¸«Dâ®Å[Å\*ãŠµŠº˜«x«^Ø«X«tğÅ]Jb®ëŠº˜«±WR¸«±WSkÛlb­â®8«TïŠ´wÅ\*İqWb­So¦*êWj˜«†*ìUØ«†*Ş*â1Wb­b®¦*êb­Óv*ìU¬UØ«†*»ZkŠ»sŠ¸â®÷8«x«TÅ]LUÀb­â®ÅZ¦*êb®;UÀâ­â­b®ßpßu1WSuqWrñÅZÅ[ÅZÅ[Üâ­ñ8«X«±V±Wb­‘Š¶7ëŠµ\U¼UØ«X«TÅ[éŠ»v*ÑEcRF*º¾«*ÕiŠ¸b®Å]Çv*ìUÔ8«*ìU¼Uªâ®Å]Š¸b­œU¬UÔÅ\*ßŠ»ˆÅZ¦*İ1WShmŠ¶*êï¶*Öø«ªqVëŠ·\U¢p«°+TÅ\F*êb­â®ÅZ =1V‰#lâ®U±Šµ[ÅZéŠ·ËuqVˆ®*î8«±VèqVqW\UØ«°«u¦ku{b®'qÅ]\*İp+©\Uİ0«°+X«·Å]óÅ]Š·Š»nø«ª1WWv*ÑÅZÅ[ÛnƒwUİ1V±WuÅ[¦*Õ1WqñÅ\1VéŠ´1V*İqWWw,UÕÅZ®*áŠ·AŠºƒu1Wb®¦*Ö*ìUÇo¦*Ö*êb®®*ìUØ«©Š·Šº´ÅZ­qC†)lUÕÅ]óÅZ¦*ßLUÇÛuqWr1Vºâ­â®¦*áŠ»
µÓ»u+Š»vvøÕ8«·Å\qWSqÅ]¾*íñWSolUİ:b­V¸««Š·AŠº˜«X«tÅ\1WuÅZ#Ãv*î8«{áWTâ­`Wb®¦*Ø¦*êŒUÛUÛb®Û¾*êâ®¯†*×\*à£º¸UØÛb­Š»v*êÓkvÿ F*êb­Ò˜«Gv*êb®Å]LUÃ|*ÑÀ®ßv*ìUØ«±C«\)v7ŠZÅÅ.Å§|RÕqWSwÏvØ«†(v)pÅ¨Â®®*Öø¼Rêâ®Øâ®éŠëŠ]CŠ»v*êâ‡b­aVê0+¶ÅZ®)uF*ìUÕÅ]\Pêâ®¦*ÑÛ¡î‚¸ŞR˜“;¶HWØ}=r
Ÿé	Æ:øæN1³	&£.bÙÛuF*ÑÅZ©Å]\P´œUa®*¦Ş8ª“œU-qV+æâ= æ»dJQOLŠTĞÔ×^9uÅV]ñVÃš×kO†*Ø®*à@éŠ»§ÓŠ­í¿LUÅI44ÅWFÇZwÜô«U÷ÅWT¿lUª×¶*ãcZıªê5ØUN„·«Li×[±«÷â­Ô6äb«¶§¶*âÊMhvÅ[ ëŠ´Vƒ¦*ĞR7Å\v;b«”ôÅ\ ş8ªæ »øbª|éŠº•§¾*ïLxïŠ­ «Š­Uf8ªª'lUÕfÅ]é¶*·Ó=+Š¯1
m×ZE1VĞ
ÔõÅV°$òêqUÔV¥+Š®§M¾œU¢I;b®5;±UÄ{â­DWr©aŠ­§R:àW*Ôï…W +·~Ø«„}Ã¬©#|U¥ˆı£ßmV§]A×íb«>ïíŠ¯$¦*´%v®Ø«f˜«\Aé¶*í‰®*×¤	ğU¶Œo¾w¢1UŞ‘#ÃèÅV˜‚ŠÓ|UhÙvÅZTQŠªğMzâ«}5èE1U† vû±VÕôÜ÷ÅW*ò½1UÀ(ßzb«
Ózâ«x­ø«a =qVÇöÛßj”?*¨Tğªò  lUi]©TØqUÌ>šb­…qWT×sŠº0	,M*×‹S"§~˜«”h6ùâ–œv¥[/Z…«}¥¥RNı1WH
ü SmEA{œUo]:b…èÄš¶ÅV*h)tj:¸«|JšÖ¤vÅ)¡şÌUªÔ÷®)i@Î(_NC~ø¥L(;€Å[GÙÅVúELU¾Å1Uër>ìUÀ wâ®)Ô“Š¶/\U¶¡éÛwSm ß¸ÅøĞí×
¯¡°Å\ÏAµ;œUeF*îuğÀ­î»AŠ¸#n¸U¥bwñÀ®&wÅZ-]*ãËí
S¦*ß]…qWOÚ¯HÔ®ÿ v(XÕ=;b«j¯\UQOÅÓZPTâ­“µ®*¤TR½0%²(;aBáB´èqKš¦ƒ¦*ÑiOÕZjSŠ€Vñü1K +@|*¼ÛÖ>iJ»ïÿ Š¨²×§ãŠ®J°®ã¾*½£
Hî0¡k]ğ%£L*æ#c÷àC`Ö˜«M¹§O£l¡føUºq]Æø«kµ*¸íï\Ui¨8«`w=0«{±Ûp4÷8«@òÛ­=ñUáÛ¨ê1U"Ôß·¾uAŞ˜«š>[áUœ¶áÛr/UµQ]ºâ«¦*µÀØu8«MÄĞ¸«–>Xªõ>™÷Å-çŠ_‹}ñUüüñVÊã©•NÃ5Æ»â•õïJç[]¶ßlŸôâ†˜6Àâ®áÄrÅV­Iäv÷ÅW1 ~ø«^Ÿ-ñV‚š×Ã^Â½FØªÀ “Üâ«ømP1W»öû±VË1½qVù²*qV˜Sj|ñUBE;÷ÅV5?³h·ëŠ®âx×¶*Ò?QÛh’Ç¦*±â„ûÓ)­²V»â•Ín£aøb…¢ÜSq¾)q‰~ÏİŠˆ—~TÅ[ôÇpØ«5m€Å]é(n½±VÄa¾ìUg¤7¨ÅWĞ­;â­¤1ƒNİñVšß¦*â€ FÃÛp‰Z¿ÇTHÑT­|6«%5|°*ßGˆ>ø«ŒvïŠ¶#ãµ+Š¹#¯ALUÜ}ª{b­¬ 
øb«•Tõ«^Š)©8ªªÄ›©ûñU¢ÙOÎ¸¥Q%H8ª¢šˆÃÇZÕı¯ÃpJ¸«[ŒU¢68ªê·|UqŞ¼~ghN*âH5;â«Ë}“÷â¯JÑ.$Î.(XS·¾”ò	yõd•^1Uãn¸ªáŠ·LU±LUİqCX««Š[pÛv(oh{b•ÀœUÛâ­W;¶N*Öq$àVëŠáVÉÕ®)v(vvouqK]1Wb®l{â­UÕÅ[­qV‰¦*í±VÎø««Š»lUÃpÅ\¶*ßUªb®÷Å]\*ìU±]°Å]Š»uqVë\UÕ«[U¬Uº‘Š»klUÔÅ]LUµø«u8«UíŠº¸«ºb­Ôb®"¸«Db­Slâ­Sol*ãL
á\UÆ˜«©Šº˜«€¦*êŠ·\U­ºâ­ü±V±WtÅ]QŠ¶)Š¸ŠµÇÃwUÄâ®ëŠ·¾*î˜«U'hŠ®¦*ìU¿–*ĞÅ]¾*İëŠµLUÛŒU°|qV¶*ãŠ»¯Ïn˜«†*íñV‰Å[«X«}qV*Ş*áŠº”;b­ÓkpÅ]¶*î¸«€ñÅ[˜«¶Å]Š¸øâ®p4Å]Š»q¯lUÔÅ]Jâ®è1VÎ*ÕqWvÅZ©Å[ùâ­Šº˜««Š·\UØ«½ñV±UÛŒU¬UØ«{b®ÅZíŠ¶*ã¶*ãŠº»Wkou1V‡†*á¶*İqVëŠº£k–*ØÅZ#q±Wn]P{b«j+…ZäqUÀ×w¾*î¸«uÀ®¦k*î=ñVê[ÍN*ŞØUªøb®$üğ+»b®Å[¨¦*ÿ ÿÖëòhu0+±VÎ*íñWU¬*ÙÅ]ŠµŠ»hb­â®Å]Šº¸¾¸U¬UØ«©ŠµOUÔ¦*ìUØ«±VñVºb®Å]ŠµŠ·ŠµŠº¸«©Š»pÅ[Å]\UØ«©Š¸œUİ1WuÅ]Š»uqWb®¦*ìUØ«UÅ]×v*â1WV»b®¦*Ö*ìUØ«©Š»*Ş*áŠ»uqV±WPâ®¦*êb®ÅZ®*î¸«TÅ[¦*ìUÕÅ]Šº˜«cu1Wb­Óku1Wl1WSku0«€À­Óu*Ö*Ş*ÖÇuqV±Wb­â­Ôâ­Wv*ìUºâ­V¸«uÅZ8«‰¦*í±WŠº¸«gkuqV«Š¸b­ü±WUÂ˜««Š¸ûâ®ëŠ¸ŒUÔ¦*áŠºƒv*İqV«…]Q]¶*í±WŒUÕÅ]_UÄxâ­|±VñVñVºâ®&˜«UÅ]Š¶F*ÖØ«*ìUØ«tÅZ#u1Wâ®opöÅZ®*ØÅ]ŠµÓklU¼UØ«[b®#u1Wb­ƒ\U­±Wb­uÅ[¦*ï–*êb­Ôb­Pâ­‘ŠµŠ»q5Å\1WSqÅZ¦*İ1VéŠ»kv*Ù8«†*Öø«·8«©Š»lU­±WSn”ÅZÅ]ÛlUÕÅ[«‰«©Š´OlU±Š¸šb®â®>Ø«±V©Š»o§LU®¸«±Vê)ŠµŠº¸«*Ø8«Db®¡Å]SŠ»v*à1VéŠ»j˜«}1Wb®Øb®ëŠ´qWoŠ¸Šâ­ŒUÔ«¶Å]¶*ìUªŒUØUÇº˜UÛàVëãŠ»jáW{àWWqjâ­Tb­òÅ]Ëw\U¬UØ«¾x«<U­±WmŠ·òÅ]óÅZÅ[®*áŠ»lU¡íŠ»hø«uÅ]Š»v*ØÅZ¥qWR˜«ø«±V*êœUÀÔWoq÷Â®®hâ­ŒUÕğÅ]\U +Š·Zb®®*ÕiŠâ—|±V«Š¶1Wb®ØuÅ[åŠµ\U¼Uªb­b®Å]Š»sŠº¸«¹â†ÁÅ-‘Šµ¶*ê×u)Šµ×v*íñVëŠ»åŠ»vø«©Š¸*ì
ìUŞØ«€«·Å]óÅ]LUºxâ®ÅZ­1V¾x«,UÔÅ]LUØ«øUØ¬*ßL
ĞUİp+x«±Wb®Å]\UØ«ªkˆí]…ŒRê`Wb®ëŠ»uN*ìUÛâ®>ø«±WWpßu1V±WU¯vØØ¡¼RìU¬UÇpZtéŠ»:˜¥ÇpÅZ#¾(o|RíñWR¸«±C[b—aC¶À®¥qK©Šº˜«±Cºt8¥İqVéŠ´1W|UÆ˜¡ÔÅ-mŠº£4N*İ0«ºàV¶«u´F*ìU¼U¬PìUªâ–ª1TìœTÓV3#Õ‹f	nR¥\.*Ê¬#áÙ™³QFdĞİqWb†±V‰ÅZ8ªÓŠVŸlUM(QlU&*Ä¼äÄD”ÜT×îÈ”0ºW¦E-A¡ëŠ®/A×S­\TU¾[Pbªu=†*ãŠ¹Xâ«…¦*ßÙû=qVÃƒ¹ß[Rk¶Ø«F›ª1VˆPâ®
:Uºšâ«•kŠ·P>x«D†ÜŒUiBzâ«J“¸ÅW(§}±W¡¦*äéÇÃ^V«^øªÒ«ßZ(GÅÓ]NXªÓU´Å]ÓzuÅ\‚Ÿk¦*¹” Iú ÅTÊ§ïÅ[t'qŠ¶±ÔuÅWztëŠ¸Ã§†*íÈ÷ì)Š­	¾ıqVÂòğÅ]öv;ŒUpV£¾*´
Ö˜«Qÿ ®*º¬v;â­n®*ïJ¦¸«|Â{U¢µ;â®"£Ôâ­qºâ­UÚ˜«©½z
¹Tu;â­‘OˆôÂ®QûG¡À­/–*İV›aV¾Ûkˆ®Ø«m_ÙéŠ¸üJëL
´| UxhŠ
uÂ«yWnøp xáVŠ¶*»jüUåZ*ÃğÂ­˜ÂĞàU¼ˆßjb®­:â«iÌb®áÛ\pióÅ\ÌÄm×XÈ ß©ğÂ®	AOÇiƒR°+‡Ã±éŠ«0 môaU:ĞSéß·P7$×
¹ßb:â«xü5À®­AïŠ¯ähköqW/6Â­ÇA·İVó¯†İ¼qVØ´Û¾*åeCÊ•¯L*ĞMëŠ®äÜ@¦ØªÊ
Öû¸«¾ìUpZ*±»â­Ô¾*Ûî #§|UmGİß]Zûâ«Yv-ãŠ¸İÿ UÊ˜‘ŠµÇ‘Å[eÜ¨8ªò“òÅVÆı1UÈ[¨û±VÀñÂ­ñîİğ*™W4ëŠª
µŠW5;˜¡MH=(F^J­(0*ÆgéÔb®]÷#
¯ï×ßµ·Ëpeï×iÀƒŠ´•¥LUuÅV¶ûUºûb­ £mŠ¶I¯¹ëŠ´^§½:|ñW2ºñWä*wÅZÄõÅU	íŠ©ŠÏCŠµJì>üUwN¸ªÖJøªå©iJb–ëLP°lÕ#¸«;rb†‚“Š·Z
UÌ	qW(`Ájø«t¯Ä{â­®õú1V¸Pb®LR¼¥7ÅYõÅ[bOÃZâ«BşÍ7«˜7Â®áÅWWuHïß¦YÏ®lÔR›×ÇpSÔ½±UÇcí…Z4cNÇ¶*´F b­´­*ê
{øŒUÜÈê7Å\xÒ½1WcÒŸ,UÉ~øªĞ:ı¬UÕ>N*¹I}›¨«_QÓiZ„ïL
Ø5é…Wš·jSYMñWßpO˜««á¸>Ø«DíŠ»jõß¸ì0«aü#¦*Ğˆbªà/âØªƒ1aŠ¯²ò"˜ªÚ}5ïŠ·¶Àu>Ø«Š‘]öÅW ßZMûñUÄ²nzU³ ;öùâ­)-Š¸S·ßŠ»jñU§n£lUqPAÅW$€‚v>ªÄ4êi\U¾¸ªÒ
ÕkQßi}ëŠ´Š|*åØ1VÀP1W ÅZ'jw8«k×‘İGlPÓ.æ1J DuŞƒYÂ›Ø«”Ÿm±VÕkŞ˜ªÖ¦ıñUŠÔş8ª¢7%éŠ¸­IÅ\6¦*İ>ìU¢ éŠ´ÀœUÄ‚Å[P £~¬Uµ¡?3Üb«ÏÂiÛÛh¨Sñu=±Ws'åŠ¶XØ«”m¾*åZøªãA_Ujšn1UÈ|+QÛh8úqW1©Å[CÔUêZ<¬b'Ã¯Ó’ŠSÄ%U\UPUpolUpÅ\1Bêb—b®íŠ¸qVúb®§†*î˜««\PáLUÛRí±Vı±C©Š·LRî˜¡Ø¥­±C±V«í…[[®(hRŞ(o¦)j˜¡Ûb–ñWaWT`WTb®ëŠ»¦*ãLUªwÅ[®*ìUÕÅ]Šº¸««áŠ»–(ukŠ]LUÄWlb®ÅZ>Ø«t=ñV©Š¶)Š´*Ş*ãŠº¸«ø«€Å]LUÇp«¾x«¶Å[>ÃklUÔÅ[Ø«*êøâ®Û¶*ìU¢	ïŠ¸Wov*Õ1W…]P0+`Ø«,UªœU°F*Ñu)Š´}ñVğ«TÀ­†Å]\UÕÅ]Q…]Ë»ohUØ«¹SlUŞø«‹UÜ‰ßp$œUvø«Å]×k|UÔÅ\@Å\;b­ò8«¶Å]Š´*ŞØ«Co¦*ï–*áŠ»v*ìU°1V±WlU®˜«‰Å[u+Š¸UØ«†İN*îX«U5Å[Çn¸«X«{â®'Çu1W|±V±WSu1WSlûb­oŠ·\UØ«X«db® b®éŠ·ŠµLUØ«±WR˜«‡LU¾˜«¾x«X«,U­Æ*İ<qVÍØ«[ŒU qWb­â®Ûh0Å]òÅ[©Å]¾*â¤â®À®®ø«uğÅZup«©ßo|
ê×l*êb­ôÅZ#©ñ¦çlâ«±VëáŠµŠ¸b­ÔSÿ×ë™$8U½±WŠ¸œUÕÅ]\UªáWŠ»uqWWolUªb®Å]]Q…]Š¸â®ÅZ¦*ß\U¬U³ŠµŠ»v*êUİ1Wb®¦*î˜«±Wb­ƒŠ»v*êb®Å]íŠµ\UØ«±Wb®Å]Šº¸«GpÅ[«X«±Wb®ÛvØ«±WSv*ìUÕÅ]¾*Ö*ØÅ]\UÇk~Ø«±WWuqWWu<1V±WSov*ìUØ«x«X«CouqV¾X«}zâ­Sn˜«X«©Š»v*í*êb®8«±W|±WR˜«±VñV«Š»híŠ»oq«†*Ù8«UÅ]Š»h×po|U qWb­â®¦*áŠ·òÅ\qV©Šº˜««¾*ìUØ«±WŠ¶wÅZ¡Å\WÇh.(lRÙÅVœU°qWb®¦*àiŠº¸««LUİqWtÅZÅ]¾*ØÅ]LUÔÅ]LUÄWwLU£Š»ulUØ«cu1WWuqWUÃuqVëßqÅVâ®¯¶*î¸«©Š´qVöÅ[Å]Šº¸«±V«Šº£Z\¾*»¯\UÔ8«©Š¸â­Šº˜«±WWj¸«uñÅ]Óv*ÕqWb®Å]LUÛb®Å]SŠ¸b®Å[«±W\UÇhšuÁjÒ°8Úº§
®v*êb­áŠµOU³¶*êø«U«©Š¸UØ«T8«tÅZÅ[®*í±WmŠµŠºƒlâ­Tb­×uN*êâ®Å]LUØ«B‡o¯LUÔÅZéŠ·SŠµŠ¸×q«Cn˜«t>8«T#v*ìUÕÂ®éZ©Â­Óº˜««ŠµŠ·Š»uqWb®Øâ­áWğ+«Šº¸«TÅ[Å]Š»ko|UØ«X««Š´1Vë…[À­Su@Å]_UÕÅ]LUÔÅZocŠº˜«±W|±Wo…\0+±WSj˜«u¦*âqV«Š·AŠº£w\UÔÅ]LUÇ
µ[¦*êb­Ópv*Ö*ìUÇÛv*Ö*êU³íŠ­ß¾\j˜«©Š·¾*êâ®®*êâ®ÅZÅ[«ˆÅ]OU­ñWT¸«uÅ]ïŠº¸«DUÔ#¾*İ1V¨qVÆØ««íŠºµÅZ®*êâ®®*êı8«°«¹
Şø««Š»lUª*İFuqWb­SwÏ
º¸«X¼(up%¾˜««íŠµ×j¸¡ºâ—b­öÅZÅZê}±VéŠº˜«TÅ]LU½é…\v*Õ1VşX«TÅ]LUØ«uÂ®ß¦)v(p'¸“Š¸ŒUÛâ†ñV«\RìU¬PŞ)vø¡­ñK±C©Š¸b—±V±WWÇvÃpß:•Å-â®Å#Ãk|UÔÅZ8«u¦)vhàWoŠ¸â†½ñWW­b)Š¥Z¤¼Pås4ú]:“LÃmU¶A$£ç’”2¸€fhjV¡Â­â­b®ëŠÓk|U£×ZØ¥I±UÅiqV+æöã
÷øêÈ”0²+‘JÒü†çlUei¶*ßjuÅ\¼”ïŠ¹·ßh¯ò±V‹)Š¶¢»â«šµ«ªKo¶*Ñ ëøb­U³Æ¾ø«ˆºâ­5§êÅ\6;uÅ\Z•ñÅ[ûCßvô¨è1Wr$SkÔÅ[8«š€ûâ­Õ¦*êP×¦*â<*î nqW=ºb®¸ëŠ´AcLUr€6ëŠ¶:×¶*Û6ÔÅVĞŠSl©ÅZTxŒUqf§ğÅ\_—^¸ª™ÜüF‡Ãl{WçŠ®FÕëŠ®olU`B+øâ­ƒN1V¸£s|ºb®š½±VÛb«˜PûœUr9U n*¤ıoÛğ«DäŸlUÀlGLU±É1WP·}ñVˆ ô4Å[
A©ú1W‡s÷b«Wã«`·/
mŠ¸‚Â§®*´»¯p«†ûàWPË
´îwÅ[$¶ã·^[â­}­—¶w 	­qW'Øb«v_™ïŠ¸ëß¸;Ò´ï…W)f]úWlUÀŠ´T1øM)Š¸P–*²‡r;`VÑ‰_lUsRƒ
º›LUÀ«l:Øªòè0«\
ß pªÒÔø{b«ŒŠ)¹À®â()Š´G·Ó…]BGl
´¡cáŠ¯ƒl*ÙbÀ®$ª+…V*ÔÔŒ
ÙPÆ¦£
´T{í[ °«\kÓìàW Nä×¶i­Ol
ßM*¹øJ‘íŠ´t;b«¨?g Â­+íJõÅ[ÃZh:Ó\!íŠµJ
Ó±]°«‰*=±W*ñÜ¸ªå^ã´Aï…\Ê Š®äv¨¯¾*Õ(j	¯†qJâ­V»öÂ­"0¯-ñUÁZ›Š[„õÅ\@ šâ®a]»œ*Ú»!âw¯|
à¢†½*°1LUq;P½qU§q¾*à\Uqn#qAŠ­¨ï¶*â”ØõğÅW„ï÷â«O¶çu)¶*¹Z‡lUÂ…·Ûi¾×†*Ñ©ñÅ[ õëLUÀ‚j?^*Ñ*7ëŠ¶«^;â­òâAQ¸®*°ì ;b«¸
b­î·†*ÓoŠ¬ï\U}ONµÅ[2PòïŠ¬&¦§lU AbªªûáV¶ï\UÌ5¯ÓŠµÇ¸Ú˜«ŸíUqUÄ€9ı±U¤´Å[oƒìïŠ¬rA©ï÷â«jûÆ*ît£~¼UÜ«ĞuÅZ4NpSÇl*ài±ëŠ®¿¾*íÚ´§ÓŠ´GˆÅ[@xò=+Š­ 9ğp;U²mñWrÅ[$Pb­Pxâ­- Û®*ŞÄo×rŸp+LÛ
»v«D³Š¸FkC¸ÅWôÅZQCQR¸«™ÇEÅ\+Û®*¸“¸8«¹/'¯ßŠ­R}*½·§°ÅV2TĞb­ñ1Š·ÑŠ»í¶«D)>Å\IíÓp;Pƒ¶*¹H¨+Š´w5ªÑV&¸«j¡ºî|qVãjV†˜«Eùî:â«TûF˜ªà´íŠ·Ôm±ÅV]Î*áŠ¯TC\U¦!E*âÄÓ–ÃÇr¡b ëŠµ×çŠ´°»*°©è7ªşT b®hI8ªÚò4ß^IèqW2-*Ğ¦*»®õéíŠ¸B¸«tøj	®*×Q¾*¹G¨Ã¡?<U®EvíŠ¶	ïŠ¶„nNØ«f«½1VÉAÀâ­söÅ[¥zbªa¨qVúí÷âªˆ¤¶8«Ó<¬Ü|_#úÛ%§É’UeÛT\Ux#
¶†n˜«x«§LU®¸«{ôÅ[âN*êmŠ¸U¾8¡Àb®­1K¶8«]1VÅqCGu1VéãŠ]Š»up¡º`K_<PİkÓ»
´0!¼UØ«°¥ÔÀ®Å]Zb­Tb®®(lb–ñV±WS¾*İqWR¸«¸ŒUÔÅPb–è1Wb­×klb®«]1Vê1V«Šº¢¸«x««Š·Š»çŠº˜«X«tÅZéŠ»çŠ»lUºøb®u1V©Š·LUÔÅ]°ÛwLU¬Uºb­b®8UÕÕÅ[Å]òÅZÅ[ÅZaVÅ0+ˆÅ]L*Õp+x«ºâ­LU¢+Šº‚˜«CÛoov*áŠ»hUÕ8«x«Å]Zâ­×j»b®ëŠ»q8«cu7Å]AŠµÓv*î˜«ªqWn1VÆ*ï–*íñWb­Óv*êb®¥1U¸«{â®Å]óÅ[q«X«‡Zb­â®§*ìUªûb­œUÀâ®®*àqWWvø«ºŒUÔÅ]Š»®*Ö*Ø8«b˜«·ÅZ¦*Öøªí»â­uÅ]Š·Š·Šµ]±WUÕ¦*í*ÑÛuqVñVñV†*áŠ¶=°+G
»çŠº”Å[Ûhâ­Ÿ|UÕ«¶í]…]\UØÛb®0«°+«…]¶*ê×·\Uc7lU¥â*¾¸«°«°+½ñVéŠ_ÿĞë$‡*Ø8«*êâ­b­ìp+¨0«ºb®­qV©Š»çŠ»uqWWklUºUÄxb®8«X«ºb­œU¬UÕÅ\qVñV±Wb®®*ìUªâ®¦*ãŠ»ov*êâ®®*êâ®Å]Šº¸«±Wb­b®Å]Š»ok|UØ«±Wb®Å]Š»v*ÑÅ[ëŠ»v*Ö*Ş*Ö*Ş*×Ëv*Ş*Ö*àqWqWWuqWb®4Å]Š·\Uªâ®Å]ŠµŠ·LUÃoj˜«±Wb®8UØ«[`Wb­õÅZéŠ¶1VÎ*´Wv*ìUØ«xªŞ¸«x««Š»lUÕÅ]ÈUØ«©Š¸â®b®Å]Çv*Şk¾v*ì*ê`WR˜«ªqWWolUİqV±V±WoŠ»‰Å]ôâ®®*áŠ·ŠµòÅ]¶*êŒU¾¸«]1Wb­â­b®Å[¡ÅZßuqVëŠ»j§uO\U¼UªwÅWmŠ­Å\IíŠ·Zâ­b®ßlb®ÅZÅ[¦Ø«±Wb­|±VÀÅ[ÅZ'pÅ]Š·¾*Ö*êØ«†*ï–*êÓv*Ö*âqWoŠ»u)Š·Šµ\Uİ1VëŠ´7Å]QŠº¸««Šº´Å]Zâ­â®®*ÕqWb®ùâ®Å]Óp­1W qWb­ŒU­*±ÖŸg BC‘|AR)\šLUÔ8«©Šº”ÅZúqVö«¶Å]LUÛb­Ôb­Wq'plâ«I®İñVñWb®Å]AŠ¸U°1V©Šº˜«©ŠµŠ·Zb®¨Å]Æ»â®®*Öø««Š·Šº˜«±Wl1WW
»®wËqÂ® À®ÅZÅ[¦*êb­qíŠ·Çq«X«}qV‰ğÅ[ßj‡ov*ìU¡LU±Š¸â­â­b®ÅZ#uqW…]L
êâ®UÕğÀ®§*Ş*ÖÃv*íñWb­â­nqWb­×ku1Wb®®*âqWb®Å]LUØ«‰8«[œU¼UØ«±V¶Å[«,UªœUª˜«}qWWv*íñWUØ«©Š»q«X«€ñÅ[¦*ÑÅ]Š»v*êb­ïŠ»kv*êb® b­ŒU³LU®˜«†*â<1WoŠ»|Uİ±V©ãŠ»¦*Ş*êxâ­mŠ·¶*ìUiÅ[¯†*î^8««\*ÙÀ­oŠ·LUØ«X«±WUÔÂ­àWaWmÛ¸Wkv*êb®ùb®ßu*íñWUÀUÔÅ]Zb®ë¾*ìUÀb®éŠ´*İ)Š»u+Šº˜¡Ô¦º¸Ø«±WWj¸Pİp+G
ZÅàK«Š»uqVºb®Ş˜«·Å÷Å.¸«‰Å]Š»;lRï–*ìUÔÅíŠ­««Š\(u1Kcßu(u)hâ­UÍã×p8ªÇ"˜ªA¬>Ô÷Ê2™Å*QC·J˜ÌÑº<<¥ärÌbË2UfcZ§!×p â®4ÅZ#8Ó´qU¤xb«*±±BÁªƒœUŠyÇku?å‰C4nô>9©Š}8«e~£Xõ«EÉéÓw·|Uº¿µŠ¹@½Î*¹ÜSp>ñUÔ~!¶*´zb®U=?UÔßÜb­¾ı6«KïŠ¶ÔªÓÓcôb«yéÛ®*½\A®*²½*â)Ór±;b­•4Å\«ûDb®<AÜâ®×l·nøªŞªò@é\UjšvÅ[ä:œU¦ØTb­¦ß,Uyzv¦*ÚÔ
Ö¸ªÖp¶*×!ö€ÅZ;tûñVÉ tÛh
ŒUÄ±ÅZèUQViLUiÚ¼{b«U÷ğÅ[&›Ö§l­wïŠ­	SV?N*´Ï½:b­©?g·µhÇ|*ãQ±ûñUÑ*r£/rp*Úƒ¦l!§ëÅZß©Üb­’IÛiª	À­qãñTáVÕ”U§øÎÕÅ\ª5­qW*SÄ{b­³×[·sQà0+|°«J½ÆşØ«‰:U±S·l
¸PÎøUÔ>>ø«Ai×¾wŠ¸UÛ¸«K%>^¾Ek…VÑ©óÀ®U`~§l$7c…]°Ş8ªà•è<p+M¾ß†*´©úpªåoLUz]0*šÑv¦ø«bƒ§\Uco¶_N"«B„ûb«Š“±Å\Í]—¨ëŠ´H5ÅVî»·LUº7ÿ 1Š´<¦*¸1À{â­ª‚hE+ßZT'S\
êp4ìzUº•ë°Â®â |=úâ­qà9Š¶+&ãq ØÆ*Ğ“jS|U¡İ±VëÒ˜«‹Sj`WH€šœ*îC·áŠ¸ÔøŒU¥AôàUÄ0ëÛ
µÓ¯LUmH©ŒU£É·ÅW&ã}±UéT«·;šÔû`WÔë…ZaUß¶*ĞnÃõm[.(†*í«…[Zúâ­ÉñR¸ 
šöÅWTv=p«EcŠ¬zÆ½<0+`˜şXªÒÿ |Up;øûb­…R+MñU§síãŠ® ¨Ş˜ªÆ–‡Àâ«˜òê)Š­ÇqŠ¶ ìzW\I^ØU KR¸¯¦˜«]MØªõU=Åp«A¸ì:àVÎÛÓ
µÎ»7n˜Á»vÅVõñöÅ\Ñ‘¸Å\(¤‡Ûl9=<qW6ı6¦*Ğ©ØíLU²¥*FçÛ
¶¬kÄıø«ŠqV–§aŠ¶ôSâqVÔ´)òÅZjµ	Û¸ô¡í…V¡¸pUãRwì0«[˜«{WÇÇYVSíŠ® ‘Q±Å\¬XSğÅ[U¦*ÓËŠ·F )û5Å[äTøÓÇ^JÌ)Ñ€Ú˜ª•
íúñW1¡ş8«Eë°Øâ®ã¾Ø«m°ß¯|U°À˜ªôPıñU²GCA°ÅV’+ğÔÓ¾*ãV<ˆ|U²ÜA ùb­P×h5z×UpÜø{b®¥i]Î*´ÆàïŠ®F Ôb­(,b«Š¨4=qVˆ#`6Å[jSZ§‰ ïòÅ\I?lUºòß¿†*Ñ$íµqVƒíŠ¯£úb­#åŠ¹ Å\´&‡aŠ·ğšŒUa]öéŠ¸U·İ<1U½6û±VÂÓrh*Õ(vÚ¸ªêßZ»ššb­3wÅWÆzâ®e
v5Å[S¿")Š¶XÔ¹éŠ¬¥:b­Ò1W6ç|UmW Å\\ü©Š®cAÚ§p5¥*½hµïŠ­ ÇnØªş›UOv«”^˜«f”â«iN¦ âª±ğ^‚µÅWW|Uk=M{â«K×\*Õ'h.õ®*Ø ÷ÅUPí÷b¯Jò®ööëúòQJ~´É*ªâªŠqUØªá\Uºb­â­ŒUÔ®*ìPíñK*àÃ®*î¸¡ßN)nƒko;åŠ\1Wb‡oŠ[ëŠ»v8Œ	v*êS;
]ßqÛ´wÅ]LU±Š¶1Wtßkp Å[Å\1WV¸«©ŠSŠ\IÅZ«uÅ]AŠº˜¡Àb–şx«±VÍ;â­b®««Š»¾*ã\UİFø«©Š·P1WWhâ­â­b®¦*ìUÛ÷Å]\UİqW*İqÅ]Š»|U®½±WñVéŠ·íŠµÓq«‰Å]Š¸ŒU¼U¬*Öq8«x«¶Å[Å\6Å]\U£íŠ·¸Å]\UØ«±WŠ·Çpb®¥qV©Š»u1WSpÅ\qWb®«±WSn£¡«ˆ¦*ÕqWSÇu1VÀ«qV®*Ş*êâ®Å]_UÕ'wN¸«·Å]¾*ßÏkv*êâ®ëŠ»u{b®®*Şø«±V±WtÅ[Çn£j¸«dâ­SokuqWb®Å[Ûp=±WUÃk¶*î˜«±Wb­‘Š»koq#kwËw\UÀb­üñWŠ¸tÅ]Zâ®
qVéŠµAŠ¸UØ«±Wb­î1Wb­
Wl\1V°«©Š¸â®À®®p°+¸WZ‡sŠ®Û®*êâ®Å[Å]ALUÿÑë¹48b®íŠ¸Œ
ì*ì
á…\qWb®Å]ŠµŠ·Šº•Å\*â1Wb®Å]×kv*Ş*ÑÅ\qWoŠ»v*Ö*Ş*ìU¬U¼U¬UºWu1Wb­UØ«±Wb®Å[ÅZ8«±Wb®ÅZÅ[§|UØ«†*ìUÕÅ]LUØ«‰Å\1V·Å[ùâ®8«_,U±ŠµLU¾˜«X««Š»q«¨qV·Å[«X«tÂ­À­(8ªìUØ«±Wb­SuqWTb­œUØ«…1V°«uÀ­Wh×®*á\U±Š·Šº˜«UÅ[â®Å]Zb­WuqWWuqWUÃou1V±V*Ş*ìUªb®Å]Š·\U®¸«©Š»ovø«‡¾*êâ®¦*ìUØ«ŠŒU¯–*íñV÷ÅZÅWS
´Nj¸«ºâ­Óu1WtÅ\}ñV©Š»¦*ØÅ]ÓukŠµŠº¸«f˜««Š»v*ìUÕÅZ®*ß¶*êb®éŠ´IÅ]Zb®©ïŠº¸«uÅ\qV*êb­Ów!ŠµQŠ·Ókn¸«[â®¦*ìUØ«†*êÓn¸«ˆÅ]LUªb®Å[ íŠ»k|UÕ'vØ«ºb­ü±Vˆ8«†ıqVéŠµòÅ]Š·ŠµÓvØ«¹Uºâ­u«±Wb­â®8«±Wb­b«r4•õ9$;n¸«‰«Å]CŠº˜«±W…[À­b®¦*â1Wb®©Å]ÓuqWU£SŠ¸Š¶vÅ\	Å]Š»åŠ»oj¸«±WWj¸«uÅ\1WSu@Å]ŠµŠ·¶j˜ºâ­b®Å[ÅZßlœUªâ­’qV©ãŠ»uqVñWb­WÃn§uqWWv*êœUÜqWV¸«[b®®*ìUØ«±WUÕ©Å]Š»ko–*êœUØ«°««Z­1Vúâ®éÓuqWb­Š¸ƒŠµLUÄSj¸«ºâ­â­ŠwÅVšb­†Å]Jb®¨Å]Š·¶*Ö*êâ®®(up¥Õ8Çv*í±WPb­Óhâ®Â®Ûº´Å[;â­}8«·qWWj˜«tÂ®º‡wLUİqVÅ1WU£NØ«»b­SÃq|±VÁñÅZ$â®Å[¯†*Õ1Wb­Óv*ìUØ«±WSv*ïlU­ñWŠº˜«}1Wb®Å\qWmŠ»vX 1290 ; N uni27F5 ; G 3913
U 10230 ; WX 1290 ; N uni27F6 ; G 3914
U 10231 ; WX 1290 ; N uni27F7 ; G 3915
U 10232 ; WX 1290 ; N uni27F8 ; G 3916
U 10233 ; WX 1290 ; N uni27F9 ; G 3917
U 10234 ; WX 1290 ; N uni27FA ; G 3918
U 10235 ; WX 1290 ; N uni27FB ; G 3919
U 10236 ; WX 1290 ; N uni27FC ; G 3920
U 10237 ; WX 1290 ; N uni27FD ; G 3921
U 10238 ; WX 1290 ; N uni27FE ; G 3922
U 10239 ; WX 1290 ; N uni27FF ; G 3923
U 10240 ; WX 659 ; N uni2800 ; G 3924
U 10241 ; WX 659 ; N uni2801 ; G 3925
U 10242 ; WX 659 ; N uni2802 ; G 3926
U 10243 ; WX 659 ; N uni2803 ; G 3927
U 10244 ; WX 659 ; N uni2804 ; G 3928
U 10245 ; WX 659 ; N uni2805 ; G 3929
U 10246 ; WX 659 ; N uni2806 ; G 3930
U 10247 ; WX 659 ; N uni2807 ; G 3931
U 10248 ; WX 659 ; N uni2808 ; G 3932
U 10249 ; WX 659 ; N uni2809 ; G 3933
U 10250 ; WX 659 ; N uni280A ; G 3934
U 10251 ; WX 659 ; N uni280B ; G 3935
U 10252 ; WX 659 ; N uni280C ; G 3936
U 10253 ; WX 659 ; N uni280D ; G 3937
U 10254 ; WX 659 ; N uni280E ; G 3938
U 10255 ; WX 659 ; N uni280F ; G 3939
U 10256 ; WX 659 ; N uni2810 ; G 3940
U 10257 ; WX 659 ; N uni2811 ; G 3941
U 10258 ; WX 659 ; N uni2812 ; G 3942
U 10259 ; WX 659 ; N uni2813 ; G 3943
U 10260 ; WX 659 ; N uni2814 ; G 3944
U 10261 ; WX 659 ; N uni2815 ; G 3945
U 10262 ; WX 659 ; N uni2816 ; G 3946
U 10263 ; WX 659 ; N uni2817 ; G 3947
U 10264 ; WX 659 ; N uni2818 ; G 3948
U 10265 ; WX 659 ; N uni2819 ; G 3949
U 10266 ; WX 659 ; N uni281A ; G 3950
U 10267 ; WX 659 ; N uni281B ; G 3951
U 10268 ; WX 659 ; N uni281C ; G 3952
U 10269 ; WX 659 ; N uni281D ; G 3953
U 10270 ; WX 659 ; N uni281E ; G 3954
U 10271 ; WX 659 ; N uni281F ; G 3955
U 10272 ; WX 659 ; N uni2820 ; G 3956
U 10273 ; WX 659 ; N uni2821 ; G 3957
U 10274 ; WX 659 ; N uni2822 ; G 3958
U 10275 ; WX 659 ; N uni2823 ; G 3959
U 10276 ; WX 659 ; N uni2824 ; G 3960
U 10277 ; WX 659 ; N uni2825 ; G 3961
U 10278 ; WX 659 ; N uni2826 ; G 3962
U 10279 ; WX 659 ; N uni2827 ; G 3963
U 10280 ; WX 659 ; N uni2828 ; G 3964
U 10281 ; WX 659 ; N uni2829 ; G 3965
U 10282 ; WX 659 ; N uni282A ; G 3966
U 10283 ; WX 659 ; N uni282B ; G 3967
U 10284 ; WX 659 ; N uni282C ; G 3968
U 10285 ; WX 659 ; N uni282D ; G 3969
U 10286 ; WX 659 ; N uni282E ; G 3970
U 10287 ; WX 659 ; N uni282F ; G 3971
U 10288 ; WX 659 ; N uni2830 ; G 3972
U 10289 ; WX 659 ; N uni2831 ; G 3973
U 10290 ; WX 659 ; N uni2832 ; G 3974
U 10291 ; WX 659 ; N uni2833 ; G 3975
U 10292 ; WX 659 ; N uni2834 