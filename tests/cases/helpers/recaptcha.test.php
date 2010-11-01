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
App::import('Helper', array('Recaptcha.Recaptcha', 'Html'));

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
		$view = new View(new PostsTestController());
		$this->Recaptcha = new RecaptchaHelper();
		$this->Recaptcha->Html = new HtmlHelper();
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
		$result = $this->Recaptcha->display(array('publicKey' => 'TestKey'));
		$expected = <<<TEXT
<script type="text/javascript">
//<![CDATA[
var RecaptchaOptions = {
	theme: "white",
	lang: "en-us"
};
//]]>
</script><script type="text/javascript" src="http://api-secure.recaptcha.net/challenge?k=TestKey"></script><noscript>
	<iframe src="http://api-secure.recaptcha.net/noscript?k=TestKey" height="300" width="500" frameborder="0"></iframe><br/>
	<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
	<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
</noscript>
TEXT;
		$this->assertIdentical($expected, $result);
	}
}
