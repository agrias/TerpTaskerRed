<?php
/**
 * This script adds an timeblock for context in the database
 * PHP Version 5
 *
 * @author   Billy Wu, Ivan Zhou
 */
require_once("/var/www/Bootstrap/loggedin.php");
require_once("/var/www/Bootstrap/db_connect.php");
require_once("/var/www/Bootstrap/reminder_options.php");
global $host, $user, $pwd, $db;
$con=new mysqli($host, $user,$pwd, $db);
if(mysqli_connect_errno()){
	echo "failed to connect";
}

$id=intval($_POST['id']);
$user_id=intval($_SESSION['user_id']);
$contextID=intval($_POST['contextID']);
$start=intval($_POST['start']);
$end=intval($_POST['end']);
$allDay=$_POST['allDay'];
$repeat=$_POST['repeat'];
$repeatLength=$_POST['repeatLength'];
$repeatFreq=intval($_POST['repeatFreq']);
$repeatEnd=intval($_POST['repeatEnd']);
$emailReminder=($_POST["emailReminder"]=="NULL")?NULL:intval($_POST['emailReminder']);
$popupReminder=($_POST["popupReminder"]=="NULL")?NULL:intval($_POST['popupReminder']);

$query=$con->stmt_init();
$query=$con->prepare("INSERT INTO secure_login.TimeBlock (blockID,contextID,startTime,endTime,allDay,repeatB,repeatLength,repeatFreq, repeatEnd,emailReminder,popupReminder) 
			 VALUES (?,?,?,?,?,?,?,?,?,?,?)");

$query->bind_param("iiiisssiiii",$id,$contextID,$start,$end,$allDay,$repeat,$repeatLength,$repeatFreq,$repeatEnd,$emailReminder, $popupReminder);
if(!$query->execute()){
	echo json_encode($query->error);
}

mysqli_close($con);
?>
