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

App::uses('CakeTestCase', 'TestSuite');
App::uses('Controller', 'Controller');
App::uses('RecaptchaComponent', 'Recaptcha.Controller/Component');

if (!class_exists('ArticlesTestController')) {
	class ArticleTestController extends Controller {

	/**
	 * Class name.
	 *
	 * @var string
	 */
		public $name = 'ArticleTests';

	/**
	 * An array of names of components to load.
	 *
	 * @var array
	 */
		public $components = array('Recaptcha.Recaptcha');

	/**
	 * An array of names of models to load.
	 *
	 * @var array
	 */
		public $uses = array('RecaptchaTestArticle');

	/**
	 *
	 */
		public function test_captcha() {
		}

	}
}

if (!class_exists('RecaptchaTestArticle')) {
	class RecaptchaTestArticle extends CakeTestModel {

	/**
	 * Class name.
	 *
	 * @var string
	 */
		public $name = 'RecaptchaTestArticle';

	/**
	 * An array of names of behaviors to load.
	 *
	 * @var array
	 */
		public $actsAs = array('Recaptcha.Recaptcha');

	/**
	 * Use table.
	 *
	 * @var mixed False or table name
	 */
		public $useTable = 'articles';
	}
}

/**
 * RecaptchaTestCase
 *
 * @package recaptcha
 * @subpackage recaptcha.tests.cases.components
 */
class RecaptchaComponentTest extends CakeTestCase {

/**
 * fixtures property
 *
 * @var array
 */
	public $fixtures = array('plugin.recaptcha.article');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		Configure::write('Recaptcha.privateKey', 'private-key');
		$this->Controller = new ArticleTestController();
		$this->Controller->constructClasses();
		$this->Controller->startupProcess();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Controller);
		ClassRegistry::flush();
		parent::tearDown();
	}

/**
 * testRecaptcha
 *
 * @return void
 */
	public function testRecaptcha() {
		$this->Controller->request->data['recaptcha_challenge_field'] = 'something';
		$this->Controller->request->data['recaptcha_response_field'] = 'something';
		$this->assertFalse($this->Controller->Recaptcha->verify());
	}

/**
 * Checking that the helper was added by the component to the controllers helpers array
 *
 * @link https://github.com/CakeDC/recaptcha/issues/14
 */
	public function testHelperWasLoaded() {
		$this->assertTrue(in_array('Recaptcha.Recaptcha', $this->Controller->helpers));
	}
}
