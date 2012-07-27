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

App::uses('Controller', 'Controller');
App::uses('HtmlHelper', 'View/Helper');
App::uses('FormHelper', 'View/Helper');
App::uses('RecaptchaHelper', 'Recaptcha.View/Helper');

/**
 * PostsTestController
 *
 * @package recaptcha
 * @subpackage recaptcha.tests.cases.helper
 */
class PostsTestController extends Controller {

/**
 * name property
 *
 * @var string 'Media'
 */
	public $name = 'PostsTest';

/**
 * uses property
 *
 * @var mixed null
 */
	public $uses = null;
}

/**
 * RecaptchaHelperTest
 *
 * @package recaptcha
 * @subpackage recaptcha.tests.cases.helpers
 */
class RecaptchaHelperTest extends CakeTestCase {

/**
 * setUp method
 *
 * The mailHide keys have to be in a certain format see https://groups.google.com/group/recaptcha/browse_thread/thread/3edc0ad4adc33073?pli=1#msg_73107610db1a1c15
 *
 * @return void
 */
	public function setUp() {
		Configure::write('Recaptcha.mailHide.publicKey', '01J_tiDKknxUV8w-2NbVFNAQ==');
		Configure::write('Recaptcha.mailHide.privateKey', '411744faf004d447f8208fc51159dc03');

		$this->View = new View(new PostsTestController());
		ClassRegistry::addObject('view', $this->View);
		$this->Recaptcha = new RecaptchaHelper($this->View);
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		ClassRegistry::flush();
		unset($this->Recaptcha);
	}

/**
 * testDisplay method
 *
 * @return void
 */
	public function testDisplay() {
		$expected = '<div class="recaptcha"><script type="text/javascript" src="https://www.google.com/recaptcha/api/challenge?k="></script>
				<noscript>
					<iframe src="https://www.google.com/recaptcha/api/noscript?k=" height="300" width="500" frameborder="0"></iframe><br/>
					<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
					<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
				</noscript></div>';
		$result = $this->Recaptcha->display();
		$this->assertEqual($result, $expected);
	}

/**
 * testSignupUrl method
 *
 * @return void
 */
	public function testSignupUrl() {
		$expected = 'http://recaptcha.net/api/getkey?domain=' . WWW_ROOT . 'test-app';
		$result = $this->Recaptcha->signupUrl('test-app');
	}

/**
 * testSignupUrl method
 *
 * @return void
 */
	public function testMailHide() {
		$expected = 'http://recaptcha.net/api/getkey?domain=' . WWW_ROOT . 'test-app';
		$result = $this->Recaptcha->mailHide('contact@cakedc.com');
	}

/**
 * testMailHideUrl method
 *
 * @return void
 */
	public function testMailHideUrl() {
		$result = $this->Recaptcha->mailHideUrl('contact@cakedc.com');
	}

}