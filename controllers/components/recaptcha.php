<?php
/**
 * Bitly plugin for CakePHP
 *
 * Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * CakePHP Recaptcha component
 *
 * @package recaptcha
 * @subpackage recaptcha.controllers.components
 */
class RecaptchaComponent extends Object {

/**
 * Name
 *
 * @var string
 */
	public $Controller = null;

/**
 * Recaptcha API Url
 *
 * @var string
 */
	public $apiUrl = 'http://api-verify.recaptcha.net/verify';

/**
 * Private API Key
 *
 * @var string
 */
	public $privateKey = '';

/**
 * Error coming back from Recaptcha
 *
 * @var string
 */
	public $error = null;

/**
 * Actions that should automatically checked for a recaptcha input
 *
 * @var array
 */
	public $actions = array();

 /**
 * Callback
 *
 * @param object Controller object
 */
	public function initialize(&$controller, $settings = array()) {
		$this->privateKey = Configure::read('Recaptcha.privateKey');
		$this->Controller = $controller;

 		if ($this->enabled == true) {
			foreach ($settings as $setting => $value) {
				if (isset($this->{$setting})) {
					$this->{$setting} = $value;
				}
			}

 			if (empty($this->privateKey)) {
				throw new Exception(__d('recaptcha', "You must set your private recaptcha key using Cofigure::write('Recaptcha.privateKey', 'your-key');!", true));
			}

			$controller->helpers[] = 'Recaptcha.Recaptcha';
		}

		if (in_array($this->Controller->action, $this->actions)) {
			if (!$this->verify()) {
				$this->Controller->{$this->Controller->modelClass}->invalidate('recaptcha', $this->error);
			}
		}
	}

/**
 * Verifies the recaptcha input
 *
 * @return boolean True if the response was correct
 */
	public function verify() {
		if (isset($this->Controller->params['form']['recaptcha_challenge_field']) && 
			isset($this->Controller->params['form']['recaptcha_response_field'])) {

			$response = $this->_getApiResponse();
			$response = explode("\n", $response);

			if ($response[0] == 'true') {
				return true;
			}

			if ($response[1] == 'incorrect-captcha-sol') {
				$this->error = __d('recaptcha', 'Incorect captcha', true);
			} else {
				$this->error = $response[1];
			}

			return false;
		}
	}

/**
 * Queries the Recaptcha API and and returns the raw response
 *
 * @return string
 */
	protected function _getApiResponse() {
		App::import('Core', 'HttpSocket');
		$Socket = new HttpSocket();
		return $Socket->post($this->apiUrl, array(
			'privatekey'=> $this->privateKey,
			'remoteip' => env('REMOTE_ADDR'),
			'challenge' => $this->Controller->params['form']['recaptcha_challenge_field'],
			'response' => $this->Controller->params['form']['recaptcha_response_field']));
	}

}
