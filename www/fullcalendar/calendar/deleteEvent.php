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
$user_id=intval($_SESSION['user_id']);

$id=intval($_POST['id']);

$query=$con->stmt_init();
$query=$con->prepare("DELETE FROM secure_login.Event WHERE id=? AND userID=?");
$query->bind_param("ii",$id,$user_id);
$query->execute();
?>
