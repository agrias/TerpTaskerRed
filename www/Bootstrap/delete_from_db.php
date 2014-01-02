<?php
require_once("loggedin.php");
require_once("db_connect.php");
include '../Login/functions.php';

$con = mysqli_connect($host,$user,$pwd,$db);
//$mysqli = new mysqli($host,$user,$pwd,$db);
$user_id =  $_SESSION['user_id'];
if(!is_numeric($user_id)) {
	echo "Potential SQL injection attempt. Exiting.";
	exit();
}

//print_r(get_defined_vars());

if (isset($_POST['category_id'])) {
	$category_id = $_POST['category_id'];
if(!is_numeric($category_id)) {
	echo "Potential SQL injection attempt. Exiting.";
	exit();
}
	$categories = mysqli_query($con,"SELECT * FROM secure_login.Category WHERE userID = $user_id");
	while ($my_cat = mysqli_fetch_array($categories)) {
		if($my_cat['name']=="None") {
			$none_id = $my_cat['categoryID'];
		}
	}
	
	if (isset($_POST['events'])) {
		mysqli_query($con, "DELETE FROM secure_login.Event WHERE userID=$user_id AND categoryID=$category_id");
	} else {
		mysqli_query($con, "UPDATE secure_login.Event SET categoryID=$none_id WHERE userID=$user_id AND categoryID=$category_id");
	}
	if (isset($_POST['tasks'])) {
		mysqli_query($con, "DELETE FROM secure_login.mtt_todolist WHERE userID=$user_id AND categoryID=$category_id");
	} else {
		mysqli_query($con, "UPDATE secure_login.mtt_todolist SET categoryID=$none_id WHERE userID=$user_id AND categoryID=$category_id");
	}
	mysqli_query($con, "DELETE FROM secure_login.Category WHERE userID=$user_id AND categoryID=$category_id");
	mysqli_close($con);
	header("Location: " . "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/calendar.php");
}

elseif (isset($_POST['context_id'])) {
	$context_id = $_POST['context_id'];
if(!is_numeric($context_id)) {
	echo "Potential SQL injection attempt. Exiting.";
	exit();
}
	$contexts = mysqli_query($con,"SELECT * FROM secure_login.Context WHERE userID = $user_id");
	while ($my_con = mysqli_fetch_array($contexts)) {
		if($my_con['name']=="None") {
			$none_id = $my_con['contextID'];
		}
	}
	if (isset($_POST['time_blocks'])) {
		mysqli_query($con, "DELETE FROM secure_login.TimeBlock WHERE contextID=$context_id");
	} else {
		mysqli_query($con, "UPDATE secure_login.TimeBlock SET contextID=$none_id WHERE contextID=$context_id");
	}
	mysqli_query($con, "UPDATE secure_login.mtt_todolist SET contextID=$none_id WHERE contextID=$context_id");
	mysqli_query($con, "DELETE FROM secure_login.Context WHERE userID=$user_id AND contextID=$context_id");
	mysqli_close($con);
	header("Location: " . "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/calendar.php");
}
elseif (isset($_POST['password'])) {
	$member = mysqli_query($con,"SELECT * FROM secure_login.members WHERE id = $user_id");
	$member = mysqli_fetch_array($member);
	$realpass = $member['password'];
	$email = $member['email'];
	$salt = $member['salt'];
	
	$password = $_POST['password'];


	$checkPass = hash('sha512', $password.$salt);

	if (trim($realpass) == trim($checkPass)) {
		mysqli_query($con, "DELETE FROM secure_login.members WHERE id=$user_id");
		mysqli_query($con, "DELETE FROM secure_login.Call_History WHERE userID=$user_id");
              mysqli_query($con, "DELETE FROM secure_login.Text_Messages WHERE userID=$user_id");
              mysqli_query($con, "DELETE FROM secure_login.Contacts WHERE userID=$user_id");
		mysqli_query($con, "DELETE FROM secure_login.Category WHERE userID=$user_id");
		mysqli_query($con, "DELETE FROM secure_login.Context WHERE userID=$user_id");
		mysqli_query($con, "DELETE FROM secure_login.Emails WHERE userID=$user_id");
		mysqli_query($con, "DELETE FROM secure_login.Event WHERE userID=$user_id");
		mysqli_query($con, "DELETE FROM secure_login.Time_Block WHERE userID=$user_id");
		mysqli_query($con, "DELETE FROM secure_login.User_Activity WHERE userID=$user_id");
		mysqli_query($con, "DELETE FROM secure_login.login_attempts WHERE userID=$user_id");
		mysqli_query($con, "DELETE FROM secure_login.mtt_tag2task WHERE userID=$user_id");
		mysqli_query($con, "DELETE FROM secure_login.mtt_tags WHERE userID=$user_id");
		mysqli_query($con, "DELETE FROM secure_login.mtt_todolist WHERE userID=$user_id");
		mysqli_close($con);
		header("Location: " . "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/logout.php");
	}
	else {
		header('Location: settings.php?error_delete=1');
	}
}


?>
