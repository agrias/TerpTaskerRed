<?php
/**
 * This script gets color based on the type and id given
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
$type=$_POST['type'];
$colorID=intval($_POST['id']);

//echo json_encode($id);
$query=$con->stmt_init();
if ($type == "context") {
    $query=$con->prepare("SELECT color FROM secure_login.Context where userID=? AND contextID=?");
} else {
    $query=$con->prepare("SELECT color FROM secure_login.Category where userID=? AND CategoryID=?");
}
$query->bind_param("ii",$user_id, $colorID);
$query->execute();
$query->bind_result($color);
$query->fetch();

echo json_encode($color);

mysqli_close($con);
