<?php

if(isset($_GET['interval'])){

date_default_timezone_set('America/New_York');

include 'loggedin.php';
include 'db_connect.php';

$con=mysqli_connect($host, $user, $pwd, $db);

if (mysqli_connect_errno($con))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }


include 'repeat_tools.php'; //get functions and cycle[] variable


$interval = mysqli_real_escape_string($con, $_GET['interval']) ;  //in unix time
$userid = $_SESSION['user_id'];


$time = time();
//$time = 1385788585; //testing single event
//$time = 1386343750; //testing repeat

//check for non-repeating event reminders first
$result = mysqli_query($con, "SELECT * FROM Event WHERE userID = ".$userid." AND (startTime - popupReminder) >= ".$time." AND (startTime - popupReminder) < ".($time + $interval));


$event_remind = array();

//add to JSON response
while($row = mysqli_fetch_array($result)){
    $e;
    $e['id'] = $row['id'];
    $e['startTime'] = $row['startTime'];
    $e['title'] = $row['title'];
    $e['popupReminder'] = $row['popupReminder']; 
    array_push($event_remind, $e);
}


//check for repeating events and add to JSON
$result = mysqli_query($con, "SELECT * from Event WHERE userID = ".$userid." AND repeatB = 'true'");

while($row = mysqli_fetch_array($result)){
    $holder = $row['startTime'];
    $holder = getNextDate($holder, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment

    while($holder <= $row['repeatEnd']){
        if(($holder - $row['popupReminder']) >= $time && ($holder - $row['popupReminder']) < ($time + $interval)){  //if in range, add to array
            $e;
            $e['id'] = $row['id'];
            $e['startTime'] = $holder;
            $e['title'] = $row['title'];
            $e['popupReminder'] = $row['popupReminder'];
            array_push($event_remind, $e);

            $holder = getNextDate($holder, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment
        }else{
            break;   //if previous increment is not in range, next one can't be
        }
    }
}


//check for task reminders
$result = mysqli_query($con, "SELECT * FROM mtt_todolist WHERE userID = ".$userid." AND (duedate - popupReminder) >= ".$time." AND (duedate - popupReminder) < ".($time + $interval));

$task_remind = array();

//add to JSON
while($row = mysqli_fetch_array($result)){
    $t;
    $t['id'] = $row['id'];
    $t['duedate'] = $row['duedate'];
    $t['title'] = $row['title'];
    $t['popupReminder'] = $row['popupReminder'];
    array_push($task_remind, $t);

}


//check for time block reminders and add to JSON
$result = mysqli_query($con, "SELECT t.blockID, t.contextID, t.startTime, t.endTime, t.popupReminder, c.name FROM TimeBlock t JOIN (SELECT * FROM Context WHERE userID = ".$userid.") c ON t.contextID = c.contextID WHERE (t.startTime - t.popupReminder) >= ".$time." AND (t.startTime - t.popupReminder) < ".($time + $interval));

$block_remind = array();

//add to JSON
while($row = mysqli_fetch_array($result)){
    $b;
    $b['blockID'] = $row['blockID'];
    $b['startTime'] = $row['startTime'];
    $b['name'] = $row['name'];
    $b['popupReminder'] = $row['popupReminder'];
    array_push($block_remind, $b);
}


//check for repeating time blocks and add to JSON
$result = mysqli_query($con, "SELECT t.blockID, t.contextID, t.startTime, t.endTime, t.popupReminder, t.repeatLength, t.repeatFreq, t.repeatEnd, c.name FROM TimeBlock t JOIN (SELECT * FROM Context WHERE userID = ".$userid.") c ON t.contextID = c.contextID WHERE t.repeatB = 'true'");

while($row = mysqli_fetch_array($result)){
    $holder = $row['startTime'];
    $holder = getNextDate($holder, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment

    while($holder <= $row['repeatEnd']){
        if(($holder - $row['popupReminder']) >= $time && ($holder - $row['popupReminder']) < ($time + $interval)){  //if in range, add to array
            $b;
            $b['blockID'] = $row['blockID'];
            $b['startTime'] = $holder;
            $b['name'] = $row['name'];
            $b['popupReminder'] = $row['popupReminder'];
            array_push($block_remind, $b);

            $holder = getNextDate($holder, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment
        }else{
            break;   //if previous increment is not in range, next one can't be
        }
    }
}


//send JSON
echo json_encode(array("events" => $event_remind, "tasks" => $task_remind, "blocks" => $block_remind));

}else{
     echo "no interval provided";
}
exit;
?>