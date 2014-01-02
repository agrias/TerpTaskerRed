
<?php

include 'loggedin.php';

$con=mysqli_connect("127.0.0.1","root","Hack5hack%hack","secure_login");



if(mysqli_connect_errno($con)) {
        echo "Failed to connect to MySQL:".mysqli_connect_error();
}

$obj = json_decode($_POST['user']);
$obj = (array)$obj;
var_dump($obj);
$id = $obj["id"];
$first = $obj["first_name"];
$last = $obj['last_name'];
$email = $obj['email'];
$admin = $obj['admin'];
$active = $obj['active'];
//var_dump($obj);


$stmt = mysqli_prepare($con, "UPDATE members SET firstname = ?, lastname = ?, email = ? , Administrator = ?, active = ? WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'sssiii', $first, $last, $email, $admin, $active, $id);
mysqli_stmt_execute($stmt);

if($active == 0){
	$mystmt = mysqli_prepare($con, "UPDATE members SET DeactivationDate = ? WHERE id = ?");
	mysqli_stmt_bind_param($mystmt, 'ii', time(), $id);
	mysqli_stmt_execute($mystmt);
}



header('Location: admin_edit.php?success=1');
mysqli_close($con);

header('Location: admin_edit.php?success=1');

?>

