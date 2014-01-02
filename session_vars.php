<?php
include 'loggedin.php';
$con=mysqli_connect("127.0.0.1","root","Hack5hack%hack","secure_login");

if (mysqli_connect_errno($con))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
$user_id =  $_SESSION['user_id'];
$member = mysqli_query($con,"SELECT id, email, firstname, lastname FROM secure_login.members WHERE id = '$user_id'");
$user = array();
$member = mysqli_fetch_array($member);


$user['id'] = 	$member['id'];
$user['firstname'] = $member['firstname'];
$user['lastname'] = $member['lastname'];

$my_contacts = mysqli_query($con,"SELECT * FROM secure_login.Contacts WHERE userID = '$user_id'");
while ($contacts = mysqli_fetch_array($my_contacts)){
$user['contacts']["" + $contacts['contactID']] = array();
$user['contacts']["" + $contacts['contactID']]['name'] = $contacts['name'];
$user['contacts']["" + $contacts['contactID']]['categoryID'] = $contacts['categoryID'];
$user['contacts']["" + $contacts['contactID']]['phonenum'] = $contacts['phonenum'];
$user['contacts']["" + $contacts['contactID']]['email'] = $contacts['email'];
}

$my_calls = mysqli_query($con,"SELECT * FROM secure_login.Call_History WHERE userID = '$user_id'");
while ($calls = mysqli_fetch_array($my_calls)){
$user['call_history']["" + $calls['callID']] = array();
$user['call_history']["" + $calls['callID']]['time'] = $calls['time'];
$user['call_history']["" + $calls['callID']]['categoryID'] = $calls['categoryID'];
$user['call_history']["" + $calls['callID']]['phonenum'] = $calls['phonenum'];
$user['call_history']["" + $calls['callID']]['duration'] = $calls['duration'];
$user['call_history']["" + $calls['callID']]['name'] = $calls['name'];
}

$my_cat = mysqli_query($con,"SELECT * FROM secure_login.Category WHERE userID = '$user_id'");
while ($cats = mysqli_fetch_array($my_cat)){
$user['categories']["" + $cats['categoryID']] = array();
$user['categories']["" + $cats['categoryID']]['name'] = $cats['name'];
$user['categories']["" + $cats['categoryID']]['color'] = $cats['color'];
}

$my_con = mysqli_query($con,"SELECT * FROM secure_login.Context WHERE userID = '$user_id'");
while ($cons = mysqli_fetch_array($my_con)){
$user['contexts']["" + $cons['contextID']] = array();
$user['contexts']["" + $cons['contextID']]['name'] = $cons['name'];
$user['contexts']["" + $cons['contextID']]['color'] = $cons['color'];
$context_id = $cons['contextID'];

$my_blocks = mysqli_query($con,"SELECT * FROM secure_login.TimeBlock WHERE contextID = $context_id");
while ($time_blocks = mysqli_fetch_array($my_blocks)){
$user['time_blocks']["" + $time_blocks['blockID']] = array();
$user['time_blocks']["" + $time_blocks['blockID']]['contextID'] = $time_blocks['contextID'];
$user['time_blocks']["" + $time_blocks['blockID']]['startTime'] = $time_blocks['startTime'];
$user['time_blocks']["" + $time_blocks['blockID']]['endTime'] = $time_blocks['endTime'];
$user['time_blocks']["" + $time_blocks['blockID']]['popup_reminder'] = $time_blocks['popupReminder'];
$user['time_blocks']["" + $time_blocks['blockID']]['repeatLength'] = $time_blocks['repeatLength'];
$user['time_blocks']["" + $time_blocks['blockID']]['repeatFreq'] = $time_blocks['repeatFreq'];
$user['time_blocks']["" + $time_blocks['blockID']]['repeatEnd'] = $time_blocks['repeatEnd'];
$user['time_blocks']["" + $time_blocks['blockID']]['repeatB'] = $time_blocks['repeatB'];
$user['time_blocks']["" + $time_blocks['blockID']]['allDay'] = $time_blocks['allDay'];
}
}

