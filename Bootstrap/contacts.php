<?php
//code to generate pages
require_once("header_updated.php");
require_once("footer_updated.php");
require_once("category_options_for_data.php");

//add the header info & navbar
generateHeader("Contacts");

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

<h1 style='margin-top:-15px; float:left;'><small>Contacts</small></h1>
<br><br>

	<form method="post" action="update_cat_tags.php">	
<input name='contacts' type='hidden' value='yes'>

		<?php
$my_contacts = mysqli_query($con,"SELECT * FROM secure_login.Contacts WHERE userID = $user_id");
while ($contact = mysqli_fetch_array($my_contacts)){
	$categoryID = $contact['categoryID'];
	$my_cat = mysqli_query($con,"SELECT * FROM secure_login.Category WHERE userID = $user_id AND categoryID=$categoryID");
	$cat = mysqli_fetch_array($my_cat);
	echo "<b>{$contact['name']}</b>&emsp;Phone: {$contact['phonenum']}&emsp;Email: {$contact['email']}<br>
	Category <select name='CONT{$contact['contactID']}'>";
	getCategoryOptions($categoryID);
	echo "</select><br>";
}
?>

<p style="text-align:center"><button class="flat-btn flat-btn-1" type="submit">Update Category Tags</button></p>
</form>

<form method="post" enctype="multipart/form-data">
  <label size=50 for="file">Import from vCard file:</label>
  <input size=40 type="file" name="file" id="file" /><br/>
  <input type="hidden" name="del_format" value="phpaddr">
  <input type="submit" name="submit" value="Submit" />
</form>

<?php

generateFooter();

	mysqli_close($con);
?>