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
$user_id=intval($_SESSION['user_id']);

$id=intval($_POST['id']);
$start=intval($_POST['start']);
$end=intval($_POST['end']);
$query=$con->stmt_init();
$query=$con->prepare("UPDATE secure_login.Event SET startTime=?, endTime=? WHERE id=? AND userID=?");
$query->bind_param("iiii",$start,$end,$id,$user_id);
$query->execute();

$query1=$con->stmt_init();
$query1=$con->prepare("UPDATE secure_login.TimeBlock SET startTime=?, endTime=? WHERE blockID=?");
$query1->bind_param("iii",$start,$end,$id);
$query1->execute();

?>

