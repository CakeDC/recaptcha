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

App::import('Core', 'Controller');
App::import('Component', 'Recaptcha.Recaptcha');

Mock::generatePartial('Recaptcha', 'RecaptchaMock', array('_getApiResponse'));

if (!class_exists('PostsTestController')) {
	class PostsTestController extends Controller {
		public $name = 'PostsTest';
		public $components = array('Recaptcha.Recaptcha');
		public $uses = array();
		public function test_captcha() {
		}
	}
}

/**
 * RecaptchaTestCase
 *
 * @package recaptcha
 * @subpackage recaptcha.tests.cases.components
 */
class RecaptchaTestCase extends CakeTestCase {

/**
 * startTest
 *
 * @return void
 */
	function startTest() {
		Configure::write('Recaptcha.privateKey', 'a-private-key');
		$this->Controller = new PostsTestController();
		$this->Controller->constructClasses();
		$this->Controller->Component->init($this->Controller);
		$this->Controller->Component->initialize($this->Controller);
		//$this->Controller->Recaptcha = new RecaptchaMock();
	}

/**
 * endTest
 *
 * @return void
 */
	function endTest() {
		unset($this->Controller);
		ClassRegistry::flush();
	}

/**
 * testRecaptcha
 *
 * @return void
 */
	public function testRecaptcha() {
		$this->Controller->params['form']['recaptcha_challenge_field'] = 'something';
		$this->Controller->params['form']['recaptcha_response_field'] = 'something';
		$this->assertFalse($this->Controller->Recaptcha->verify());
	}

}
