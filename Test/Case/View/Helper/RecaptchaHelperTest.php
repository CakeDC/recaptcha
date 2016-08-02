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
 * @property View View
 * @property RecaptchaHelper Recaptcha
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
		Configure::write('Recaptcha.publicKey', '01J_tiDKknxUV8w-2NbVFNAQ==');
		Configure::write('Recaptcha.privateKey', '411744faf004d447f8208fc51159dc03');

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
		$expected = '<div class="recaptcha"><div data-sitekey="' . Configure::read('Recaptcha.publicKey') .'" data-theme="light" class="g-recaptcha"/></div>';
		$result = $this->Recaptcha->display();
		$this->assertXmlStringEqualsXmlString($result, $expected);

		$expected = '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=en" async="async" defer="defer"></script>';
		$result = $this->View->fetch('script');
		$this->assertTextEquals($result, $expected);
	}

/**
 * testDisplayExplicit method
 *
 * @return void
 */
	public function testDisplayExplicit() {
		$id = 'recaptcha123';
		$expected = '<div class="recaptcha"><div id="' . $id . '"/></div>';
		$result = $this->Recaptcha->display(array('id' => $id, 'explicit' => true));
		$this->assertXmlStringEqualsXmlString($result, $expected);

		$expected = '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=en&onload=onRecaptchaLoadCallback&render=explicit" async="async" defer="defer"></script>';
		$result = $this->View->fetch('script');
		$this->assertTextEquals($result, $expected);

		$this->Recaptcha->display(array(
				'id'               => $id . '4',
				'explicit'         => true,
				'recaptchaOptions' => array('theme' => 'dark'),
			)
		);
		$expected = array(
			$id       => array(
				'sitekey' => Configure::read('Recaptcha.publicKey'),
				'theme'   => 'light',
			),
			$id . '4' => array(
				'sitekey' => Configure::read('Recaptcha.publicKey'),
				'theme'   => 'dark',
			),
		);
		$reflector = new ReflectionProperty('RecaptchaHelper', 'explicit');
		$reflector->setAccessible(true);
		$result = $reflector->getValue($this->Recaptcha);
		$this->assertEquals($result, $expected);
	}

	/**
	 * testSignupUrl method
 *
 * @return void
 */
	public function testSignupUrl() {
		$expected = 'https://www.google.com/recaptcha/admin?domain=' . WWW_ROOT . '&amp;app=test-app';
		$result = $this->Recaptcha->signupUrl('test-app');
		$this->assertTextEquals($expected, $result);
	}

/**
 * testSignupUrl method
 *
 * @return void
 */
	public function testMailHide() {
		$email = 'contact@cakedc.com';
		$url = htmlentities($this->Recaptcha->mailHideUrl($email));
		$expected = 'cont<a href=\'' . $url . '\' onclick="window.open(\'' . $url . '\', \'\', \'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300\'); return false;" title="Reveal this e-mail address">...</a>@cakedc.com';
		$result = $this->Recaptcha->mailHide($email);
		$this->assertTextEquals($expected, $result);
	}

/**
 * testMailHideUrl method
 *
 * @return void
 */
	public function testMailHideUrl() {
		$expected = 'http://mailhide.recaptcha.net/d?k=01J_tiDKknxUV8w-2NbVFNAQ==&c=j7XGXEnoSEqoLbCFEvwv4HFHaJq18FADS4WY2X_gDIo=';
		$result = $this->Recaptcha->mailHideUrl('contact@cakedc.com');
		$this->assertTextEquals($expected, $result);
	}

}
