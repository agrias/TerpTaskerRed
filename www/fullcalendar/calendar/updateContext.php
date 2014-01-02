<?php
/**
 * This script updates an event in the database
 * PHP Version 5
 *
 * @author   Ivan Zhou
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
$query=$con->prepare("UPDATE TimeBlock SET contextID=?, startTime=?,endTime=?,allDay=?,emailReminder=?,popupReminder=?,repeatB=?,repeatLength=?, 
repeatFreq=?, repeatEnd=? WHERE blockID=?");
$query->bind_param("iiisiissiii", $contextID, $start,$end,$allDay,$emailReminder,$popupReminder, $repeat, $repeatLength, $repeatFreq, $repeatEnd, $id);
$query->execute();
mysqli_close($con);
?>

