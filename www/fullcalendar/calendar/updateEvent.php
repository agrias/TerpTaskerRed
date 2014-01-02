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
$cat_id=intval($_POST['category']);
$title=$_POST['title'];
$id=intval($_POST['id']);
$location=$_POST['loc'];
$url=$_POST['url'];

$allDay=$_POST['allDay'];
$repeat=$_POST['repeat'];
$repeatLength=$_POST['repeatLength'];
$repeatFreq=intval($_POST['repeatFreq']);
$repeatEnd=intval($_POST['repeatEnd']);
$emailReminder=($_POST["emailReminder"]=="NULL")?NULL:intval($_POST['emailReminder']);
$popupReminder=($_POST["popupReminder"]=="NULL")?NULL:intval($_POST['popupReminder']);
$description=$_POST['description'];

$query=$con->stmt_init();
$query=$con->prepare("UPDATE Event SET categoryID=?,title=?,location=?,url=?, allDay=?, repeatB=?, repeatLength=?, repeatFreq=?, repeatEnd=?, emailReminder=?, popupReminder=?,description=? WHERE id=?");
$query->bind_param("issssssiiiisi",$cat_id,$title,$location,$url,$allDay,$repeat,$repeatLength, $repeatFreq, $repeatEnd, $emailReminder, $popupReminder, $description,$id);
$query->execute();

?>

