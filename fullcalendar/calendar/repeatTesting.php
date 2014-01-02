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

global $host, $user, $pwd, $db, $eventList;
$con=new mysqli($host, $user,$pwd, $db);
$conColor=new mysqli($host, $user,$pwd, $db);
$conTitle=new mysqli($host, $user,$pwd, $db);
$conContext=new mysqli($host, $user,$pwd, $db);
if(mysqli_connect_errno()){
	echo "failed to connect";
}
$user_id=intval($_SESSION['user_id']);
$eventList = array();
$category=$_POST['category'];
$context=$_POST['context'];
/***********************************GETTING EVENTS************************/
if(strcmp($category,"")!=0 || (strcmp($category,"")==0 && strcmp($context,"")==0)){
	$query=$con->stmt_init();
	if(strcmp($category,"")==0){
		$query=$con->prepare("SELECT title,startTime,endTime,id,location,url,
		     allDay,categoryID,repeatB,repeatFreq,repeatLength,
		     repeatEnd,emailReminder,popupReminder,description FROM secure_login.Event where userID=?");
		$query->bind_param("i",$user_id);
        }
	if(strcmp($category,"")!=0){
		$query=$con->prepare("SELECT title,startTime,endTime,id,location,url,
		     allDay,categoryID,repeatB,repeatFreq,repeatLength,
		     repeatEnd,emailReminder,popupReminder,description FROM secure_login.Event where userID=? AND categoryID=?");
		$query->bind_param("ii",$user_id, intval($category));
        }
	$query->execute();
	$query->bind_result($title,$start,$end,$id,$loc,$url,$a,$catID,$repeatB,
		    $repeatFreq,$repeatLength,$repeatEnd,$eRem,$pRem,$desc);
	while ($query->fetch()) {
    	$allday=FALSE;
    	if(strcmp($a,"true")==0){
 		$allday=TRUE;
    	}
	$editable=TRUE;
        if($allday==TRUE){
            $editable=FALSE;
        }
    	if(strcmp($repeatB,"true")==0) {
        	$color = "#".getColor($conColor, $user_id,"category", $catID);
		$array = array(
	   	"title"=>$title,
	   	"start"=>$start,
	   	"end"=>$end,
	   	"id"=>$id,
	   	"loc"=>$loc, 
	   	"url"=>$url,
	   	"type"=>"event",
	   	"allDay"=>$allday,
		"editable"=>$editable,
	   	"category"=>$catID,
	   	"color"=>$color,
	   	"repeat"=>"true",
	   	"repeatFreq"=>$repeatFreq,
	   	"repeatLength"=>$repeatLength,
	   	"repeatEnd"=>$repeatEnd,
	   	"emailReminder"=>$eRem,
	   	"popupReminder"=>$pRem,
	   	"description"=>$desc
		);
	array_push($eventList, $array); 

   	$eventList = getEventRepeats($conColor,$eventList,$user_id,$title,$start,$end,
				     $id,$loc,$url,$allday,$catID,$repeatFreq,$repeatLength,$repeatEnd,$eRem,$pRem,$desc);
   	 } else {
      	$color = "#".getColor($conColor, $user_id,"category", $catID);
      	$array = array(
  		   "title"=>$title,
     	 	  "start"=>$start,
   		   "end"=>$end,
		   "id"=>$id,
		   "loc"=>$loc,
		   "url"=>$url,
		   "type"=>"event",
		   "allDay"=>$allday,
		   "editable"=>$editable,
		   "color"=>$color,
		   "category"=>$catID,
		   "repeat"=>"false",
		   "emailReminder"=>$eRem,
		   "popupReminder"=>$pRem,
		   "description"=>$desc
      		);
      	array_push($eventList, $array);
   	}
	}
}

/***********************************GETTING Contexts************************/
if(strcmp($context,"")!=0 || (strcmp($category,"")==0 && strcmp($context,"")==0)){
	$queryCon=$conContext->stmt_init();
	if(strcmp($context,"")==0){
		$queryCon=$conContext->prepare("SELECT startTime,endTime,blockID,TimeBlock.contextID,allDay,repeatB,repeatFreq,repeatLength,
		     repeatEnd,emailReminder,popupReminder FROM secure_login.TimeBlock INNER JOIN secure_login.Context On TimeBlock.contextID = Context.contextID WHERE userID=? ");
		$queryCon->bind_param("i",$user_id);
	}
	if(strcmp($context,"")!=0){
		$queryCon=$conContext->prepare("SELECT startTime,endTime,blockID,contextID,allDay,repeatB,repeatFreq,repeatLength,
		     repeatEnd,emailReminder,popupReminder FROM secure_login.TimeBlock WHERE contextID=?");
		$queryCon->bind_param("i",intval($context));
	}

	$queryCon->execute();
	$queryCon->bind_result($start,$end,$id,$conID,$a,$repeatB,$repeatFreq,$repeatLength,$repeatEnd,$eRem,$pRem);
	while ($queryCon->fetch()) {
    		$allday=FALSE;

    	if(strcmp($a,"true")==0){
 		$allday=TRUE;
 	   }
	$editable=true;
        if($allday){
            $editable=false;
        }

    	if(strcmp($repeatB,"true")==0) {
        	$color = "#".getColor($conColor, $user_id,"context", $conID);
		$title = "Context: ".getTitle($conTitle, $user_id, $conID);
		$array = array(
		   "title"=>$title,
		   "start"=>$start,
		   "end"=>$end,
		   "id"=>$id,
	 	   "type"=>"context",
	 	   "allDay"=>$allday,
	           "editable"=>$editable,
	 	   "context"=>$conID,
		   "color"=>$color,
		   "repeat"=>"true",
		   "repeatFreq"=>$repeatFreq,
	  	   "repeatLength"=>$repeatLength,
		   "repeatEnd"=>$repeatEnd,
	 	   "emailReminder"=>$eRem,
	   	   "popupReminder"=>$pRem,
		);
	array_push($eventList, $array); 
	$eventList = getContextRepeats($conTitle,$conColor,$eventList,$user_id,$start,$end,
				     $id,$allday,$conID,$repeatFreq,$repeatLength,$repeatEnd,$eRem,$pRem);
   	 } else {
      		$color = "#".getColor($conColor, $user_id,"context", $conID);
      		$title = "Context: ".getTitle($conTitle, $user_id, $conID);
      		$array = array(
  	 	"title"=>$title,
     		"start"=>$start,
   		"end"=>$end,
	 	"id"=>$id,
		"type"=>"context",
	   	"allDay"=>$allday,
		 "editable"=>$editable,
	   	"color"=>$color,
	   	"context"=>$conID,
	   	"repeat"=>"false",
	   	"emailReminder"=>$eRem,
	   	"popupReminder"=>$pRem,
      	);
      array_push($eventList, $array);
   	}
     }
}

echo json_encode($eventList);
mysqli_close($con);
mysqli_close($conColor);
mysqli_close($conTitle);
mysqli_close($conContext);



/*******************************FUNCTIONS*******************************/

function getEventRepeats($conColor,$eventList,$user_id,$title,$start,$end,$id,
			$loc,$url,$allday,$catID,$repeatFreq,$repeatLength,$repeatEnd,$eRem,$pRem,$desc) {
   $time = time();
   //$time = $time->getTimestamp();
   $temp = strtotime("+10 year", $time);
   //echo "Freq " . $repeatFreq . "<br>";
   //echo "Length " . $repeatLength . "<br>";
   //echo "End " . $repeatEnd . "<br>";
   //echo "time " . $time . "<br>";
   //echo "temp " . $temp . "<br>";
   if ($repeatEnd === NULL || $repeatEnd > $temp) { 
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

   $nextDate = $start;
   $duration = $end - $nextDate;
   //echo "start " . $nextDate . "<br>";
  // echo "duration " . $duration . "<br>";
  // echo "repInt " . $repeatInt . "<br>";
   while ($nextDate < $repeatEnd) {
	$nextDate = getNextDate($nextDate, $repeatInt, $repeatFreq);
	if ($nextDate > $repeatEnd) {
	   // repeat end date was larger then last repeat
	   // but smaller than current one.
	   break;
	}
	$editable=TRUE;
        if($allday==TRUE){
            $editable=FALSE;
        }

	//echo "nextDate " . $nextDate . "<br>";
	$color = "#".getColor($conColor, $user_id,"category", $catID);
	//echo "$color<br>";
	$array = array(
	   "title"=>$title,
	   "start"=>$nextDate,
	   "end"=>$nextDate+$duration,
	   "id"=>$id,
	   "loc"=>$loc,
	   "url"=>$url,
	   "type"=>"event",
	   "allDay"=>$allday, 
	   "editable"=>$editable,
	   "category"=>$catID,
	   "color"=>$color,
	   "repeat"=>"true",
	   "repeatFreq"=>$repeatFreq,
	   "repeatLength"=>$repeatLength,
	   "repeatEnd"=>$repeatEnd,
	   "emailReminder"=>$eRem,
	   "popupReminder"=>$pRem,
	   "description"=>$desc
	);
	array_push($eventList, $array); 
   }
   return $eventList;
}


function getContextRepeats($conTitle,$conColor,$eventList,$user_id,$start,$end,
				     $id,$allday,$conID,$repeatFreq,$repeatLength,$repeatEnd,$eRem,$pRem) {
   $time = time();

   $temp = strtotime("+10 year", $time);
  
   if ($repeatEnd === NULL || $repeatEnd > $temp) { 
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

   $nextDate = $start;
   $duration = $end - $nextDate;

   while ($nextDate < $repeatEnd) {
	$nextDate = getNextDate($nextDate, $repeatInt, $repeatFreq);
	if ($nextDate > $repeatEnd) {
	   // repeat end date was larger then last repeat
	   // but smaller than current one.
	   break;
	}
	$editable=TRUE;
        if($allday==TRUE){
            $editable=FALSE;
        }

	$color = "#".getColor($conColor, $user_id,"context", $conID);
        $title = "Context: ".getTitle($conTitle, $user_id, $conID);
	//echo "$color<br>";
	$array = array(
	   "title"=>$title,
	   "start"=>$nextDate,
	   "end"=>$nextDate+$duration,
	   "id"=>$id,
	   "type"=>"context",
	   "allDay"=>$allday,
           "editable"=>$editable,
	   "context"=>$conID,
	   "color"=>$color,
	   "repeat"=>"true",
	   "repeatFreq"=>$repeatFreq,
	   "repeatLength"=>$repeatLength,
	   "repeatEnd"=>$repeatEnd,
	   "emailReminder"=>$eRem,
	   "popupReminder"=>$pRem,
	);
	array_push($eventList, $array); 
   }
   return $eventList;
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


function getColor($con, $uid, $type, $id) {
	$query=$con->stmt_init();
	if ($type == "context") {
	    $query=$con->prepare("SELECT color FROM secure_login.Context where userID=? AND contextID=?");
	} else {
	    $query=$con->prepare("SELECT color FROM secure_login.Category where userID=? AND CategoryID=?");
	}
	$query->bind_param("ii",$uid, $id);
	$query->execute();
	$query->bind_result($color);
	$query->fetch();
	return $color;
}

function getTitle($con, $user_id, $conID) {
	$query=$con->stmt_init();
	$query=$con->prepare("SELECT name FROM secure_login.Context where userID=? AND contextID=?");
	$query->bind_param("ii",$user_id, $conID);
	$query->execute();
	$query->bind_result($name);
	$query->fetch();
	return $name;
}
?>

