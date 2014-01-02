
<?php

include 'loggedin.php';

$con=mysqli_connect("127.0.0.1","root","Hack5hack%hack","secure_login");



if(mysqli_connect_errno($con)) {
        echo "Failed to connect to MySQL:".mysqli_connect_error();
}


$user_id = $_SESSION['user_id'];


$obj = json_decode($_POST['task']);
$obj = (array)$obj;
var_dump($obj["title"]);

$taskID = $obj["taskid"];
$categoryID = $obj["category"];


mysqli_query($con, "UPDATE Task SET categoryID=$categoryID WHERE taskID=$taskID");


mysqli_close($con);


?>



