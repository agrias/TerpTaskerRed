<?php

include 'loggedin.php';

$con=mysqli_connect("127.0.0.1","root","Hack5hack%hack","secure_login");



if(mysqli_connect_errno($con)) {
        echo "Failed to connect to MySQL:".mysqli_connect_error();
}


$user_id = $_SESSION['user_id'];


$obj = json_decode($_POST['task']);
$obj = (array)$obj;
$email = $obj["email"];

$stmt = mysqli_prepare($con, "DELETE FROM members WHERE email = ?");
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
 
mysqli_close($con);


?>

