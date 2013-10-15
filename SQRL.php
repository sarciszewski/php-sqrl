<?php
session_start();
include 'config.php';

// This needs to be better
function get_random_string($length)
{
	$valid_chars = "qwertyuiopasdfghjklzxcvbnm@£&%{[]}";
	$num_valid_chars = strlen($valid_chars);
 
	$random_string = "";
	for ($i = 0; $i < $length; $i++)
	{
		$random_pick = mt_rand(1, $num_valid_chars);
		$random_string .= $valid_chars[$random_pick-1];
	}
	return $random_string;
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