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
 * CakePHP Recaptcha Behavior
 *
 * @package recaptcha
 * @subpackage recaptcha.models.behaviors
 */
class RecaptchaBehavior extends ModelBehavior {

/**
 * Settings array
 *
 * @var array
 */
	public $settings = array();

/**
 * Default settings
 *
 * @var array
 */
	public $defaults = array(
		'errorField' => 'recaptcha');

/**
 * Setup
 *
 * @param Model $Model
 * @param array $settings
 */
	public function setup(Model $Model, $settings = array()) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = $this->defaults;
		}
		$this->settings[$Model->alias] = array_merge($this->settings[$Model->alias], (is_array($settings) ? $settings : array()));
	}

/**
 * Validates the captcha responses status set by the component to the model
 *
 * @param Model $Model
 * @return boolean
 * @see RecaptchaComponent::initialize()
 */
	public function validateCaptcha(Model $Model) {
		if (isset($Model->recaptcha) && $Model->recaptcha === false) {
			$Model->invalidate($this->settings[$Model->alias]['errorField'], $Model->recaptchaError);
		}
		return true;
	}

/**
 * Validates the captcha
 *
 * @param Model $Model
 * @param array $options
 * @return void
 */
	public function beforeValidate(Model $Model, $options = array()) {
		$this->validateCaptcha($Model);
		return true;
	}

}