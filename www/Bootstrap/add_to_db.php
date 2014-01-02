<?php
require_once("loggedin.php");
require_once("db_connect.php");
 
$mysqli = new mysqli($host,$user,$pwd,$db);
$user_id =  $_SESSION['user_id'];
$description = $_POST['description'];

//print_r(get_defined_vars());

if (isset($_POST['category'])) {
	$name = $_POST['category'];
	$color = substr($_POST['cat_color'],1);
	if ($stmt = $mysqli->prepare("INSERT INTO Category (userID, name, description, color) VALUES (?, ?, ?, ?)")) {    
		$stmt->bind_param('isss',$user_id,$name,$description,$color); 
		$stmt->execute();
	}
	$new_id = $mysqli->insert_id;

header("Location: " . "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/category.php?c=".$new_id);
}
elseif (isset($_POST['context'])) {
	$name = $_POST['context'];
	$color = substr($_POST['con_color'],1);
	if ($stmt = $mysqli->prepare("INSERT INTO Context (userID, name, description, color) VALUES (?, ?, ?, ?)")) {    
		$stmt->bind_param('isss',$user_id,$name,$description,$color); 
		$stmt->execute();
	}
	$new_id = $mysqli->insert_id;

header("Location: " . "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/context.php?c=".$new_id);
}

$mysqli->close();

?>
