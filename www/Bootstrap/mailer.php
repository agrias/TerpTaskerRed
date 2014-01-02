<?php

//This script periodically checks for reminders and schedules email for sending.

// echo 'echo "this is a second message from the command line" | mail -s "terminal mail two" elfalem@gmail.com' | at now + 1 minute

date_default_timezone_set('America/New_York');

$interval = 600;  //in seconds, must be equivalent to how often cron job is scheduled


include 'db_connect.php';
include 'repeat_tools.php';

$con=mysqli_connect($host, $user, $pwd, $db);

if (mysqli_connect_errno($con))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

$time = time();
//$time = 1386345580; //testing event
//$time = 1388159700; //testing repeating event
//$time = 1386251100; //testing task
//$time = 1386248200; //testing time block
//$time = 1386686800; //testing repeating time block

$footer = "\n - Terp Tasker\n\nYou are receiving this email because you have set up email reminders for events, tasks, and/or time blocks. You can change your settings by logging in to Terp Tasker.";


//check for events reminders first
$result = mysqli_query($con, "SELECT M.id, M.firstname, E.userID, E.title, E.startTime, E.emailReminder, M.email FROM (SELECT * FROM Event WHERE (startTime - emailReminder) >= ".$time." AND (startTime - emailReminder) < ".($time + $interval).") E JOIN members M ON E.userID = M.id");

//schedule an email
while($row = mysqli_fetch_array($result)){
   $message = "Hi ".$row['firstname'].",\n This is a reminder that your event ".$row['title']." will start at ". date('H:i:s \o\n D F j, Y' , $row['startTime'])  .".".$footer;
   $subject = "Event Reminder: ".$row['title']; 
   $send_time = floor((($row['startTime'] - $row['emailReminder']) - $time)/60); //in minutes, rounded down
   $address = $row['email'];
   $command = "echo 'echo \"" .$message. "\" | mail -s \"" .$subject. "\" " .$address. "' | at now + " .$send_time. " minutes";

   //$command =  "echo 'echo \"" .$message. "\" | mail -s \"" .$subject. "\" doctorwho.terptasker@gmail.com' | at now + 2 minutes"; //TESTING, REMOVE
   //echo $command;
   exec($command);
}


//check for repeating events
$result = mysqli_query($con, "SELECT M.id, M.firstname, E.userID, E.title, E.startTime, E.repeatLength, E.repeatFreq, E.emailReminder, E.repeatEnd, M.email FROM (SELECT * from Event WHERE repeatB = 'true') E JOIN members M ON E.userID = M.id");

