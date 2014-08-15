<?php
session_start();
include 'config.php';

// I made it better!
function get_random_string($length)
{
	$valid_chars = "qwertyuiopasdfghjklzxcvbnm@£&%{[]}";
	$num_valid_chars = strlen($valid_chars);
	
	$random_string = "";
	for ($i = 0; $i < $length; ++$i)
	{
		$random_pick = random_int(0, $num_valid_chars - 1);
		$random_string .= $valid_chars[$random_pick];
	}
	return $random_string;
}

// Get a cryptographically secure random integer within a given range
function random_int($min, $max) {
	if ($max <= $min) {
		trigger_error("Invalid parameters passed to random_int()", E_USER_WARNING);
		return null;
	}
	
	$rval = 0;
	$range = $max - $min;
	
	$need_bits = ceil(log($range, 2));
	
	// Create a bitmask
	$mask = intval( pow(2, $need_bits) - 1); // 7776 -> 8191
	
	// Number of random bytes to fetch
	$need_bytes = ceil($need_bits / 8);
	
	// Let's grab a random byte that falls within our range
	do {
		$rval = intval(get_random_bytes($need_bytes) & $mask);
	} while($rval> $range);
	// We now have a random value in the range between $min and $max, so...
	
	// Let's return the random value + the minimum value
	return $rval + $min;
}

// Get a cryptographically secure sequence of random bytes
function get_random_bytes($number)
{
	if($number < 1) {
		$number = 1;
	}
	$number = intval($number);
	// Now that we've got that out of our system
	$buf = '';
	if (is_readable('/dev/urandom')) {
		$fp = fopen('/dev/urandom', 'rb');
		if($fp !== false) {
			$buf = fread($fp, $number);
		}
	}
	if (empty($buf) && function_exists('mcrypt_create_iv') && defined('MCRYPT_DEV_URANDOM')) {
		$buf = mcrypt_create_iv($number, MCRYTP_DEV_URANDOM);
	}
	if (empty($buf) && function_exists('openssl_random_pseudo_bytes')) {
		$buf = openssl_random_pseudo_bytes($number);
	}
	if (empty($buf)) {
		trigger_error("No suitable random number generator exists!", E_USER_ERROR);
	}
	return $buf;
}
 
function generateNonce() {
	$nonce = hash('sha256', get_random_string(20));
	return $nonce;
}

// Check if the current user is logged in
// Returns user id form DB, or -1 if not logged in
function isLoggedIn() {
	global $host, $user, $password, $database, $domain;
	// Check if $_SESSION['nonce'] is set. If not set it with new psudo random nonce
	if ($_SESSION['nonce'] == "") {
		$nonce = generateNonce();	
		$message = "$domain/sqrl_verify.php?webnonce=$nonce";
		$_SESSION['nonce'] = "$message"; // set the session
	
		// insert new nonce in DB
		mysql_connect($host,$user,$password);
		@mysql_select_db($database) or die( "Unable to select database");
		$unixtime = time(); // time so we can delete it when it gets old
		mysql_query("INSERT INTO nonce VALUES ('','$message', $unixtime ,-1)");  // add the new nonce
		mysql_close();
	}
	
	// *** Check if the user has logged in
	mysql_connect($host,$user,$password);
	@mysql_select_db($database) or die( "Unable to select database");

	// get the nonce from db
	$noce = $_SESSION['nonce']; 
	$query="SELECT * FROM nonce WHERE message='$noce'";
	$result=mysql_query($query);
	$num_rows = mysql_num_rows($result);
	if ($num_rows < 1) exit("Cant find the nonce");  // this should never happen
	$user_id=mysql_result($result,0,"user"); // the user associated with the nonce
	mysql_close();
	
	return $user_id;
}
?>
