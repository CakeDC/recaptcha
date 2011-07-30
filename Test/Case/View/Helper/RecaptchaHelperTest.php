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

App::uses('Helper', 'View');
App::uses('ClassRegistry', 'Utility');
App::uses('Controller', 'Controller');
App::uses('Model', 'Model');
App::uses('RecaptchaHelper', array('Recaptcha.View/Helper'));

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
 * @return void
 */
	function setUp() {
		$view = new View(new PostsTestController());
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
}
