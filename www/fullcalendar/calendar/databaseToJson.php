<?php
/**
 * This script converts database entries to a json output
 * this also includes local time offsets
 * PHP Version 5
 *
 * @author   Ivan Zhou
 */
require_once("/var/www/Bootstrap/loggedin.php");
require_once("/var/www/Bootstrap/db_connect.php");
require_once("/var/www/Bootstrap/reminder_options.php");

global $host, $user, $pwd, $db;
$con=mysqli_connect($host, $user,$pwd, $db);
if(mysqli_connect_errno()){
	echo "failed to connect";
}
$user_id=$_SESSION['user_id'];

$eventList = array();
$result=mysqli_query($con,"SELECT * FROM secure_login.Event where userID=$user_id");
while ($row=mysqli_fetch_assoc($result)) {
    $allday=FALSE;
    if(strcmp($row['allDay'],"true")==0){
 	$allday=TRUE;
    }
    $array = array(
   	"title"=>$row['title'],
    	"start"=>$row['startTime'],
   	"end"=>$row['endTime'],
	"id"=>$row['id'],
	"allDay"=>$allday
   );
  array_push($eventList, $array);
}

echo json_encode($eventList);
?>

