Setup
=====

To use the recaptcha plugin its required to include the following two lines in your `/app/Config/bootstrap.php` file.

```php
Configure::write('Recaptcha.publicKey', 'your-public-api-key');
Configure::write('Recaptcha.privateKey', 'your-private-api-key');
```

Don't forget to replace the placeholder text with your actual keys!

Keys can be obtained for free from the [Recaptcha website](http://www.google.com/recaptcha).

Controllers that will be using recaptcha require the Recaptcha Component to be included. Through inclusion of the component, the helper is automatically made available to your views.

```php
public $components = array('Recaptcha.Recaptcha');
```
In the view simply call the helpers `display()` method to render the recaptcha input:

```php
echo $this->Recaptcha->display();
```

To render multiple recatpchas on a page, pass `['explicit' => true]`:

```php
echo $this->Recaptcha->display(['explicit' => true]);
```

You could select another theme, setup it as parameter, for instance:

```php
echo $this->Recaptcha->display(array(
	'recaptchaOptions' => array(
			'theme' => 'dark'
		)
	)
);
```

For the complete list of configuration options, take a look here: [https://developers.google.com/recaptcha/docs/display#config](https://developers.google.com/recaptcha/docs/display#config). Please note you don't need to prefix option names with `data-`.

To check the result simply do something like this in your controller:

```php
if ($this->request->is('post')) {
	if ($this->Recaptcha->verify()) {
		// do something, save you data, login, whatever
	} else {
		// display the raw API error
		$this->Session->setFlash($this->Recaptcha->error);
	}
}
````
