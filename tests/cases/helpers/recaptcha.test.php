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

App::import('Core', array('Helper', 'AppHelper', 'ClassRegistry', 'Controller', 'Model'));
App::import('Helper', array('Recaptcha.Recaptcha', 'Html', 'Form'));
Mock::generatePartial('HtmlHelper', 'RecaptchaTestHtmlHelper', array('scriptBlock'));

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

Mock::generate('View', 'MockView');

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
 * @return void
 */
	function setup() {
		// "Global" keys for testing
		Configure::write('Recaptcha.publicKey', '6LdsGskSAAAAAPz645yuA9Bwj4BgBkWtWJZj_iWa');
		Configure::write('Recaptcha.privateKey', '6LdsGskSAAAAANEUP5yhcJ-XzbeUqSySGKfWH_Zm');

		

		$view = new View(new PostsTestController());
		$this->Recaptcha = new RecaptchaHelper();
		$this->Recaptcha->Html = new RecaptchaTestHtmlHelper();
		$this->Recaptcha->Form = new FormHelper();
		ClassRegistry::addObject('view', $view);
	}

/**
 * tearDown method
 *
 * @return void
 */
	function tearDown() {
		ClassRegistry::flush();
		unset($this->Recaptcha);
	}

/**
 * testSignupUrl
 *
 * @return void
 */
	function testSignupUrl() {
		$result = $this->Recaptcha->signupUrl('test');
		$expected = 'http://recaptcha.net/api/getkey?domain=' . WWW_ROOT . '&amp;app=test' ;
		$this->assertIdentical($expected, $result);
	}

/**
 * testDisplayDefault
 *
 * @return void
 */
	function testDisplayDefault() {
		$this->Recaptcha->Html->expectOnce('scriptBlock', array(
				'var RecaptchaOptions = {"theme":"red","lang":"en","custom_translations":[]}',
				array('inline' => false)));

		$result = $this->Recaptcha->display(array('publicKey' => Configure::read('Recaptcha.publicKey')));
		$expected = <<<TEXT
<div class="recaptcha"><script type="text/javascript" src="https://www.google.com/recaptcha/api/challenge?k=6LdsGskSAAAAAPz645yuA9Bwj4BgBkWtWJZj_iWa"></script>
				<noscript>
					<iframe src="https://www.google.com/recaptcha/api/noscript?k=6LdsGskSAAAAAPz645yuA9Bwj4BgBkWtWJZj_iWa" height="300" width="500" frameborder="0"></iframe><br/>
					<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
					<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
				</noscript></div>
TEXT;
		$this->assertIdentical($expected, $result);
	}

/**
 * testDisplayTheme
 *
 * @return void
 */
	function testDisplayTheme() {
		$this->Recaptcha->Html->expectOnce('scriptBlock', array(
				'var RecaptchaOptions = {"theme":"blackglass","lang":"en","custom_translations":[]}',
				array('inline' => false)));

		$result = $this->Recaptcha->display(array(
			'publicKey' => Configure::read('Recaptcha.publicKey'),
			'recaptchaOptions' => array(
				'theme' => 'blackglass')));
		$expected = <<<TEXT
<div class="recaptcha"><script type="text/javascript" src="https://www.google.com/recaptcha/api/challenge?k=6LdsGskSAAAAAPz645yuA9Bwj4BgBkWtWJZj_iWa"></script>
				<noscript>
					<iframe src="https://www.google.com/recaptcha/api/noscript?k=6LdsGskSAAAAAPz645yuA9Bwj4BgBkWtWJZj_iWa" height="300" width="500" frameborder="0"></iframe><br/>
					<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
					<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
				</noscript></div>
TEXT;
		$this->assertIdentical($expected, $result);
	}

/**
 * testDisplayError
 *
 * @return void
 */
	function testDisplayError() {
		$this->Recaptcha->Html->expectOnce('scriptBlock', array(
				'var RecaptchaOptions = {"theme":"red","lang":"en","custom_translations":[]}',
				array('inline' => false)));

		$result = $this->Recaptcha->display(array(
			'publicKey' => Configure::read('Recaptcha.publicKey'),
			'error' => 'testError'));
		$expected = <<<TEXT
<div class="recaptcha"><script type="text/javascript" src="https://www.google.com/recaptcha/api/challenge?k=6LdsGskSAAAAAPz645yuA9Bwj4BgBkWtWJZj_iWa"></script>
				<noscript>
					<iframe src="https://www.google.com/recaptcha/api/noscript?k=6LdsGskSAAAAAPz645yuA9Bwj4BgBkWtWJZj_iWa" height="300" width="500" frameborder="0"></iframe><br/>
					<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
					<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
				</noscript></div>
TEXT;
		$this->assertIdentical($expected, $result);
	}
}
