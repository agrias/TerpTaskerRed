<?php

//Postpone context block

require_once("/var/www/Bootstrap/loggedin.php");
require_once("/var/www/Bootstrap/db_connect.php");

$con=mysqli_connect("127.0.0.1","root","Hack5hack%hack","secure_login");

$user_id = $_SESSION['user_id'];
$query = mysqli_prepare($con, "SELECT t.blockID, t.contextID, c.userID FROM `TimeBlock` t INNER JOIN `Context` c ON t.contextID = c.contextID WHERE t.blockID = ?");

mysqli_stmt_bind_param($query,"i",intval($_GET['id']));

mysqli_stmt_execute($query);
mysqli_stmt_store_result($query);
$numrows = mysqli_stmt_num_rows($query);

if($numrows === 0){
	echo "{result: failure, note: 'improper permissions'}";
}else{
	// the user has proper access.
	//
	//"UPDATE `TimeBlock` SET `startTime`= LEAST( `endTime`, (`startTime` + (15 * 60)) ) WHERE `blockID` = 1"
	$q2 = mysqli_prepare($con, "UPDATE `TimeBlock` SET `startTime`= LEAST( `endTime`, (`startTime` + (15 * 60)) ) WHERE `blockID` = ?" );
	mysqli_stmt_bind_param($q2,"i",intval($_GET['id']));
	mysqli_stmt_execute($q2);
	echo "{result: success, note: '15 minutes added'}";
}

mysqli_close($con);
?>