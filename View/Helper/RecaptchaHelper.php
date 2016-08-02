<?php
/**
 * Copyright 2009-2014, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009-2014, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * CakePHP Recaptcha helper
 *
 * @property HtmlHelper Html
 * @property FormHelper Form
 * @property View View
 * @package recaptcha
 * @subpackage recaptcha.views.helpers
 */
class RecaptchaHelper extends AppHelper {

/**
 * API Url
 *
 * @var string
 */
	public $apiUrl = 'https://www.google.com/recaptcha/api.js';

/**
 * View helpers
 *
 * @var array
 */
	public $helpers = array('Form', 'Html');

/**
 * Callback function name (fot explicit rendering)
 *
 * @var string
 */
	protected $callback = 'onRecaptchaLoadCallback';

/**
 * Items to render explicitly
 *
 * @var array
 */
	protected $explicit = array();

	public function afterRender($viewFile) {
		if ($this->explicit) {
			$script = array();

			$script[] = sprintf("var %s = function() {", $this->callback);
			foreach ($this->explicit as $k => $v) {
				array_unshift($script, sprintf('var %s;', $k));
				$script[] = sprintf('%s = grecaptcha.render(\'%s\', %s);', $k, $k, json_encode($v));
			}
			$script[] = "}";

			$this->Html->scriptBlock(implode("\n", $script), array('block' => 'script'));
		}
	}

/**
 * Displays the Recaptcha input
 *
 * @param array $options An array of options
 *
 * ### Options:
 *
 * - `element` String, name of the view element that can be used instead of the hardcoded HTML structure from this helper
 * - `explicit` reCAPTCHA rendering method. Set to true when rendering multiple reCAPTCHAs on a page
 * - `error` String, optional error message that is displayed using Form::error()
 * - `div` Array of options for the div tag the recaptcha is wrapped with, set to false if you want to disable it
 * - `lang` Forces the widget to render in a specific language
 * - `recaptchaOptions` assoc array of options to pass into RecaptchaOptions var, like 'sitekey' (default is read from Configure::read('Recaptcha.publicKey')), 'theme', 'type', 'size.
 *
 * @return string The resulting mark up
 * @access public
 */
	public function display($options = array()) {
		$defaults = array(
			'element'          => null,
			'explicit'         => false,
			'error'            => false,
			'div'              => array(
				'class' => 'recaptcha',
			),
			'lang'             => 'en',
			'recaptchaOptions' => array(
				'sitekey' => Configure::read('Recaptcha.publicKey'),
				'theme'   => 'light',
			),
		);

		$options = Hash::merge($defaults, $options);

		if ($element = $options['element']) {
			$elementOptions = array();
			if (is_array($element)) {
				$keys = array_keys($element);
				$elementOptions = $element[$keys[0]];
			}

			return $this->View->element($element, $elementOptions);
		}

		if ($lang = Hash::get($options, 'recaptchaOptions.lang')) {
			// Backwards compatibility
			$options['lang'] = $lang;
			unset($options['recaptchaOptions']['lang']);
		}

		if ($publicKey = Hash::get($options, 'publicKey')) {
			// Backwards compatibility
			$options['recaptchaOptions']['sitekey'] = $publicKey;
			unset($options['publicKey']);
		}

		$query = array('hl' => $options['lang']);

		if ($options['explicit']) {
			$query = array_merge($query, array('onload' => $this->callback, 'render' => 'explicit'));

			$id = Hash::get($options, 'id', uniqid('recaptcha'));
			$this->explicit[$id] = $options['recaptchaOptions'];

			$output = $this->Html->tag('div', '', compact('id'));
		} else {
			$data = array_combine(
				array_map(
					function ($k) {
						return 'data-' . $k;
					},
					array_keys($options['recaptchaOptions'])
				),
				$options['recaptchaOptions']
			);
			$data['class'] = 'g-recaptcha';

			$output = $this->Html->tag('div', '', $data);
		}

		if ($error = $options['error']) {
			$output .= $this->Form->error($error);
		}

		if ($options['div']) {
			$output = $this->Html->tag('div', $output, $options['div']);
		}

		$this->Form->unlockField('g-recaptcha-response');

		$apiUrl = $this->apiUrl . '?' . http_build_query($query);
		$this->Html->script($apiUrl, array('block' => 'script', 'async' => true, 'defer' => true, 'once' => true));

		return $output;
	}

/**
 * Recaptcha signup URL
 *
 * @param string $appName An application name
 * @return string A signup url
 */
	public function signupUrl($appName = null) {
		return "https://www.google.com/recaptcha/admin?domain=" . WWW_ROOT . '&amp;app=' . urlencode($appName);
	}

/**
 * AES Pad
 *
 * @param string $val A value to pad
 * @return string
 */
	private function __aesPad($val) {
		$blockSize = 16;
		$numpad = $blockSize - (strlen($val) % $blockSize);
		return str_pad($val, strlen($val) + $numpad, chr($numpad));
	}

/**
 * AES Encryption
 *
 * @param string $value A value
 * @param string $key A key to use
 * @return string
 * @throws Exception
 */
	private function __aesEncrypt($value, $key) {
		if (!function_exists('mcrypt_encrypt')) {
			throw new Exception(__d('recaptcha', 'To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.', true));
		}

		$mode = MCRYPT_MODE_CBC;
		$encryption = MCRYPT_RIJNDAEL_128;
		$value = $this->__aesPad($value);

		return mcrypt_encrypt($encryption, $key, $value, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
	}

/**
 * Mail-hide URL
 *
 * @param string $x An input string
 * @return string A base 64 encrypted string
 */
	private function __mailhideUrlbase64($x) {
		return strtr(base64_encode($x), '+/', '-_');
	}

/**
 * Gets the reCAPTCHA Mailhide url for a given email
 *
 * @param string $email An email address
 * @return string
 * @throws Exception
 */
	public function mailHideUrl($email = null) {
		$publicKey = Configure::read('Recaptcha.mailHide.publicKey');
		$privateKey = Configure::read('Recaptcha.mailHide.privateKey');

		if ($publicKey == '' || $publicKey == null || $privateKey == "" || $privateKey == null) {
			throw new Exception(__d('recaptcha', "You need to set a private and public mail hide key. Please visit https://www.google.com/recaptcha/mailhide/apikey", true));
		}

		$key = pack('H*', $privateKey);
		$cryptmail = $this->__aesEncrypt($email, $key);

		return "http://mailhide.recaptcha.net/d?k=" . $publicKey . "&c=" . $this->__mailhideUrlbase64($cryptmail);
	}

/**
 * Get a part of the email to show
 *
 * Given johndoe@example,com return ["john", "example.com"].
 * the email is then displayed as john...@example.com
 *
 * @param string $email an email address
 * @return array
 */
	private function __hideEmailParts($email) {
		$array = preg_split("/@/", $email );

		if (strlen($array[0]) <= 4) {
			$array[0] = substr($array[0], 0, 1);
		} elseif (strlen($array[0]) <= 6) {
			$array[0] = substr($array[0], 0, 3);
		} else {
			$array[0] = substr($array[0], 0, 4);
		}
		return $array;
	}

/**
 * Gets html to display an email address given a public an private key to get a key go to:
 * https://www.google.com/recaptcha/mailhide/apikey
 *
 * @param string $email An email address
 * @return string
 */
	public function mailHide($email) {
		$emailparts = $this->__hideEmailParts($email);
		$url = $this->mailHideUrl($email);

		return htmlentities($emailparts[0]) . "<a href='" . htmlentities($url) .
			"' onclick=\"window.open('" . htmlentities($url) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this e-mail address\">...</a>@" . htmlentities($emailparts[1]);
	}

}