while($row = mysqli_fetch_array($result)){
    $holder = $row['startTime'];
    $holder = getNextDate($holder, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment

    while($holder <= $row['repeatEnd']){
        if(($holder - $row['emailReminder']) >= $time && ($holder - $row['emailReminder']) < ($time + $interval)){  //if in range, add to array
             $message = "Hi ".$row['firstname'].",\n This is a reminder that your event ".$row['title']." will start at ". date('H:i:s \o\n D F j, Y' , $holder)  .".".$footer;
             $subject = "Event Reminder: ".$row['title'];
             $send_time = floor((($holder - $row['emailReminder']) - $time)/60); //in minutes, rounded down
             $address = $row['email'];
             $command = "echo 'echo \"" .$message. "\" | mail -s \"" .$subject. "\" " .$address. "' | at now + " .$send_time. " minutes";

             //$command =  "echo 'echo \"" .$message. "\" | mail -s \"" .$subject. "\" doctorwho.terptasker@gmail.com' | at now + 2 minutes"; //TESTING, REMOVE
             //echo $command;
             exec($command);

             $holder = getNextDate($holder, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment
        }else{
            break;   //if previous increment is not in range, next one can't be
        }
    }
}

//check for task reminders
$result = mysqli_query($con, "SELECT M.id, M.firstname, T.userID, T.title, T.duedate, T.emailReminder, M.email FROM (SELECT * FROM mtt_todolist WHERE (duedate - emailReminder) >= ".$time." AND (duedate - emailReminder) < ".($time + $interval).") T JOIN members M ON T.userID = M.id");

while($row = mysqli_fetch_array($result)){
   $message = "Hi ".$row['firstname'].",\n This is a reminder that your task ".$row['title']." is due at ". date('H:i:s \o\n D F j, Y' , $row['duedate'])  .".".
$footer;
   $subject = "Task Reminder: ".$row['title'];
   $send_time = floor((($row['duedate'] - $row['emailReminder']) - $time)/60); //in minutes, rounded down
   $address = $row['email'];
   $command = "echo 'echo \"" .$message. "\" | mail -s \"" .$subject. "\" " .$address. "' | at now + " .$send_time. " minutes";

   //$command =  "echo 'echo \"" .$message. "\" | mail -s \"" .$subject. "\" doctorwho.terptasker@gmail.com' | at now + 2 minutes"; //TESTING, REMOVE
   //echo $command;
   exec($command);
}


//check for time block reminders
$result = mysqli_query($con, "SELECT M.id, M.firstname, E.userID, E.name, E.startTime, E.emailReminder, M.email FROM (SELECT t.blockID, t.contextID, t.startTime, t.emailReminder, c.name, c.userID FROM TimeBlock t JOIN Context c ON t.contextID = c.contextID WHERE (t.startTime - t.emailReminder) >= ".$time." AND (t.startTime - t.emailReminder) < ".($time + $interval).") E JOIN members M ON E.userID = M.id");

while($row = mysqli_fetch_array($result)){
   $message = "Hi ".$row['firstname'].",\n This is a reminder that your time block ".$row['name']." will start at ". date('H:i:s \o\n D F j, Y' , $row['startTime'])  .".".$footer;
   $subject = "Time block Reminder: ".$row['name'];
   $send_time = floor((($row['startTime'] - $row['emailReminder']) - $time)/60); //in minutes, rounded down
   $address = $row['email'];
   $command = "echo 'echo \"" .$message. "\" | mail -s \"" .$subject. "\" " .$address. "' | at now + " .$send_time. " minutes";

   //$command =  "echo 'echo \"" .$message. "\" | mail -s \"" .$subject. "\" doctorwho.terptasker@gmail.com' | at now + 2 minutes"; //TESTING, REMOVE
   //echo $command;
   exec($command);
}



//check for repeating time blocks
$result = mysqli_query($con, "SELECT M.id, M.firstname, E.userID, E.name, E.startTime, E.repeatLength, E.repeatFreq, E.emailReminder, E.repeatEnd, M.email FROM (SELECT t.blockID, t.contextID, t.startTime, t.emailReminder, t.repeatLength, t.repeatFreq, t.repeatEnd, c.name, c.userID FROM TimeBlock t JOIN Context c ON t.contextID = c.contextID WHERE t.repeatB = 'true') E JOIN members M ON E.userID = M.id");


while($row = mysqli_fetch_array($result)){
    $holder = $row['startTime'];
    $holder = getNextDate($holder, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment

    while($holder <= $row['repeatEnd']){
        if(($holder - $row['emailReminder']) >= $time && ($holder - $row['emailReminder']) < ($time + $interval)){  //if in range, add to array
             $message = "Hi ".$row['firstname'].",\n This is a reminder that your time block ".$row['name']." will start at ". date('H:i:s \o\n D F j, Y' , $holder)  .".".$footer;
             $subject = "Time block Reminder: ".$row['name'];
             $send_time = floor((($holder - $row['emailReminder']) - $time)/60); //in minutes, rounded down
             $address = $row['email'];
             $command = "echo 'echo \"" .$message. "\" | mail -s \"" .$subject. "\" " .$address. "' | at now + " .$send_time. " minutes";

             //$command =  "echo 'echo \"" .$message. "\" | mail -s \"" .$subject. "\" doctorwho.terptasker@gmail.com' | at now + 2 minutes"; //TESTING, REMOVE
             //echo $command;
             exec($command);

             $holder = getNextDate($holder, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment
        }else{
            break;   //if previous increment is not in range, next one can't be
        }
    }
}

?>