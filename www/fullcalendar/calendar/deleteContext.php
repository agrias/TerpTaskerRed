<?php
/**
 * This script deletes an event from the database
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

$query=$con->stmt_init();
$query=$con->prepare("DELETE FROM secure_login.TimeBlock WHERE blockID=?");
$query->bind_param("i",$id);
$query->execute();
?>
