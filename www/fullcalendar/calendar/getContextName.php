<?php
/**
 * This script gets context name based on id given
 * PHP Version 5
 *
 * @author  Billy Wu
 */
require_once("/var/www/Bootstrap/loggedin.php");
require_once("/var/www/Bootstrap/db_connect.php");
global $host, $user, $pwd, $db;
$con=new mysqli($host, $user,$pwd, $db);
if(mysqli_connect_errno()){
	echo "failed to connect";
}
$user_id=intval($_SESSION['user_id']);
$conID=intval($_POST['id']);

$query=$con->stmt_init();
$query=$con->prepare("SELECT name FROM secure_login.Context where userID=? AND contextID=?");
$query->bind_param("ii",$user_id, $conID);
$query->execute();
$query->bind_result($name);
$query->fetch();

echo json_encode($name);
