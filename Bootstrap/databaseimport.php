<?php
/**
 * This script imports a ics file's contents into the database
 * PHP Version 5
 *
 * @author   Ivan Zhou
 */

header('Location: calendar.php');
require_once("/var/www/Bootstrap/loggedin.php");
require_once("/var/www/Bootstrap/db_connect.php");
require 'class.iCalReader.php';
global $host, $user, $pwd, $db;
$con=new mysqli($host, $user,$pwd, $db);
if(mysqli_connect_errno()){
	echo "failed to connect";
}
$user_id=intval($_SESSION['user_id']);
if($_FILES==null){
	exit(0);
}
$ical   = new ICal($_FILES["file"]["tmp_name"]);
$events = $ical->events();
$eventList = array();
$con1=mysqli_connect($host,$user,$pwd, $db);
$r=mysqli_query($con1,"SELECT categoryID from Category WHERE userID='".$user_id."' AND name='None'");
$row=mysqli_fetch_array($r);
$none=$row['categoryID'];
//echo $none;
foreach ($events as $event) {
	$title=$event['SUMMARY'];
    	$start=$ical->iCalDateToUnixTimestamp($event['DTSTART']);
   	$end=$ical->iCalDateToUnixTimestamp($event['DTEND']);
	$id=rand(0,2147483647);
        $allDay="false";
	if((($start-$end)/60/60) > 8) {
	    $allDay = "true";
	}
	$query=$con->stmt_init();
	
	$query=$con->prepare("INSERT INTO secure_login.Event (userID,id,title,startTime,endTime,allDay, categoryID) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $query->bind_param("iisiisi",$user_id,$id,$title,$start,$end,$allDay, $none);
	if(!$query->execute()){
		echo $query->error;
	}
}
?>

