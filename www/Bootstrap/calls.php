<?php
//code to generate pages
require_once("header_updated.php");
require_once("footer_updated.php");
require_once("category_options_for_data.php");

//add the header info & navbar
generateHeader("Call History");

//connect to the database
$con=mysqli_connect($host,$user,$pwd,$db);

//get the current user id & info associated with that user
$user_id =  $_SESSION['user_id'];
if(!is_numeric($user_id)) {
	echo "Potential SQL injection attempt. Exiting.";
	exit();
}

//add the body of your page below this tag
//note that you don't need any <html> or <body> tags
?>

<h1 style='margin-top:-15px; float:left;'><small>Call History</small></h1>
<br><br>

	<form method="post" action="update_cat_tags.php">	
<input name='call_history' type='hidden' value='yes'>

		<?php
$my_calls = mysqli_query($con,"SELECT * FROM secure_login.Call_History WHERE userID = $user_id");
while ($call = mysqli_fetch_array($my_calls)){
	$categoryID = $call['categoryID'];
	$my_cat = mysqli_query($con,"SELECT * FROM secure_login.Category WHERE userID = $user_id AND categoryID=$categoryID");
	$cat = mysqli_fetch_array($my_cat);
$datetime = strtotime($call['time']);
$date = date("M d, Y", $datetime);
$time = date("g:i A", $datetime);
	echo "<b>{$call['name']}</b> ({$call['phonenum']}) on $date at $time&emsp;Duration: {$call['duration']}<br>
	Category <select name='CALL{$call['callID']}'>";
	getCategoryOptions($categoryID);
	echo "</select><br>";
}
?>

<p style="text-align:center"><button class="flat-btn flat-btn-1" type="submit">Update Category Tags</button></p>
</form>

<?php

generateFooter();

	mysqli_close($con);
?>
