<?php
include 'loggedin.php';
include 'db_connect.php';

$con=mysqli_connect($host, $user, $pwd, $db);

if (mysqli_connect_errno($con))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }

include 'repeat_tools.php';

$userid = $_SESSION['user_id'];

$time = time();
//$time =  1385641820;  //for testing, should take you to categoryID 1 when logged in as Bruce unless database is changed
//$time =  1386082801;  //for testing, doctor who

//check for events first
$result =  mysqli_query($con, "SELECT categoryID FROM Event WHERE userID = ".$userid." AND startTime <= ".$time." AND endTime > ".$time);
$result =  mysqli_fetch_array($result);  //note: if there are multiple events at the same time, it redirects to whichever is returned as the first row


if($result){
   header("Location: category.php?c=".$result['categoryID']);
   exit;
}else{

   //check for repeating events
   $result = mysqli_query($con, "SELECT * from Event WHERE userID = ".$userid." AND repeatB = 'true'");

   while($row = mysqli_fetch_array($result)){
         $repStart = $row['startTime'];
         $repEnd = $row['endTime'];
         $repStart = getNextDate($repStart, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment
         $repEnd = getNextDate($repEnd, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment

          while($repStart <= $row['repeatEnd']){
             if($repStart <= $time && $repEnd > $time){ 
                header("Location: category.php?c=".$row['categoryID']);
                exit;
             }
             $repStart = getNextDate($repStart, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment
             $repEnd = getNextDate($repEnd, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment
          }
   }



    //check for time blocks
   $result = mysqli_query($con, "SELECT contextID FROM TimeBlock WHERE contextID IN (SELECT contextID FROM Context WHERE userID = ".$userid.") AND  startTime <= ".$time." AND endTime > ".$time);
   $result =  mysqli_fetch_array($result);  //note: if there are multiple timeblocks at the same time, it redirects to whichever is returned as the first row

   if($result){  //check result of timeblock query here
      header("Location: context.php?c=".$result['contextID']);
      exit;
   }else{

      //check for repeating time blocks here
      $result = mysqli_query($con, "SELECT * FROM TimeBlock WHERE contextID IN (SELECT contextID FROM Context WHERE userID = ".$userid.") AND repeatB = 'true'");

      while($row = mysqli_fetch_array($result)){
         $repStart = $row['startTime'];
         $repEnd = $row['endTime'];
         $repStart = getNextDate($repStart, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment
         $repEnd = getNextDate($repEnd, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment

          while($repStart <= $row['repeatEnd']){
             if($repStart <= $time && $repEnd > $time){
                header("Location: context.php?c=".$row['contextID']);
                exit;
             }
             $repStart = getNextDate($repStart, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment
             $repEnd = getNextDate($repEnd, $cycle[$row['repeatLength']], $row['repeatFreq']); //increment
          }
      }

      //default to calendar
      header("Location: calendar.php");
      exit;
   }

}


?>