<?php

//This script periodically checks for reminders and schedules email for sending.

// echo 'echo "this is a second message from the command line" | mail -s "terminal mail two" elfalem@gmail.com' | at now + 1 minute

$interval = 600;  //in seconds, must be equivalent to how often cron job is scheduled


include 'db_connect.php';

$con=mysqli_connect($host, $user, $pwd, $db);

if (mysqli_connect_errno($con))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

//$time = time();
$time = 1386345580; //testing

$footer = "\n - Terp Tasker\n\nYou are receiving this email because you have set up email reminders for events, tasks, and/or time blocks. You can change your settings by logging in to Terp Tasker.";


//check for events reminders first
$result = mysqli_query($con, "SELECT M.id, M.firstname, E.userID, E.title, E.startTime, M.id, M.email FROM (SELECT * FROM Event WHERE (startTime - emailReminder) >= ".$time." AND (startTime - emailReminder) < ".($time + $interval).") E JOIN members M ON E.userID = M.id");

//schedule an email
while($row = mysqli_fetch_array($result)){
   $message = "Hi ".$row['firstname'].",\n This is a reminder that your event ".$row['title']." will start at ".$row['startTime'].".".$footer;
   $subject = "Event Reminder: ".$row['title']; 
   $send_time = floor((($row['startTime'] - $row['emailReminder']) - $time)/60); //in minutes, rounded down
   $address = $row['email'];
   //$command = "echo 'echo \"" .$message. "\" | mail -s \"" .$subject. "\" " .$address. "' | at now + " .$send_time. " minutes";

   $command =  "echo 'echo \"" .$message. "\" | mail -s \"" .$subject. "\" doctorwho.terptasker@gmail.com' | at now + 2 minutes"; //TESTING, REMOVE
   echo $command;
   //exec($command);
}



exit;
/**



//check for task reminders and add to JSON
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


//send JSON
echo json_encode(array("events" => $event_remind, "tasks" => $task_remind, "blocks" => $block_remind));


**/

?>