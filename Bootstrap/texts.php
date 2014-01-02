<?php
//code to generate pages
require_once("header_updated.php");
require_once("footer_updated.php");
require_once("category_options_for_data.php");

//add the header info & navbar
generateHeader("Text Messages");

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

<h1 style='margin-top:-15px; float:left;'><small>Text Messages</small></h1>
<br><br>

	<form method="post" action="update_cat_tags.php">	
<input name='text_messages' type='hidden' value='yes'>

		<?php
$my_texts = mysqli_query($con,"SELECT * FROM secure_login.Text_Messages WHERE userID = $user_id");
while ($text = mysqli_fetch_array($my_texts)){
	$categoryID = $text['categoryID'];
	$my_cat = mysqli_query($con,"SELECT * FROM secure_login.Category WHERE userID = $user_id AND categoryID=$categoryID");
	$cat = mysqli_fetch_array($my_cat);
	echo "<b>{$text['name']}</b> ({$text['phonenumber']}) at {$text['time']}<br>
	{$text['text_content']}<br>
	Category <select name='TEXT{$text['textID']}'>";
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
