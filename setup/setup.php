<?php
print "\n";
print getGreenColoredString("db   db d88888b d8888b. .88b  d88.  .d8b.  d8b   db\n");
print getGreenColoredString("88   88 88'     88  `8D 88'YbdP`88 d8' `8b 888o  88\n");
print getGreenColoredString("88ooo88 88ooooo 88oobY' 88  88  88 88ooo88 88V8o 88\n");
print getGreenColoredString("88OOO88 88OOOOO 88`8b   88  88  88 88OOO88 88 V8o88\n");
print getGreenColoredString("88   88 88.     88 `88. 88  88  88 88   88 88  V888\n");
print getGreenColoredString("YP   YP Y88888P 88   YD YP  YP  YP YP   YP VP   V8P\n");
print "\n";
print getCyanColoredString("Copying migration files...");

$projectPath = realpath(__DIR__ . '/../../../../');
recurse_copy(realpath(__DIR__ . '/base/'),$projectPath);

date_default_timezone_set('America/Chicago');
$timeStr = date("YmdHi", time());

rename($projectPath . "/migrations/create_users.php", $projectPath . "/migrations/" . $timeStr . "01_create_users.php");
rename($projectPath . "/migrations/create_accounts.php", $projectPath . "/migrations/" . $timeStr . "02_create_accounts.php");
rename($projectPath . "/migrations/create_failed_logins.php", $projectPath . "/migrations/" . $timeStr . "03_create_failed_logins.php");

print getCyanColoredString(" Finished!\n");

print "\n";
print getPurpleColoredString("Put the following path in your routes file:\n");
print getGreenColoredString("\$config['routes'] = array(\n");
print getGreenColoredString("  array('/login','sessions', 'login'),\n");
print getGreenColoredString("  array('/logout','sessions', 'logout')\n");
print getGreenColoredString(");\n");
print "\n";
print getPurpleColoredString("Put the following code in your ApplicationController:\n");
print getGreenColoredString("class ApplicationController extends Controller{\n");
print getGreenColoredString("	public \$auth;\n");
print getGreenColoredString("\n");
print getGreenColoredString("	function __construct(\$controller, \$action) {\n");
print getGreenColoredString("		parent::__construct(\$controller, \$action);\n");
print getGreenColoredString("\n");
print getGreenColoredString("		\$this->auth = new HermanAuth();\n");
print getGreenColoredString("\n");
print getGreenColoredString("		if(\$controller != 'login'){\n");
print getGreenColoredString("			\$this->auth->membersOnly();\n");
print getGreenColoredString("		}\n");
print getGreenColoredString("\n");
print getGreenColoredString("		if(\$this->auth->isLoggedIn()){\n");
print getGreenColoredString("			\$this->getCurrentUser();\n");
print getGreenColoredString("			\$this->getCurrentAccount();\n");
print getGreenColoredString("		}\n");
print getGreenColoredString("	}\n");
print getGreenColoredString("\n");
print getGreenColoredString("	function getCurrentUser(){\n");
print getGreenColoredString("		\$this->db->where('id', \$_SESSION['user_id']);\n");
print getGreenColoredString("		\$this->addToApplicationData('current_user', \$this->db->getOne('users'));\n");
print getGreenColoredString("	}\n");
print getGreenColoredString("\n");
print getGreenColoredString("	function getCurrentAccount(){\n");
print getGreenColoredString("		\$this->db->where('id', \$_SESSION['account_id']);\n");
print getGreenColoredString("		\$this->addToApplicationData('current_account', \$this->db->getOne('accounts'));\n");
print getGreenColoredString("	}\n");
print getGreenColoredString("}\n");
print "\n";
print getCyanColoredString("Have a nice day!\n\n");

function getCyanColoredString($string) {
	return getColoredString($string, '0;36');
}

function getLightCyanColoredString($string) {
	return getColoredString($string, '1;36');
}

function getGreenColoredString($string) {
	return getColoredString($string, '0;33');
}

function getPurpleColoredString($string) {
	return getColoredString($string, '0;35');
}

function getColoredString($string, $color_code) {
	$colored_string = "";
	$colored_string .= "\033[".$color_code."m";

	// Add string and end coloring
	$colored_string .=  $string . "\033[0m";

	return $colored_string;
}

function recurse_copy($src,$dst) {
	$dir = opendir($src);
	@mkdir($dst);
	while(false !== ( $file = readdir($dir)) ) {
		if (( $file != '.' ) && ( $file != '..' )) {
			if ( is_dir($src . '/' . $file) ) {
				recurse_copy($src . '/' . $file,$dst . '/' . $file);
			}else{
				copy($src . '/' . $file,$dst . '/' . $file);
			}
		}
	}
	closedir($dir);
}
?>
