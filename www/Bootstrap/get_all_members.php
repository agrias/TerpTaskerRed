<?php
include 'loggedin.php';
$con=mysqli_connect("127.0.0.1","root","Hack5hack%hack","secure_login");

if (mysqli_connect_errno($con))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
$user_id =  $_SESSION['user_id'];
$members = mysqli_query($con,"SELECT id, email, firstname, lastname, Administrator, active FROM secure_login.members ");
$users = array();
$count = 0;
while ($user = mysqli_fetch_array($members)){
//var_dump($user);
$users[$count] = $user;
$count = $count + 1;
}
//var_dump($users);

echo $users = json_encode($users);
  


mysqli_close($con);
?>
