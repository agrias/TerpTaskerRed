<?php
/**
 * This script converts database entries to a json output
 * this also includes local time offsets
 * PHP Version 5
 *
 * @author   Billy Wu
 */
require_once("/var/www/Bootstrap/loggedin.php");
require_once("/var/www/Bootstrap/db_connect.php");
require_once("/var/www/Bootstrap/reminder_options.php");

global $host, $user, $pwd, $db;
$con=new mysqli($host, $user,$pwd, $db);
if(mysqli_connect_errno()){
	echo "failed to connect";
}
$user_id=intval($_SESSION['user_id']);
$eventList = array();

//$result=mysqli_query($con,"SELECT * FROM secure_login.Event where userID=%d");
//$result=sprintf($result, $user_id);
$query=$con->stmt_init();
$query=$con->prepare("SELECT title,startTime,endTime,id,allDay,repeatB,repeatFreq,repeatLength,repeatEnd FROM secure_login.Event where userID=?");
$query->bind_param("i",$user_id);
$query->execute();
$query->bind_result($title,$start,$end,$id,$a,$repeatB,$repeatFreq,$repeatLength,$repeatEnd);
while ($query->fetch()) {
    $allday=FALSE;
    if(strcmp($a,"true")==0){
 	$allday=TRUE;
    }
    $array = array(
   	"title"=>$title,
    	"start"=>$start,
   	"end"=>$end,
	"id"=>$id,
	"allDay"=>$allday
   );
   array_push($eventList, $array);
   if(strcmp($repeatB,"true")==0){
   	//getRepeats($eventList, $repeatFreq,$repeatLength,$repeatEnd);
   }
}


echo json_encode($eventList);

function getRepeats($eventList, $repeatFreq,$repeatLength,$repeatEnd) {
   $time = new DateTime();
   $time = $time->getTimestamp();
   $temp = strtotime("+10 year", $time);
   echo "Freq " . $repeatFreq . "<br>";
   echo "Length " . $repeatLength . "<br>";
   echo "End " . $repeatEnd . "<br>";
   echo "time " . $time . "<br>";
   echo "temp " . $temp . "<br>";
   if ($repeatEnd === NULL || $repeatEnd < $temp) { 
	$repeatEnd = $temp;
   }
   $repeatInt = 0;
   if ($repeatLength == "day") {
	$repeatInt = 1;
   } else if ($repeatLength == "week") {
	$repeatInt = 2;
   } else if ($repeatLength == "month") {
	$repeatInt = 3;
   } else if ($repeatLength == "year") {
	$repeatInt = 4;
   }

   $nextDate = $row['startTime'];
   $duration = $row['endTime'] - $nextDate;
   //echo "start " . $nextDate . "<br>";
  // echo "duration " . $duration . "<br>";
  // echo "repInt " . $repeatInt . "<br>";
   while ($nextDate < $repeatEnd) {
	$nextDate = getNextDate($nextDate, $repeatInt, $repeatFreq);
	//echo "nextDate " . $nextDate . "<br>";
	$array = array(
	   "title"=>$row['title'],
	   "start"=>$nextDate,
	   "end"=>$nextDate+$duration,
	   "id"=>$row['id'],
	   "allDay"=>FALSE
	);
	array_push($eventList, $array); 
   }
  // echo json_encode($eventList);
}

function getNextDate($nextDate, $repeatInt, $repeatFreq) {
    if ($repeatInt == 1)
        return strtotime("+" . $repeatFreq . " days", $nextDate);
    if ($repeatInt == 2)
        return strtotime("+" . $repeatFreq . " weeks", $nextDate);
    if ($repeatInt == 3)
        return getNextMonth($nextDate, $repeatFreq);
    if ($repeatInt == 4)
        return getNextYear($nextDate, $repeatFreq);
}

function getNextMonth($date, $repeatFreq) {
    $newDate = strtotime("+{$n} months", $date);
    // adjustment for events that repeat on the 29th, 30th and 31st of a month
    if (date('j', $date) !== (date('j', $newDate))) {
        $newDate = strtotime("+" . $n + 1 . " months", $date);
    }
    return $newDate;
}

function getNextYear($date, $repeatFreq) {
    $newDate = strtotime("+{$n} years", $date);
    // adjustment for events that repeat on february 29th
    if (date('j', $date) !== (date('j', $newDate))) {
        $newDate = strtotime("+" . $n + 3 . " years", $date);
    }
    return $newDate;
}

?>

