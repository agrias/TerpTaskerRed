<?php
//code to generate pages
require_once("header_updated.php");
require_once("footer_updated.php");

//connect to the database
$con=mysqli_connect($host,$user,$pwd,$db);

//get the current user id & info associated with that user
$user_id =  $_SESSION['user_id'];
if(!is_numeric($user_id)) {
	echo "Potential SQL injection attempt. Exiting.";
	exit();
}

generateHeader('Tasks');?>

	

<?php
require('/var/www/mytinytodo/'. 'index.php');
?>

<script type="text/javascript">

	var mainList = -1;
	var contextView = -1;
   </script>

<?php



generateFooter();

	mysqli_close($con);
?>
