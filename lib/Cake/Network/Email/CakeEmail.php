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
 * attachment compatibility with outlo3���B�s���`'\������Q `?�%��rO��FU�p싞�}�$  r�T�}��'��QmW�\yP���:=�#� �`U�/�n�СC�5����V��T�~a��'�_��'v��Xn����� �7�S{�~��SU%��)�/�qN ��T��vK��Z���6�[�)ꌨ=���W�n�Ӻu�i��Ն[6n47���Z�� �]�wi��A�X�k�+��~��͊�����/*� ��Ʉ���Ӣ}�m۶i���LOC�����~�~�믿�2��T����U�/����8�� �7�7ź��ƚ�H �]�Sߏ��7�p7id�9�ES�2�E�� ����)/��r���\����_znz��{�bٕnþ~���ˢ�.jBǎT���}��83�Q��Ru��6��X����D ;��J���{缇��ľ�g�/�L  �g�Kf͝;7�c�>�ك>���[���/E�oʿ:o޼����C9d�h����G�v�ң_����ɶQkb}�K�;�F��  ص���buW�V"+ݩmv쏆ƾ�!q  �3CD�Ɣ)S���7ꄞ={����{*W�Ae��*�SO=5;�Qs�13E��> =�%݆�ӱ�Z��%�L�/׈�����q� @��R�:�P�E�*���r��c�.���Jq  �;t��C=��h����SN9��\�i���+(+k�^�Z���O�����tꩧ�"�����}�'cЭ�H{�TW�k�}i��5B2 �D{��;]�'E}@"�Wz"�I����>q  �8t��=�ܓ�����:�.�;��PZ�
��Q/�{�s�O�z4j��g�-�)����|4��Q�����j}HWL�*��� �,{���ƾ���?�>�������c�tw��  v�:��-���&W�l�^��p�����C�[���/���[���d�_��J4@�� ���JO��n�8 W������~�ɭ�Z���%��X׊��					throw new SocketException(__d('cake_dev', 'File not found: "%s"', $fileName));
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
w\U�8����u1V�\U��X�x��V����n�uF*�LUnuN*�vup+X�u�]LU��Wm��v��T®�����������b�b�V��x��V�{b���*�Uث�WR��X�c�k[�C�]Z�W`Wm��u+��]�]���
��KG|(u<p+�LR�*�*��&���aWV����.8�[�w,U��Z�*�qCU`WW:��ء��)uqV���LU����ثGS��*��i�\�1r�q@ME{eL�}=�e��'˙b�b��Z�-8�M��5�VUE�P��#�3���^G"P�(I�|�[���⭓J/|UL�J��N*��8���b��/��x�� b�H�8����p4��ZR�w�>G�*�r��SK怖�'4�b���r|eWF�<e\m��2�-���ǌ�of�T�xʶ�� ���2�����W5�=*<q�*еa���ǌ�bȾ�ǌ��fX
��2�>���WF=k�U�di��2�>��ǌ��mA��ǌ��՗����V�B?e*f�׹��.������4���h�|B�R��c\|B�ض�}�#��G�5��+KRA�T�
��
�o��?,!ZZ#���̔�:��WaCN��t�5ٱU6m��]���«��߱�\iA�b�;o�WN�UJrݫ�_^u4V;{�xB8[�S�1�pp�l4����Ǆ-4�\W�5>x��p��
��o|<!i�$��f��p��s������w)�G?~����P�O�<!x\~������$��T�}��K���h�烄"���w,GӇ�&�3 y����E6L�����M8z�I@���E-C-~�W燅ix�F[n�+K�����-s������A<�{np���n��~<!iw)�^M���Zlz����ǅi�Ԯ��~<!����6�����j�֜����Kd�~�<!4����I��8�\Uh`z�["��v*���T�\U��N�b�j��0���|p%3OP�Oт����`���l��q��W� ���_����?,h/�US�>2<pP_�#\=��3�QNG"�᠓��q��a����.z�4Ƃ��n[�1��9w�N�>8�O�l� }���/�W�dm�B?xB<r����v����˗�'b�`�	��tos�w4�����@����/�A�y9xB�`�Ը��I����k�7sQ��/�
Ԓ����Ǆ/�
�-�@,pp���o$��,~�xB����+O����_��p���Ƃ|r���m!-צ�(/���.���h19��J�z�9�"ȼ{>��D��X֤}�X:�Up�7z���*�@H=��+D-v�[w;��V���5��V��iN�����
U��U��(�#e��6nd�Ȍ���e�W}fE;�#���^2�^Oݏ߃��|Bѹ��c���h�o�2�7"<w��`�eq����r_��+~�?���?�	�.�������x������i��_����휏�"�!l�U���q��S��r���n���_�:����"�!h��IO����_�:��g8�T/�Z�3t��~�E|B��.��8�R+�εvN�p~R+��W�R�}8�R+���ܚRF'�"�!\5;��<�0�R+�?ҷB�o��_��g����"�!lk7`}�����W�.�5x�f4���H��W^��v���"�!CM�]L�Y��-��D�bgka,H�d��\��B��;x�V̌:�x��`U��F*��]�V��ۦs M\UBj���B���Uc���ZU�����@�p�'�.{몐�N<K��P�?�~�x��6n�:�lx��+M�Ҋ��<bظ��d4ǉ|b��W~�v���	����8�|b�Թ�v��Ŷ���x�h�J՞����i����Fs��|eE����(Gq�������9�x��7���*d�я��Ynۤ�-1kֻ�!�6�1]�݁_SO��^.������z�_���O���7c����ľ3Z�4%����ď���N�lx��+^��s�q�_L��Nd�ľ1^�W�P��Y���y,QLm��Z!&!wuZ��Bگp*1V��@�]ɗ�t��[S]ɦ*�4��Jk��o���*�z��@����-�Ĩ<�Ǣ�3W��y��N����"~�1��o�C%+�c���
��#� &>2�.1IM�����<�"2��|� �2Ma������c�/���������H�����_�V?����'�o�F���3�>2�-��c�/��|�^�L|e�Z1���>2�-2�����K�F>2�-�2w�q��-��5c��0��๼�N����o���C��O��y������hy���|d�.� ����
�u�p�T`�i�⧩�Yr@�q'���"�ir�z�mZU�!�� v*���*�_φ*�5튻��m���1UHO& �
�� .��������<2J����*�U� �[�*�1V�ث�1Wb��W�*�*�qCu�.�qWUء��#�b���p8��Wb���-�k
�]_U��KU8Uծvثx��*�*���W|�W|�WSk劷LU��{b��qWW��[��*���]�����;⮭:b�o��p��1K{�oh�*�*�b��[�U��]���*�⮮*��늺�up�U8ث��]J�����v�]��[�aV����]\U�b��*�aV�0+}qV늴qWu�]�����b��*�#v�<U��WSlb��*���b�V�1V�qV�V�VϾ*�*���\F*�b�8�@b���]J���]Z�늻j�o�*�1WWuqWo��p8�x��=1V�㊻�*�xb����]���8�����⮨8��V���������]��\U��WWj��t�]LUث�V銻v*�1Wb�⮦*�<1V�*�0+��\1V��o�����u|1WT�o�ow^��c���U�Sv���uqV�⭜U��­�V��+|���ۮ*�qW���47�]�*�qWb��*�qV�n���*�U�b��\*�1WSq�v*㊵�����Wb���8��V�WSv*�U�lU��v*�qWb�Su*�b��u1WSu1Wb��p8�Gv*�*�*�k�������v*�\U�Sq�]\U��[����kv*��V��[�v*�4�]��C���uqWu�]��uqWWn��X��*�*�b�5튵��[�Z늸U�k�*��n��D��[�\*㊵����x�^ثX�t��]Jb�늺���WR���WSk�lb��8�Tw�\*�qWb�So�*�Wj���*�Uث�*�*�1Wb�b��*�b��v*�U�Uث�*�Zk��s����8�x�T�]LU�b���Z�*�b�;U���b��p�u1WSuqWr��Z�[�Z�[���8�X��V�Wb����7늵\U�UثX�T�[銻v*�EcRF*����*�i��b��]�v*�U�8��*�U�U���]��b��U�U��\*�����Z�*�1WShm���*��*����qV늷\U�p��+T�\F*�b���Z�=1V�#l�U����[�Z銷�uqV��*�8��V�qV�qW\Uث��u�ku{b�'q�]\*�p+�\U�0��+X���]��]����n���1WWv*��Z�[�n�wU�1V�Wu�[�*�1Wq��\1V銴1V�*�qWWw,U��Z�*኷A���u1Wb��*�*�U�o�*�*�b��*�Uث�������Z�qC�)lU��]��Z�*�LU��uqWr1V��⮦*ኻ
���u+��vv��8���\qWSq�]�*��WSolU�:b�V�����A����X�t�\1Wu�Z#�v*�8�{�WT�`Wb��*ئ*�U�U�b�۾*�⮯�*�\*���U��b����v*��kv� F*�b�Ҙ�Gv*�b��]LU�|*����v*�Uث�C�\)v7�Z��.��|R�qWSw�vث�(v)p��®�*���R������]C��v*��b�aV�0+��Z�)uF*�U��]\P�⮦*��������R��;��HW�}=r
��	�:��N1�	&�.b��uF*��Z��]\P��Ua�*��8���U-qV+��= �dJQOL�T���^9u�V]�VÚ�k�O�*��*�@銻�ӊ��LU�I44�WF�Zw���U��WT�lU�׶*�cZ���5�UN���Li�[�����6�b����*��Mhv�[�늴V��*�R7�\v;b����\ �8�� ���b�|銺���*�Lx ���Uf8��'�lU�f�]遶*��=+��1
m�ZE1V�
���V�$��qU�V�+���M��U�I;b�5;�U�{�DWr�a���R:�W*��W +�~ث�}���#|U�����mV�]A��b�>�튯$�*�%v�ثf��\A�*퉮*פ	�U��o�w�1Uޑ#���V����|Uh�v�ZTQ���Mz�}5�E1U� v��V�����W*��1U�(�zb�
�z�x���a =qV����j�?*�T�� lUi]��TؐqU�>�b��qWT�s��0	,M*��S"�~���h6�▜v�[/Z��}��RN�1WH
� SmEA{�Uo]:b��Ě��V*h)tj:��|J�֤v�)���U����)i@��(_NC~��L(;��[G��V�E�LU��1U�r>�U� w�)ԓ��/\U����wSm ߸�����
�����\�A�;�UeF*�u����A���#n�U�bw���&�w�Z-]�*���
S�*�]�qWO��HԮ� v(X�=;b�j�\UQO��ZPT⭓��*�TR�0%�(;aB�B��qK����*эiOՁZjS��V��1K� +@|*���>iJ��� ���ק㊮�J���*��
H�0�k]�%�L*�#c��C`֘�M��O�l��f�U�q]���k�*���\Ui�8�`w=0�{��p4�8�@�ۭ=�U�ۨ�1U"�߷�uAޘ��>[�U����r/U�Q]�⫁�*���u8�M�����>X��>���-�_�}�U���Vʎ���N�5ƻ���J�[]��l��↘6����r�V�I�v��W1�~��^�-�V����^½Fت������mP1W����V�1�qV���*qV�Sj|�UBE;��V5?�h��늮�x׶*�?Q�h�Ǧ*�����)��V����n�a�b���Sq�)q�~�݊��~T�[��pث�5m��]�(n��V�a��Ug�7��WЭ;⭤1�N��V�ߦ*� F��p�Z��TH�T�|6�%5|�*�G�>���v#�+��#�ALU�}�{b�� 
�b��T��^�)�8��ě���U��Oθ�Q%H8�������Z����pJ���[�U�68�ꁷ|Uq޼~ghN*�H5;��}���J�.$��.(XS����	y�d�^1U�n��኷LU�LU�qCX���[p�v(oh{b���U��W;�N*�q$�V��V�ծ)v(vvouqK]1Wb�l{�U��[�qV��*�V������lU�p�\�*�U�b���]\*�U��]��]��uqV�\U��[U�U����klU��]LU���u8�U튺���b��b�"��Db�Sl�Sol*�L
�\UƘ��������*ꁊ�\U�����V�Wt�]Q��)�������wU��늷�*U'h���*�U��*��]�*�늵LUیU�|qV��*㊻��n���*��V��[�X�}qV�*�*ኺ�;b��kp�]�*���[����]����p4�]��q�lU��]J��1V�*�qWv�Z��[���������\Uث��V�UیU�Uث{b��Z튶�*�*㊺�Wkou1V��*�*�qV늺�k�*��Z#q�Wn]P{b�j+�Z�qU��w�*u���k�*�=�V�[�N*��U��b�$��+�b��[��*� ����hu0+�V�*��WU�*��]����hb���]�����U�Uث���OUԦ*�Uث�V�V�b��]�������������p�[�]\Uث����U�1Wu�]��uqWb��*�UثU�]�v*�1WV�b��*�*�Uث����*�*ኻuqV�WP⮦*�b��Z�*T�[�*�U��]����cu1Wb��ku1Wl1WSku0�����u*�*�*��uqV�Wb����Wv*�U��V��u�Z8���*�W����gkuqV���b���WU������늸�UԦ*ኺ�v*�qV��]Q�]�*�W�U��]_U�x�|�V�V�V��&��U�]��F*�ث�*�Uثt�Z#u1W�op��Z�*��]���klU�Uث[b�#u1Wb��\U��Wb�u�[�*�*�b��b�P⭑����q5�\1WSq�Z�*�1V銻kv*�8��*����8����lU��WSn��Z�]�lU��[������OlU����b��>ث�V���o�LU����V�)�������*�8�Db���]S��v*�1V銻j��}1Wb��b�늴qWo���⭌U����]�*�U��U�U���U��V�㊻j�W{�WWqj�Tb���]�w\U�Uث�x�<U��Wm����]��Z�[�*ኻlU�튻h���u�]��v*��Z�qWR������V�*�U��Woq�®�h⭌U���]\U�+��Zb��*�i��|�V���1Wb��u�[劵\U�U�b�b��]��s��������-����*��u)���v*��V늻劻v�����*�
�U�ث����]��]LU�x��Z�1V�x�,U��]LUث��U��*�L
�U�p+x��Wb��]\Uث�k��]��R�`Wb�늻uN*�U��>���WWp�u1V�WU��v�ء�R�U�U�pZt銻:���p�Z#�(o|R��WR���C[b�aC����qK������C�t8��qV銴1W|UƘ���-m���4N*�0���V��u�F*�U�U�P�U�▪1T�T�V3#Ջf	nR�\.*ʬ#�ٙ�QFd��qWb��V��Z8�ӊV�lUM��(QlU&*ļ��D��T��Ȕ0�W�E-A�늮/A�S�\TU�[Pb�u=�*㊹X⫅�*���=qVÃ��[Rk�ثF��1V�P�
:U��⫕k��P>x�D�܌UiBz�J���W(�}�W��*����^V�^��ҫ�Z(G��]NX��U��]�zu�\��k�*�� I� �T����[t'q����u�Wzt늸ç�*����)��	��qV����]�v;�UpV��*�
֘�Q�� �*��v;�n�*�J���|{U��;�"����q��Uژ���z
�Tu;⭑O��®Q�G���/�*�V�aV��k��ثm_�銸�J�L
�| Uxh�
u«yWn�p x�V��*�j�U�Z*��­����U���jb��:�i�b���\pi��\��m�X� ߩ�®	AO�i�R��+�ñ銫0 m�aU:�S���P7$�
��b:�x�5���A�hk�qW/6­�A�݁V�ݼqV��۾*�eCʕ�L*�M늮��@�تʏ
������UpZ�*�����*�� #�|UmG��]Z��Yv-㊸�� Uʝ����Ǒ�[eܨ8�����V���1U�[���V��­����*�W4늪
��W5;��MH=(F^�J�(0*�g��b�]�#
�������pe��i������LUu�V��U��b���m��I��늴^��:|�W2����W�*w�Z���U	튩��C��J�>�UwN���J�����iJb��LP�l�#��;rb�����Z
U�	qW(`�j��t��{����1V�Pb�LR��7�Y��[bO�Z�B��7���7®��WW�uH�ߦYϮl�R���pSԍ��U�c�Z4cNǶ*�F b����*�
{��U���7�\xҽ1Wcҟ,U�~��Ё:��U�>N*�I}���_Q�iZ��L
�5�W��jSYM�W�pO����>ثD튻j����0�a�#�*Јb��/�ت�1a����"���}5��u>ث��]��W �ZM��UĲnzU� ;���)-��S�ߊ�j��U�n�lUqPA�W$��v>��4�i\U����
�kQ�i}늴�|*�ؐ1V�P1W ��Z'jw8�kב�GlP�.�1J�DuރYث��m�V�kޘ�֦��U���8��7%銸�I�\6�*�>�U��銴��UĂ�[P �~�U��?3�b���i��h�S�u=�Ws'劶X�ث�m�*�Z����A_Uj�n1U�|+Q�h8�qW1��[C�U�Z<�b'ïӒ�S�%U\UPUpolUp�\1B�b�b�트qV�b���*�\P�LU�R�V��C���LRإ��C�V��[[�(hR�(o�)j���b��WaWT`WTb�늻�*�LU�w�[�*�U��]�����ኻ�(uk�]LU�Wlb��Z>ثt=�V���)��*�*㊺�������]LU�p��x���[>�klU��[ث�*���۶*�U�	Wov*�1W�]P0+`�ث,U��U�F*�u)��}�V�T����]\U��]Q�]��ohUث�SlU����U܉�p$�Uv����]�k|U��\@�\;b��8���]��*�ثCo�*�*ኻv*�U�1V�WlU�����[u+��Uث��N*�X�U5�[�n��X�{�'�u1W|�V�WSu1WSl�b�o��\UثX�db� b�銷��LUث�WR���LU����x�X�,U��*�<qV�ث[�U�qWb���h0�]��[��]�*�������u��Zup���o|
��l*�b���Z#���l⫱V�ኵ��b��S�����$8U��W���U��]\U��W��uqWWolU�b��]�]Q�]����Z�*�\U�U�����v*�U�1Wb��*�Wb����v*�b��]튵\Uث�Wb��]����Gp�[�X��Wb��vث�WSv*�U��]�*�*��]\U�k~ث�WWuqWWu<1V�WSov*�Uثx�X�CouqV�X�}z�Sn��X����v*�*�b�8��W|�WR���V�V���h튻oq��*�8�U�]��h�po|U�qWb�⮦*኷��\qV�������*�Uث�W��w�Z��\W�h.(lR��V�U�qWb��*�i�����LU�qWt�Z�]�*��]LU��]LU�WwLU���ulUثcu1WWuqWU�uqV��q�V⮯�*���qV��[�]�����V����Z\�*��\U�8����⭁�����WWj��u��]�v*�qWb��]LU�b��]S��b��[��W\U�h�u�jҰ8ں�
�v*�b��ኵOU��*���U����UثT8�t�Z�[�*�Wm�����l�Tb��uN*���]LUثB�o�LU��Z銷S�����q�Cn��t>8�T#v*�U�®�Z�­�����������uqWb����W�+�����T�[�]��ko|UثX����1V�[��Su@�]_U��]LU��Zoc�����W|�Wo�\0+�WSj��u�*�qV���A���w\U��]LU�
��[�*�b��pv*�*�U��v*�*�U�튭߾\j������*�⮮*���Z�[���]OU��WT���u�]��DU�#�*�1V�qV�ث�튺��Z�*�⮮*��8����
������lU�*�FuqWb�Sw�
���X�(up%����튵�j����b���Z�Z�}�V銺��T�]LU��\v*�1V�X�T�]LUثu®��)v(p'�����U���V�\R�U�P�)v����K�C���b��V�WW�v�p�:��-��#�k|U��Z8�u�)vh�Wo��↽�WW�b)��Z��P�s4��]:�L�mU�A$���2��fhjV�­�b���k|U��ZإI�U�iqV+���
����Ȕ0�+�J����lUei�*�ju�\����h��V��)����⫚���Ko�*Ѡ��b�U�ƾ�����5���\6;u�\Z���[�C�v���1Wr$Sk���[8�����զ*�Pצ*�<*� nqW=�b��늴AcLUr�6늶:׶*�6��VЊSl��ZTx�Uqf���\_�^�����F��l{W犮F�늮olU`B+�⭃N�1V���s|�b�����V��b��P��Ur9U n*��o���D�lU�lGLU�ɍ1WP�}�V� �4�[
A��1W�s�b�W��`�/
m���§�*�����p����WP�
��w�[$���^[�}���w 	�qW'�b�v_����;Ҵ�W)f]�WlU���T1�M)��P��*��r;`Vщ_lUsR�
��LU��l:����0��\
� p����{b���)����()��G�Ӆ]BGl
��cኯ�l*�b���$��+�V*�Ԍ
�PƦ�
�T{�[ ��\k���W N�׶i�Ol
�M*���J�튴t;b��?g�­+�J��[��Zh:�\!튵J
ӱ]���*=�W*�ܞ���^��A�\ʠ���v���*�(j	��qJ�V��­"0�-�U�Z����[���\@ ��a]��*ڻ!�w�|
ࢆ�*�1LUq;P��qU�q�*�\Uqn#qA����*�����W����O��u)�*�Z�lU��i�׆*ѩ��[ ��LU��j?^*�*7늶�^�;���AQ��*�� ;b��
b����*�o���\U}ON��[2P�&��lU�Ab�����V��\U�5�ӊ�Ǹژ���UqUĀ9��U���[o��rA���⫍j��*�t�~�Uܫ�u�Z4NpS�l*�i�늮��*�ڴ�ӊ�G��[@x�=+�� 9�p;U�m�Wr�[$Pb�Px�- ۮ*��o�r�p+L�
��v�D����FkC��W��ZQCQR����E�\+ۮ*���8��/'�ߊ�R}�*�����V2T�b��1��ъ����D)>�\I��p;P��*�H�+��w5��V&��j���|qV�jV���E��:�T�F���튷�m��V]�*ኯTC\U�!E*��Ӗ��r�b 늵�犴��*���7��T b�hI8���4�^I�qW2-*��*����트�B��t�j	�*�Q�*�G�á?<U�Ev튶	�nNثf��1V�A��s��[�zb�a�qV���⪈���8��<��|_#��%�ɒUe�T\Ux#
��n��x���LU���{��[�N*�m��U�8��b��1K�8�]1V�qCGu1V��]��up��`K_<P�k��
�0!�Uث������]Zb�Tb��(lb��V�WS�*�qWR����U��Pb��1Wb��klb��]1V�1V������x������犺��X�t�Z銻犻lU��b�u1V���LU��]��wLU�U�b�b�8U���[�]��Z�[�ZaV�0+��]L*�p+x��⭁LU�+�����C�oov*ኻhU�8�x���]Z��j�b�늻q8�cu7�]A���v*�qWn1V�*�*��Wb��v*�b��1U��{��]��[q�X��Zb�⮧�*�U��b��U�⮮*�qWWv����U��]���*�*�8�b����Z�*�����u�]������]�WUզ*�*��uqV�V�V�*኶=�+G
�犺��[�h⭟|U����]�]\U��b�0��+��]�*���\Uc7lU��*������+��V�_���$�*�8��*��b��p+�0��b��qV���犻uqWWklU�U�xb�8�X��b��U�U��\qV�V�Wb��*�U�⮦*㊻ov*�⮮*���]�����Wb�b��]��ok|Uث�Wb��]��v*��[늻v*�*�*�*�*��v*�*�*�qWqWWuqWb�4�]��\U���]����LU�oj���Wb�8Uث[`Wb���Z銶1V�*�Wv*�Uثx�޸�x����lU��]�Uث����b��]�v*�k�v*�*�`WR���qWWolU�qV�V�Wo����]�⮮*኷����]�*�U���]1Wb��b��[��Z�uqV늻j�uO\U�U�w�Wm���\I튷Z�b��lb��Z�[�ث�Wb�|�V��[�Z'p�]���*�*�ث�*�*��v*�*�qWo��u)����\U�1V늴7�]Q���������]Z�⮮*�qWb����]�p�1W qWb��U��*�֟g BC��|AR)\�LU�8������Z�qV����]LU�b��b�Wq'pl�I���V�Wb��]A��U�1V����������Zb���]ƻ⮮*�����������Wl1WW
��w�q®����Z�[�*�b�q튷�q�X�}qV���[�j�ov*�U�LU�����b��Z#uqW�]L
��U������*�*��v*��Wb��nqWb��ku1Wb��*�qWb��]LUث�8�[�U�Uث�V��[�,U��U����}qWWv*��WUث���q�X����[�*��]��v*�b�kv*�b� b��U�LU����*�<1Wo��|UݱV�㊻�*�*�x�m���*�Ui�[��*�^8��\*���o��LUثX��WU�­�WaWm��Wkv*�b��b��u*��WU�U��]Zb��*�U�b�銴*�)��u+����Ԧ��ث�WWj�P�p+G
Z��K���uqV�b�ޘ�����.����]��;lR�*�U��튭���\(u1Kc�u(u)h�U���p8��"��A�>���2���*QC�J��Ѻ<<��r�b�2UfcZ�!�p �4�Z#8��qU�xb�*��B����U�y�ku?��C4n�>9��}8�e~�X��E���w�|U�����@��*��Sp>�U�~!�*��zb�U=?U���b���6�K����c�b�y�ۮ*�\A�*���*�)�r�;b��4�\��Db�<A���l�n�����@�\Uj�v�[�:�U��Tb���,Uyzv�*��
ָ��p�*�!���Z;t��V� t�h
�Uı�Z�UQV�iLUiڼ{b�U���[&�֧l�w	SV?N*�Ͻ:b��?g��h�|*�Q���U�*r�/rp*ڎ��l!���Zߩ�b��I�i�	��q��T�VՔU�����\�5�qW*S�{b����[�sQ�0+|��J���ث�:U�S�l
�P��U�>>��Ai׾w���Uێ��K%>^�E�k�Vѩ���U`~�l�$7c�]��8����<p+M�߆*���p��oLUz�]0*��v���b��\Uco�_N"��B��b�����\�]��늴H5�VLU�7� 1��<�*�1�{⭪�hE+�ZT'S\
�p4�zU���®�|=��q�9��+&�q�؝�*ГjS|U�ݱV�Ҙ��Sj`WH���*�C�ኸ���U�A��U�0��
�ӯLUmH��U�ɷ�W&�}�U�T��;���`W��ZaU߶*�n��m�[.(�*�[Z�����R��
���WTv=p�Ec��zƽ<0+`��X��� |Up;��b��R+M�U�s�㊮ �ޘ�Ɩ��⫘��)���q����zW\I^�U�KR�����]Mت�U=�p�A��:�V���
�λ7n���v�V����\ё��\(���l9=<qW6�6�*Щ��LU��*F��
��k�������qV��a���S�qVԏ�)��Zj�	�����V��pU�Rw�0�[���{W��YVS튮 �Q��\�XS��[U�*Ӟˊ�F )�5�[�T���^J�)рژ��
���W1��8�E����ثm�߯|U�����P��U�GCA��V�+��Ӿ*�V<�|U�܍A��b�P�h5z�Up��{b��i]�*���F �b�(,b���4=qV�#`6�[jSZ�� ���\I?lU��߿�*�$�qV�튯��b�#効 �\�&�a���Ua]�銸U��<1U�6��V��rh*�(vڸ���Z���b�3w�W�z�e
v5�[S�")��XԹ銬�:b�ҝ1W6�|UmW��\\����cAڧp5�*�h� �nت��UOv��^��f��iN��⪱�^���WW|Uk=M{�K��\*�'h.��*ؠ��UP��b�J������QJ~��*�⪊qUت�\U�b�⭌UԮ*�P��K�*�î*�N)n�ko;�\1Wb�o�[늻v8�	v*�S;
]��q��w�]LU���1Wt�kp��[�\1WV����S�\I�Z�u�]A�����b��x��V�;�b������*�\U�F�����P1WWh��b��*�U���]\U�qW*�q�]��|U���W�V銷튵�q���]���U�U�*�q8�x���[�\6�]\U�튷��]\Uث�W���pb��qV���u1WSp�\qWb���WSn�����*�qWS�u1V���qV��*�*���]_U�'wN����]�*��kv*��늻u{b��*����V�Wt�[�n�j��d�SokuqWb��[�p=�WU�k�*�Wb����koq#kw�w\U�b���W��t�]Z�
qV銵A��Uث�Wb��1Wb�
Wl��\1V���������p�+�WZ�s��ۮ*���[�]ALU����48b�트�
�*�
�\qWb��]��������\*�1Wb��]�kv*�*��\qWo��v*�*�*�U�U�U�U�Wu1Wb�Uث�Wb��[�Z8��Wb��Z�[�|Uث�*�U��]LUث��\1V��[��8�_,U���LU���X����q��qV��[�X�t­��(8��Uث�Wb�SuqWTb��Uث�1V��u��Wh׮*�\U�������U�[��]Zb�WuqWWuqWU�ou1V�V�*�*�U�b��]��\U������ov����*�⮦*�Uث��U��*��V��Z�WS
�Nj�����u1Wt�\}�V����*��]�uk������f�����v*�U��Z�*߶*�b�銴I�]Zb����u�\qV�*�b��w!��Q���kn��[⮦*�Uث�*��n����]LU�b��[�튻k|U�'vث�b���V�8���qV銵��]�����vث�U��u��Wb��8��Wb�b�r4��9$;n������]C�����W�[��b��*�1Wb���]�uqWU�S����v�\	�]��劻oj���WWj��u�\1WSu@�]�����j���b��[�Z�l�U�⭒qV�㊻uqV�Wb�W�n�uqWWv*�U�qWV��[b��*�Uث�WUթ�]��ko�*�Uث����Z�1V����uqWb�������LU�Sj����⭊w�V�b���]Jb���]���*�*�⮮(up��8�v*�WPb��h�®����[;�}8��qWWj��t®��wLU�qV�1WU�Nث�b�S�q|�V���Z$��[��*�1Wb��v*�Uث�WSv*�lU��W����}1Wb��\qWm��vX 1290 ; N uni27F5 ; G 3913
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