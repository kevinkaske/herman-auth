# Herman Auth
A simple authentication lib for [Pooch](http://www.poochhq.com)

##About
Herman is a drop in authentication Lib for Pooch. It's simple. It gives you a users table and an accounts table as well as functions for
authenticating users.

##Installation
###Install Overview
Installation of Herman is through [composer](https://getcomposer.org). You simply need to add the following to your composer.json
file with the following contents:
```json
{
	"minimum-stability": "dev",
	"require": {
		"kevinkaske/herman": "dev-master"
	}
}
```

Then run `composer install`. Composer will then install the dependent libraries.

Now we need to setup the project. Run the following command `php vendor/kevinkaske/herman/setup.php`.

You will now need to run your migrations to setup the DB tables for Herman. You can do that by running `php vendor/bin/phinx migrate` from
the command line to run this migration.

###Integrate with Pooch
Now that we have installed the Herman Auth library and setup the database tables, we need to use this library to restrict access.

Change your ApplicationController to look like the following:
```php
<?
class ApplicationController extends Controller{
	public $auth;

	function __construct($controller, $action) {
		parent::__construct($controller, $action);

		$this->auth = new HermanAuth();

		if($controller != 'login'){
			$this->auth->membersOnly();
		}

		if($this->auth->isLoggedIn()){
			$this->getCurrentUser();
			$this->getCurrentAccount();
		}
	}

	function getCurrentUser(){
		$this->db->where("id", $_SESSION['user_id']);
		$this->addToApplicationData('current_user', $this->db->getOne("users"));
	}

	function getCurrentAccount(){
		$this->db->where("id", $_SESSION['account_id']);
		$this->addToApplicationData('current_account', $this->db->getOne("accounts"));
	}
}
?>
```
The `__construct` method is called each time a request comes in. We are simply creating a new instance of
HermanAuth and picking what rules we want to apply to restrict access. We are applying the following logic:
```php
if($controller != 'login'){
	$this->auth->membersOnly();
}
```
With this we are checking the current controller this request is being routed to. Our login controller is the controller we use to create
sessions so we will want to give that controller a pass and require a session for every other controller. You could reverse this logic if
you want to explicitly list all controllers that you want to limit access to. For example:
```php
if($controller == 'restricted' || $controller == 'members'){
	$this->auth->membersOnly();
}
```
The last piece of this is just loading the currently logged in user and account and making is available via
`$this->application_data['current_account']`  and `$this->application_data['current_user']`.
