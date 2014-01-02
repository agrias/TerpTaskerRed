<?php
require_once("loggedin.php");
require_once("db_connect.php");

$mysqli = new mysqli($host,$user,$pwd,$db);
$user_id =  $_SESSION['user_id'];
$description = $_POST['description'];

//print_r(get_defined_vars());

if (isset($_POST['category'])) {
	$name = $_POST['category'];
	$description = $_POST['description'];
	$color = substr($_POST['cat_color1'],1);
	$category_id = $_POST['category_id'];
	if ($stmt = $mysqli->prepare("UPDATE Category SET name=?, description=?, color=? WHERE userID=? AND categoryID=?")) {    
		$stmt->bind_param('sssii',$name,$description,$color,$user_id,$category_id); 
		$stmt->execute();
	}
}
elseif (isset($_POST['context'])) {
	$name = $_POST['context'];
	$description = $_POST['description'];
	$color = substr($_POST['con_color1'],1);
	$context_id = $_POST['context_id'];
	if ($stmt = $mysqli->prepare("UPDATE Context SET name=?, description=?, color=? WHERE userID=? AND contextID=?")) {    
		$stmt->bind_param('sssii',$name,$description,$color,$user_id,$context_id); 
		$stmt->execute();
	}
}

$mysqli->close();

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>
