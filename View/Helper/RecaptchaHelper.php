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
 * @package recaptcha
 * @subpackage recaptcha.views.helpers
 */
class RecaptchaHelper extends AppHelper {

/**
 * Secure API Url
 *
 * @var string
 */
	public $secureApiUrl = 'https://www.google.com/recaptcha/api';

/**
 * API Url
 *
 * @var string
 */
	public $apiUrl = 'http://www.google.com/recaptcha/api';

/**
 * View helpers
 *
 * @var array
 */
	public $helpers = array('Form', 'Html');

/**
 * Displays the Recaptcha input
 *
 * @param array $options An array of options
 *
 * ### Options:
 *
 * - `element` String, name of the view element that can be used instead of the hardcoded HTML structure from this helper
 * - `publicKey` String, default is read from Configure::read('Recaptcha.publicKey'), you can override it here
 * - `error` String, optional error message that is displayed using Form::error()
 * - `ssl` Boolean, use SSL or not, default is true
 * - `div` Array of options for the div tag the recaptcha is wrapped with, set to false if you want to disable it
 * - `recaptchaOptions` assoc array of options to pass into RecaptchaOptions var, like 'theme', 'lang'
 *    or 'custom_translations' to runtime configure the widget.
 *
 * @return string The resulting mark up
 * @access public
 */
	public function display($options = array()) {
		$defaults = array(
			'element' => null,
			'publicKey' => Configure::read('Recaptcha.publicKey'),
			'error' => null,
			'ssl' => true,
			'error' => false,
			'div' => array(
				'class' => 'recaptcha'),
				'recaptchaOptions' => array(
					'theme' => 'red',
					'lang' => 'en',
					'custom_translations' => array()
				)
		);

		$options = Set::merge($defaults, $options);
		extract($options);

		if ($ssl) {
			$server = $this->secureApiUrl;
		} else {
			$server = $this->apiUrl;
		}

		$errorpart = "";
		if ($error) {
			$errorpart = "&amp;error=" . $error;
		}

		if (!empty($element)) {
			$elementOptions = array();
			if (is_array($element)) {
				$keys = array_keys($element);
				$elementOptions = $element[$keys[0]];
			}

			return $this->View->element($element, $elementOptions);
		}

		$jsonOptions = preg_replace('/"callback":"([^"\r\n]*)"/', '"callback":$1', json_encode($recaptchaOptions));
		unset($recaptchaOptions);

		if (empty($this->params['isAjax'])) {
			$configScript = sprintf('var RecaptchaOptions = %s', $jsonOptions);
			echo $this->Html->scriptBlock($configScript);

			$script = '';
			$script .= '<script type="text/javascript" src="' . $server . '/challenge?k=' . $publicKey . '"></script>';
			$script .= '<noscript>';
			$script .= '	<iframe src="' . $server . '/noscript?k=' . $publicKey . '" height="300" width="500" frameborder="0"></iframe><br/>';
			$script .= '	<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>';
			$script .= '  <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>';
			$script .= '</noscript>';

			if (!empty($error)) {
				$script .= $this->Form->error($error);
			}

			if ($options['div'] != false) {
				$script = $this->Html->tag('div', $script, $options['div']);
			}

			$this->Form->unlockField('recaptcha_challenge_field');
			$this->Form->unlockField('recaptcha_response_field');

			return $script;
		}

		$id = uniqid('recaptcha-');

		$script = '';
		$script .= '<div id="' . $id . '"></div>';
		$script .= '<script>';
		$script .= 'if (window.Recaptcha == undefined) {';
		$script .= '  (function() {';
		$script .= '	  var headID = document.getElementsByTagName("head")[0];';
		$script .= '		var newScript = document.createElement("script");';
		$script .= '		newScript.type = "text/javascript";';
		$script .= '		newScript.onload = function() {';
		$script .= '			Recaptcha.create("' . $publicKey . '", "' . $id . '", ' . $jsonOptions . ');';
		$script .= '		  Recaptcha.focus_response_field();';
		$script .= '		};';
		$script .= '		newScript.src = "' . $server . '/js/recaptcha_ajax.js"';
		$script .= '	  headID.appendChild(newScript);';
		$script .= '  })();';
		$script .= '} else {';
		$script .= '  setTimeout(\'Recaptcha.create("' . $publicKey . '", "' . $id . '", ' . $jsonOptions . ')\', 1000);';
		$script .= '}';
		$script .= '</script>';

		return $script;
	}

/**
 * Recaptcha signup URL
 *
 * @param string $appName An application name
 * @return string A signup url
 */
	public function signupUrl($appName = null) {
		return "http://recaptcha.net/api/getkey?domain=" . WWW_ROOT . '&amp;app=' . urlencode($appName);
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
			throw new Exception(__d('recaptcha', "You need to set a private and public mail hide key. Please visit http://mailhide.recaptcha.net/apikey", true));
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
 * http://mailhide.recaptcha.net/apikey
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
