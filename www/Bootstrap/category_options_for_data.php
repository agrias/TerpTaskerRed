<?php

require_once("loggedin.php");
require_once("db_connect.php");

function getCategoryOptions($id) {
global $host, $user, $pwd, $db;
$con=mysqli_connect($host,$user,$pwd,$db);
	$user_id =  $_SESSION['user_id'];
if(!is_numeric($user_id)) {
	echo "Potential SQL injection attempt. Exiting.";
	exit();
}
	$my_cat = mysqli_query($con,"SELECT * FROM secure_login.Category WHERE userID = $user_id");
	while ($cat = mysqli_fetch_array($my_cat)){
		echo ($id == $cat['categoryID']) ? "<option value='{$cat['categoryID']}' selected>{$cat['name']}</option>" : "<option value='{$cat['categoryID']}'>{$cat['name']}</option>";
	}
}
?>