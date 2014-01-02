<?php
/**
 * This script adds an event in the database
 * PHP Version 5
 *
 * @author   Ivan Zhou, Billy Wu
 */
require_once("/var/www/Bootstrap/loggedin.php");
require_once("/var/www/Bootstrap/db_connect.php");
global $host, $user, $pwd, $db;
$con=new mysqli($host, $user,$pwd, $db);
if(mysqli_connect_errno()){
	echo "failed to connect";
}
$user_id=intval($_SESSION['user_id']);
$id=60;
$start=intval($_POST['start']);
$end=intval($_POST['end']);
$title=$_POST['title'];
$allDay=$_POST['allDay'];
//$location=$_POST['loc'];
//$url=$_POST['url'];
$category=intval($_POST['category']);
//$repeat=$_POST['repeat'];
//$repeatLength=$_POST['repeatLength'];
//$repeatFreq=intval($_POST['repeatFreq']);
//$repeatEnd=intval($_POST['repeatEnd']);
//$emailReminder=intval($_POST['emailReminder']);
//$popupReminder=intval($_POST['popupReminder']);
//$description=$_POST['description'];


//$query="INSERT INTO secure_login.Event (userID,id,startTime,endTime,title,location,url,repeatB,repeatLength, repeatFreq, repeatEnd) 
//			 VALUES (%d,%d,%d,%d,'%s','%s','%s','%s','%s',%d,%d)";
//$query=sprintf($query,$user_id,$id,$start,$end,$title,$location,$url,$repeat,$repeatLength,$repeatFreq,$repeatEnd,$user_id);
$query=$con->stmt_init();
$query=$con->prepare("INSERT INTO secure_login.Event 
			(userID,id,startTime,endTime,categoryID, title,allDay) 
			 VALUES (?,?,?,?,?,?,?)");
$query->bind_param("iiiiiss",$user_id,$id,$start,$end,$category,$title,$allDay);
$query->execute();
?>
