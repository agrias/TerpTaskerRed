
<?php
include '../loggedin.php';
$con=mysqli_connect("127.0.0.1","root","Hack5hack%hack","secure_login");

if (mysqli_connect_errno($con))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
$user_id = $_SESSION['user_id'];
var_dump($user_id);
var_dump(intval($user_id));
 date_default_timezone_set("America/New_York");
 $updated = microtime(true);
 $time = date("Y\-m\-d h:i:s",strtotime("-20 minutes"));
 $ip_address = $_SERVER['REMOTE_ADDR']; 
	
$stmt = mysqli_prepare($con, "INSERT INTO secure_login.user_Activity (userID, timestamp, ip_address, action) 
				VALUES (?,?,?,?)");
$id = 20;
$action = 'login';

mysqli_stmt_bind_param($stmt, 'isss',intval($user_id), $time, $ip_address,$action);
mysqli_stmt_execute($stmt);

header("Location: ../index.php");
mysqli_close($con);
?>
