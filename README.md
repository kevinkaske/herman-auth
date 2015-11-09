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
Now that we have installed the Herman Auth library and has setup the database tables, we need to USE this library to restrict access.

Change your ApplicationController to look like the following:
```php
<?
class ApplicationController extends Controller{
	public $auth;

	function __construct($controller, $action) {
		parent::__construct($controller, $action);

		$this->auth = new HermanAuth();

		//Put in code that should be run in every request here
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
