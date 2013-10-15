<?php
session_start();
include 'SQRL.php';

$user_id = isLoggedIn();
if ($user_id != -1) { // if there is a associated user
	mysql_connect($host,$user,$password);
	@mysql_select_db($database) or die( "Unable to select database");
	// Get user info and show that he is logged in
	$query="SELECT * FROM users WHERE id=$user_id";
	$result=mysql_query($query);
	$username=mysql_result($result,0,"username"); // that message that should have been signed	
	mysql_close();
	
	echo "logged in as $username! WOOOOOOOOHO <br><br>";
	echo "<a href='logout.php'>logout</a> ";
	
	exit();
}
?>

<html>
Test page for sqrl authentication<br>
<?php 
	$nonce = $_SESSION['nonce'];
	$URL = "qrl%3A%2F%2F$nonce";
?>

<img src="https://chart.googleapis.com/chart?chs=100x100&cht=qr&chl=<?php  echo "$URL"; ?>&choe=UTF-8" title="SQRL" />
<a href="<?php $nonce = $_SESSION['nonce']; echo "$URL"; ?>">SQRL</a> 
</html>