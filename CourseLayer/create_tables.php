<?php
//THIS SCRIPT SHOULD ONLY BE RUN ONCE TO CREATE THE TIME-SPECIFIC TABLES, NOT PART OF PRODUCT


exit; //comment out to enable

include 'connect.php';  //conect to database


$semester = "201308";


foreach(array("F") as $weekday){   //use M Tu W Th F

   $query = "SELECT DISTINCT time FROM (SELECT start_time AS time FROM terpnav.massive_table WHERE meeting_hashcode LIKE BINARY '%".$weekday."%' AND semester_code=".$semester." UNION SELECT end_time AS time FROM terpnav.massive_table WHERE meeting_hashcode LIKE BINARY '%".$weekday."%' AND semester_code=".$semester.") AS K ORDER BY time";

   $results = $connection->query($query);

   while($row = mysqli_fetch_array($results)){
       if(is_null($row["time"]) || $row["time"] == "00:00:00"){
          //ignore
       } else {
          $table_name = $weekday.str_replace(":", "", substr($row["time"], 0, 5));
	  //$table_name = str_replace(":", "", substr($row["time"], 0, 5));  //-----TEMP

          //CRITICAL PART
	  //$create_query = "CREATE TABLE Shippable." .$table_name. " SELECT * FROM terpnav.massive_table WHERE semester_code=".$semester." AND meeting_hashcode LIKE BINARY '%" .$weekday. "%' AND start_time <= '".$row["time"]."' AND end_time > '".$row["time"]."'";

          //$r = $connection->query($create_query);
          //END CRITICAL PART

          echo $row["time"]." created ".$table_name."<br/>";
	  //echo ", ".$table_name;  //-----TEMP
       }
   }

}

echo "end of file<br/>";

?>