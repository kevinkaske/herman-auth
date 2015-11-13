<?php
print getGreenColoredString("db   db d88888b d8888b. .88b  d88.  .d8b.  d8b   db\n");
print getGreenColoredString("88   88 88'     88  `8D 88'YbdP`88 d8' `8b 888o  88\n");
print getGreenColoredString("88ooo88 88ooooo 88oobY' 88  88  88 88ooo88 88V8o 88\n");
print getGreenColoredString("88OOO88 88OOOOO 88`8b   88  88  88 88OOO88 88 V8o88\n");
print getGreenColoredString("88   88 88.     88 `88. 88  88  88 88   88 88  V888\n");
print getGreenColoredString("YP   YP Y88888P 88   YD YP  YP  YP YP   YP VP   V8P\n");
print "\n";
print getCyanColoredString("Making migration files...");

$projectPath = realpath(__DIR__ . '/../../../../');
recurse_copy(realpath(__DIR__ . '/base/'),$projectPath);

date_default_timezone_set('America/Chicago');
$timeStr = date("YmdHi", time());

rename($projectPath . "migrations/create_users.php", $projectPath . "migrations/" . $timeStr . "01_create_users.php");
rename($projectPath . "migrations/create_accounts.php", $projectPath . "migrations/" . $timeStr . "02_create_accounts.php");
rename($projectPath . "migrations/create_failed_logins.php", $projectPath . "migrations/" . $timeStr . "03_create_failed_logins.php");

print getCyanColoredString("Finished!\n\n");

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
