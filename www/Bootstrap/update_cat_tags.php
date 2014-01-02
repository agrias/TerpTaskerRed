<?php
require_once("loggedin.php");
require_once("db_connect.php");

$mysqli = new mysqli($host,$user,$pwd,$db);
$user_id =  $_SESSION['user_id'];

//print_r(get_defined_vars());

if (isset($_POST['text_messages'])) {
foreach ($_POST as $k => $v) {
	if (substr($k,0,4) == "TEXT") {
		$text_id = substr($k,4);
		$category_id = $v;
		if ($stmt = $mysqli->prepare("UPDATE Text_Messages SET categoryID=? where userID=? AND textID=?")) {    
			$stmt->bind_param('iii',$category_id,$user_id,$text_id); 
			$stmt->execute();
		}
	}
}
} elseif (isset($_POST['contacts'])) {
foreach ($_POST as $k => $v) {
	if (substr($k,0,4) == "CONT") {
		$contact_id = substr($k,4);
		$category_id = $v;
		if ($stmt = $mysqli->prepare("UPDATE Contacts SET categoryID=? where userID=? AND contactID=?")) {    
			$stmt->bind_param('iii',$category_id,$user_id,$contact_id); 
			$stmt->execute();
		}
	}
}
} elseif (isset($_POST['call_history'])) {
foreach ($_POST as $k => $v) {
	if (substr($k,0,4) == "CALL") {
		$call_id = substr($k,4);
		$category_id = $v;
		if ($stmt = $mysqli->prepare("UPDATE Call_History SET categoryID=? where userID=? AND callID=?")) {    
			$stmt->bind_param('iii',$category_id,$user_id,$call_id); 
			$stmt->execute();
		}
	}
}
}

$mysqli->close();

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>