$my_events = mysqli_query($con,"SELECT * FROM secure_login.Event WHERE userID = '$user_id'");
while ($events = mysqli_fetch_array($my_events)){
$user['events']["" + $events['id']] = array();
$user['events']["" + $events['id']]['start_time'] = $events['startTime'];
$user['events']["" + $events['id']]['end_time'] = $events['endTime'];
$user['events']["" + $events['id']]['title'] = $events['title'];
$user['events']["" + $events['id']]['location'] = $events['location'];
$user['events']["" + $events['id']]['url'] = $events['url'];
$user['events']["" + $events['id']]['categoryID'] = $events['categoryID'];
$user['events']["" + $events['id']]['repeatB'] = $events['repeatB'];
$user['events']["" + $events['id']]['repeatLength'] = $events['repeatLength'];
$user['events']["" + $events['id']]['repeatFreq'] = $events['repeatFreq'];
$user['events']["" + $events['id']]['repeatEnd'] = $events['repeatEnd'];
$user['events']["" + $events['id']]['popup_reminder'] = $events['popupReminder'];
$user['events']["" + $events['id']]['allDay'] = $events['allDay'];
$user['events']["" + $events['id']]['description'] = $events['description'];
}

$my_texts = mysqli_query($con,"SELECT * FROM secure_login.Text_Messages WHERE userID = '$user_id'");
while ($texts = mysqli_fetch_array($my_texts)){
$user['text_messages']["" + $texts['textID']] = array();
$user['text_messages']["" + $texts['textID']]['time'] = $texts['time'];
$user['text_messages']["" + $texts['textID']]['categoryID'] = $texts['categoryID'];
$user['text_messages']["" + $texts['textID']]['content'] = $texts['content'];
$user['text_messages']["" + $texts['textID']]['phonenum'] = $texts['phonenumber'];
$user['text_messages']["" + $texts['textID']]['name'] = $texts['name'];
}

$my_blocks = mysqli_query($con,"SELECT * FROM secure_login.TimeBlock WHERE userID = '$user_id'");
while ($time_blocks = mysqli_fetch_array($my_blocks)){
$user['time_blocks']["" + $time_blocks['blockID']] = array();
$user['time_blocks']["" + $time_blocks['blockID']]['timeblockID'] = $time_blocks['id'];
$user['time_blocks']["" + $time_blocks['blockID']]['contextID'] = $time_blocks['contextID'];
$user['time_blocks']["" + $time_blocks['blockID']]['startTime'] = $time_blocks['startTime'];
$user['time_blocks']["" + $time_blocks['blockID']]['endTime'] = $time_blocks['endTime'];
$user['time_blocks']["" + $time_blocks['blockID']]['popup_reminder'] = $time_blocks['popupReminder'];
$user['time_blocks']["" + $time_blocks['blockID']]['repeatLength'] = $time_blocks['repeatLength'];
$user['time_blocks']["" + $time_blocks['blockID']]['repeatFreq'] = $time_blocks['repeatFreq'];
$user['time_blocks']["" + $time_blocks['blockID']]['repeatEnd'] = $time_blocks['repeatEnd'];
}

$my_tasks = mysqli_query($con,"SELECT * FROM secure_login.mtt_todolist WHERE userID = '$user_id'");
while ($tasks = mysqli_fetch_array($my_tasks)){
$user['tasks']["" + $tasks['id']] = array();
$user['tasks']["" + $tasks['id']]['categoryID'] = $tasks['categoryID'];
$user['tasks']["" + $tasks['id']]['contextID'] = $tasks['contextID'];
$user['tasks']["" + $tasks['id']]['popup_reminder'] = $tasks['popupReminder'];
$user['tasks']["" + $tasks['id']]['estHours'] = $tasks['estHours'];
$user['tasks']["" + $tasks['id']]['estMins'] = $tasks['estMins'];
$user['tasks']["" + $tasks['id']]['duedate'] = $tasks['duedate'];
$user['tasks']["" + $tasks['id']]['completed'] = $tasks['compl'];
$user['tasks']["" + $tasks['id']]['name'] = $tasks['title'];
$user['tasks']["" + $tasks['id']]['description'] = $tasks['note'];
$user['tasks']["" + $tasks['id']]['priority'] = $tasks['prio'];
$user['tasks']["" + $tasks['id']]['tags'] = $tasks['tags'];
}



 echo $user = json_encode($user);
  


mysqli_close($con);
?>

