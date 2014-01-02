<?php

include 'connect.php'; //connect to database

if($_POST){
	$day = $_POST["day"];
	$time = $_POST["time"];
	$last_table = $_POST["last_table"];

	$table_name;

	if($day == "Sa" || $day == "Su"){   //days with no times handled here
		echo json_encode(array("status" => "clear"));
		exit;
        }

        //times must be in sorted order
        $table = array();
        $table["M"] = array("0800", "0830", "0900", "0915", "0930", 1000, 1015, 1020, 1030, 1045, 1050, 1100, 1115, 1130, 1140, 1145, 1150, 1200, 1215, 1220, 1230, 1240, 1245, 1250);
	$table["Tu"] = array("0800", "0830", "0900", "0915", "0930", 1000, 1015, 1020, 1030, 1045, 1050, 1100, 1115, 1120, 1130, 1145, 1150, 1200, 1215, 1220, 1230, 1245, 1250, 1900, 2200);
	$table["W"] = array("0800", "0830", "0900", "0915", "0930", 1000, 1015, 1020, 1030, 1045, 1050, 1100, 1115, 1130, 1145, 1150, 1200, 1215, 1220, 1230, 1245, 1250, 1825, 1910, 1930, 2200, 2230);
	$table["Th"] = array("0800", "0830", "0900", "0915", "0930", 1000, 1015, 1020, 1030, 1040, 1045, 1050, 1100, 1115, 1120, 1130, 1145, 1150, 1200, 1215, 1220, 1230, 1245, 1250, 1825, 1900, 2200);
	$table["F"] = array("0800", "0900", "0930", 1000, 1015, 1030, 1050, 1100, 1115, 1130, 1145, 1150, 1200, 1205, 1215, 1220, 1230, 1245, 1250);
	
	//pick appropriate table
        for($i = 0; $i < count($table[$day]); $i++){
		if($time < $table[$day][$i]){
		     if($i != 0){
			 $table_name = $day.$table[$day][$i-1];
		     }else{
		         $table_name = $day.$table[$day][count($table[$day]) - 1]; //get last table which should be empty, e.g. if tuesday before classes queried, returns tuesday after classes
		     }
                     break;
		}else if($i == count($table[$day]) - 1){
                     $table_name = $day.$table[$day][$i];
		}
	}

	//if no change since last refresh
	if($last_table == $table_name){
	       echo json_encode(array("status" => "no_change"));
               exit;
	}	

	//query the database
	$query = "SELECT * FROM Shippable." .$table_name;
 	$results = $connection->query($query);

	//build JSON
	$sessions = array();
   	while($row = mysqli_fetch_array($results)){
                $s;
		$s["x"] = $row["x"];
		$s["y"] = $row["y"];
		$s["meeting_place"] = $row["meeting_place"];
		$s["course_code"] = $row["course_code"];
		$s["title"] = $row["title"];
		$s["professor"] = $row["professor"];
		$s["start_time"] = DATE("g:ia", STRTOTIME($row["start_time"]));
		$s["end_time"] = DATE("g:ia", STRTOTIME($row["end_time"]));
		array_push($sessions, $s);
	}

	echo json_encode(array("status" => "refresh", "table_name" => $table_name, "data" => $sessions));
          
}