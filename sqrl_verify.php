<?php
include 'config.php';	

// Just for testing, sends all requests to http://ed25519.herokuapp.com/api/Verify
// Should do ECC25519 PHP implementation
function verify($message, $signature, $publicKey) 
{
	$postdata = http_build_query(
		array(
			'message' => $message,
			'signature' => $signature,
			'publicKey' => $publicKey
		)
	);

	$opts = array('http' =>
		array(
			'method'  => 'POST',
			'header'  => 'Content-type: application/x-www-form-urlencoded',
			'content' => $postdata
		)
	);

	$context  = stream_context_create($opts);
	$result = file_get_contents('http://ed25519.herokuapp.com/api/Verify', false, $context);
	echo "$result<br>";
	
	if ($result === '{"result":true}')
	{
		return true;
	}
	else
	{
		return false;
	}
}

// *** Main ****
$message = $_POST['message'];
$signature = $_POST['signature'];
$publicKey = $_POST['publicKey'];

$v = verify($message, $signature, $publicKey);

if ($v) { // if the message got verified
	mysql_connect("mysql9.000webhost.com",$user,$password);
	@mysql_select_db($database) or die( "Unable to select database");
	
	// find the nonce we made in DB
	$query="SELECT * FROM nonce WHERE message='$message'";
	$result=mysql_query($query);
	$msg=mysql_result($result,0,"message"); // that message that should have been signed	
	$nonce_user=mysql_result($result,0,"user"); 
	
	if ($nonce_user != -1) exit("Error nonce used"); // has it been associated with a user
	if ($message != $msg) exit("Error");  // is the message the same in DB and session?
		
	// ****** Get the user info
	$query="SELECT * FROM users WHERE pubkey='$publicKey'";
	$result=mysql_query($query);
	$num_rows = mysql_num_rows($result);
	
	// If it's a new user add him to database
	if ($num_rows == 0) 
	{
		mysql_query("INSERT INTO users VALUES ('','unknown', '$publicKey')");  // add the new user
	}
	
	// get user info
	$query="SELECT * FROM users WHERE pubkey='$publicKey'";
	$result=mysql_query($query);
	$user_id=mysql_result($result,0,"id"); // get user ID
	
	// update the nonce with user info
	mysql_query("UPDATE nonce SET user = '$user_id' WHERE message LIKE '$message'");	  	
	
	mysql_close();
	echo "Verified";
}
else
{
	echo "Error";
}
?>
