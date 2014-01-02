<?php
/**
 * This script adds an event in the database
 * PHP Version 5
 *
 * @author   Ivan Zhou, Billy Wu
 */
require_once("/var/www/Bootstrap/loggedin.php");
require_once("/var/www/Bootstrap/db_connect.php");
require_once("/var/www/Bootstrap/reminder_options.php");
global $host, $user, $pwd, $db;
$con=new mysqli($host, $user,$pwd, $db);
if(mysqli_connect_errno()){
	echo "failed to connect";
}
$user_id=intval($_SESSION['user_id']);
$id=intval($_POST['id'])%2147483647;
$start=intval($_POST['start']);
$end=intval($_POST['end']);
$title=$_POST['title'];
$allDay=$_POST['allDay'];
$location=$_POST['loc'];
$url=$_POST['url'];
$category=intval($_POST['category']);
$repeat=$_POST['repeat'];
$repeatLength=$_POST['repeatLength'];
$repeatFreq=intval($_POST['repeatFreq']);
$repeatEnd=intval($_POST['repeatEnd']);
$emailReminder=($_POST["emailReminder"]=="NULL")?NULL:intval($_POST['emailReminder']);
$popupReminder=($_POST["popupReminder"]=="NULL")?NULL:intval($_POST['popupReminder']);
$description=$_POST['description'];

$query=$con->stmt_init();
$query=$con->prepare("INSERT INTO secure_login.Event 
			(userID,id,startTime,endTime,title,allDay,location,url,repeatB,repeatLength,repeatFreq, repeatEnd,emailReminder,popupReminder,description, categoryID) 
			 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
$query->bind_param("iiiissssssiiiisi",$user_id,$id,$start,$end,$title,$allDay,$location,$url,$repeat,$repeatLength,$repeatFreq,$repeatEnd,$emailReminder, $popupReminder, $description, $category);
if(!$query->execute()){
	echo json_encode($query->error);
}

mysqli_close($con);
?>
