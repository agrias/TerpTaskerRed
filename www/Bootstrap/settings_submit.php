<?php
require_once("loggedin.php");
require_once("db_connect.php");
include '../Login/functions.php';

$mysqli = new mysqli($host,$user,$pwd,$db);

$user_id =  $_SESSION['user_id'];

$firstname = $_POST['firstname']; 
$lastname = $_POST['lastname'];  
$email = $_POST['email'];
$task_e = ($_POST['task-email']=="NULL")?NULL:$_POST['task-email'];
$task_p = ($_POST['task-popup']=="NULL")?NULL:$_POST['task-popup'];
$event_e = ($_POST['event-email']=="NULL")?NULL:$_POST['event-email'];
$event_p = ($_POST['event-popup']=="NULL")?NULL:$_POST['event-popup'];
$context_e = ($_POST['context-email']=="NULL")?NULL:$_POST['context-email'];
$context_p = ($_POST['context-popup']=="NULL")?NULL:$_POST['context-popup'];

if ($stmt = $mysqli->prepare("UPDATE members SET firstname=?, lastname=?, email=?, task_email_remind=?, task_popup_remind=?, event_email_remind=?, event_popup_remind=?, context_email_remind=?, context_popup_remind=? WHERE id=?")) {    
	$stmt->bind_param('sssiiiiiii',$firstname,$lastname,$email,$task_e,$task_p,$event_e,$event_p,$context_e,$context_p,$user_id); 
	$stmt->execute();
}

if (isset($_POST['password'])) {
	$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
	$password = $_POST['password'];
	$password = hash('sha512', $password.$random_salt);
	if ($stmt = $mysqli->prepare("UPDATE members SET password=?, salt=? WHERE id=?")) {    
		$stmt->bind_param('ssi',$password,$random_salt,$user_id); 
		$stmt->execute();
	}
}

$mysqli->close();

header('Location: settings.php?success=1');

?>
