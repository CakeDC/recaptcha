<?php
/**
 * Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
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
	public $secureApiUrl = 'http://api-secure.recaptcha.net';

/**
 * API Url
 *
 * @var string
 */
	public $apiUrl = 'http://api.recaptcha.net';

/**
 * View helpers
 *
 * @var array
 */
	public $helpers = array('Html');

/**
 * Displays the Recaptcha input
 *
 * @param
 * @param boolean
 * @return string
 */
	public function display($options = array()) {
		$defaults = array(
			'element' => null, 
			'publicKey' => Configure::read('Recaptcha.publicKey'),
			'error' => null,
			'ssl' => true,
			'theme' => 'white',
			'lang'  => 'en'
		);

		if (Configure::read('Config.language')) {
			$defaults['lang'] = Configure::read('Config.language');
		}
		$options = array_merge($defaults, $options);

		$errorpart = '';
		if ($options['error']) {
			$errorpart = '&amp;error=' . $options['error'];
		}

		if (!empty($options['element'])) {
			$elementOptions = array();
			if (is_array($options['element'])) {
				$keys = array_keys($options['element']);
				$elementOptions = $options['element'][$keys[0]];
			}
			$View = $this->__view();
			return $View->element($options['element'], $elementOptions);
		}

		$result = $this->_buildOptions($options);
		$result .= $this->_buildScripts($options);
		return $result;
	}

/**
 * Build the Recaptcha options, and return script block for output.
 *
 * @param array $options Options
 * @return string
 */
	protected function _buildOptions($options) {
		$js = <<<ENDJS
var RecaptchaOptions = {
	theme: "${options['theme']}",
	lang: "${options['lang']}"
};
ENDJS;
		return $this->Html->scriptBlock($js);
	}

/**
 * Build the script tags to show recaptcha
 *
 * @param array $options Options
 * @return string
 */
	protected function _buildScripts($options) {
		$server = $options['ssl'] ? $this->secureApiUrl : $this->apiUrl;
		$jsCode = $this->Html->script($server . '/challenge?k=' . $options['publicKey']);
		$noJsCode = <<<ENDCODE
<noscript>
	<iframe src="${server}/noscript?k=${options['publicKey']}" height="300" width="500" frameborder="0"></iframe><br/>
	<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
	<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
</noscript>
ENDCODE;
		return $jsCode . $noJsCode;
	}

/**
 * Recaptcha signup URL
 *
 * @return string
 */
	public function signupUrl($appname = null) {
		return "http://recaptcha.net/api/getkey?domain=" . WWW_ROOT . '&amp;app=' . urlencode($appname);
	}

/**
 * AES Pad
 *
 * @param string value
 * @return string
 */
	private function __AesPad($val) {
		$blockSize = 16;
		$numpad = $blockSize - (strlen($val) % $blockSize);
		return str_pad($val, strlen ($val) + $numpad, chr($numpad));
	}

/**
 * AES Encryption
 *
 * @return string
 */
	private function __AesEncrypt($value, $key) {
		if (!function_exists('mcrypt_encrypt')) {
			throw new Exception(__d('recaptcha', 'To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.', true));
		}

		$mode = MCRYPT_MODE_CBC;
		$encryption = MCRYPT_RIJNDAEL_128;
		$value = $this->__AesPad($value);

		return mcrypt_encrypt($encryption, $key, $value, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
	}

/**
 * Mail-hide URL
 *
 * @return string base 64 encrypted string
 */
	private function __mailhideUrlbase64 ($x) {
		return strtr(base64_encode ($x), '+/', '-_');
	}

/**
 * Gets the reCAPTCHA Mailhide url for a given email
 *
 * @param $email
 * @return string
 */
	public function mailHideUrl($email = null) {
		$publicKey = Configure::read('Recaptcha.mailHide.publicKey');
		$privateKey = Configure::read('Recaptcha.mailHide.privateKey');

		if ($publicKey == '' || $publicKey == null || $privateKey == "" || $privateKey == null) {
			throw new Exception(__d('recaptcha', "You need to set a private and public mail hide key. Please visit http://mailhide.recaptcha.net/apikey", true));
		}

		$key = pack('H*', $privateKey);
		$cryptmail = $this->__AesEncrypt ($email, $key);

		return "http://mailhide.recaptcha.net/d?k=" . $publicKey . "&c=" . $this->__mailhideUrlbase64($cryptmail);
	}

/**
 * Get a part of the email to show
 *
 * Given johndoe@example,com return ["john", "example.com"].
 * the email is then displayed as john...@example.com
 *
 * @param string email
 * @return array
 */
	private function __hideEmailParts($email) {
		$array = preg_split("/@/", $email );

		if (strlen ($array[0]) <= 4) {
			$array[0] = substr ($array[0], 0, 1);
		} else if (strlen ($array[0]) <= 6) {
			$array[0] = substr ($array[0], 0, 3);
		} else {
			$array[0] = substr ($array[0], 0, 4);
		}
		return $array;
	}

/**
 * Gets html to display an email address given a public an private key to get a key go to:
 * http://mailhide.recaptcha.net/apikey
 *
 * @param string Email 
 * @return string
 */
	public function mailHide($email) {
		$emailparts = __hideEmailParts ($email);
		$url = $this->mailHideUrl($email);

		return htmlentities($emailparts[0]) . "<a href='" . htmlentities ($url) .
			"' onclick=\"window.open('" . htmlentities ($url) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this e-mail address\">...</a>@" . htmlentities ($emailparts [1]);
	}

/**
 * Get current view class
 *
 * @return object, View class
 */
	private function __view() {
		if (!empty($this->globalParams['viewInstance'])) {
			return $this->globalParams['viewInstance'];
		}
		return ClassRegistry::getObject('view');
	}
}
