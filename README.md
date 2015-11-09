# Herman Auth
A simple authentication lib for [Pooch](http://www.poochhq.com)

##About
Herman is a drop in authentication Lib for Pooch. It's simple. It gives you a users table and an accounts table as well as functions for
authenticating users.

##Installation
###Installation Process (Detailed Overview)
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

You will now need to run your migrations to setup the DB tables for Herman. You can do that by running `php vendor/bin/phinx migrate` from the command line to run this migration.
