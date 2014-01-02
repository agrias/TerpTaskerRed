<?php
include 'loggedin.php';
$con=mysqli_connect("127.0.0.1","root","Hack5hack%hack","secure_login");

if (mysqli_connect_errno($con))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
$user_id = $_SESSION['user_id'];


 $obj = json_decode($_POST['task']);
 $obj = (array)$obj;
 //var_dump((array)$obj);
 var_dump($obj["title"]);


if(mysqli_query($con,"INSERT INTO secure_login.Task (userID, categoryID, contextID, title, description,start_date,deadline,estimated_time, prerequisite,recurrenceID,status,postpone_count,email_reminder,popup_reminder)
VALUES ('$user_id','".$obj["category"]."','2','".$obj["title"]."','".$obj["description"]."','".$obj["start"]."','".$obj["deadline"]."','".$obj["time"]."','".$obj["prereq"]."',NULL,NULL,NULL,NULL,NULL);"))
var_dump("here");
else
var_dump("there");

mysqli_close($con);
?>
