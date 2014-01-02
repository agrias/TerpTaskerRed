<?php
//code to generate pages
require_once("header_updated.php");
require_once("footer_updated.php");

//connect to the database
$con=mysqli_connect($host,$user,$pwd,$db);

//get the current user id & info associated with that user
$user_id =  $_SESSION['user_id'];
$category = $_GET['c'];
if(!is_numeric($category) || !is_numeric($user_id)) {
	echo "Potential SQL injection attempt. Exiting.";
	exit();
}
$cat = mysqli_query($con,"SELECT * FROM secure_login.Category WHERE userID = $user_id AND categoryID = $category;");
$cat = mysqli_fetch_array($cat);
$name = $cat['name'];
$color = $cat['color'];

generateHeader($name);

echo "<h1 style='margin-top:-15px; float:left;'><small style='color:#$color'>$name</small></h1>";

if ($name!="None") {
	echo "<h5 style='margin-top:-10px; margin-right:0px; float:right;'><a data-toggle='modal' href='#edit'>Edit</a>&emsp;<a data-toggle='modal' href='#delete'>Delete</a></h5>";
}
?>
		
	<div id="edit" class="modal hide fade in" style="display: none; width:400px;">
	<div class="modal-header"><a class="close" data-dismiss="modal">x</a>
	<h4>Edit category</h4>
	<form method="post" action="update_db.php">
		<?php
	echo "<h5>Name:</h5><input name='category' type='text' style='margin-bottom:10px' value='{$cat['name']}' autofocus><br>
	<h5>Description:</h5><textarea name='description' style='margin-bottom:10px'>{$cat['description']}</textarea><br>
	<h5>Color:</h5><input name='cat_color1' id='cat_color1' type='text' value='#{$cat['color']}'> 
	<input name='category_id' type='hidden' value=$category>"
?>
	<button class="flat-btn flat-btn-2" type="submit" style="float:right; margin-top:-20px; margin-bottom:20px;">OK</button>
	</form>	
	</div></div>
	
	<div id="delete" class="modal hide fade in" style="display: none; width:400px;">
	<div class="modal-header"><a class="close" data-dismiss="modal">x</a>
	<h4>Delete category</h4>
	<form method="post" action="delete_from_db.php">
		<?php
	echo "Are you sure you want to delete the category {$cat['name']}?<br><br>
	<input type='checkbox' name='events' value='yes'> Also delete all {$cat['name']} events<br><br>
	<input type='checkbox' name='tasks' value='yes'> Also delete all {$cat['name']} tasks
	<input name='category_id' type='hidden' value=$category>"
?>
	<button class="flat-btn flat-btn-2" type="submit" style="float:right; margin-bottom:20px;">OK</button>
	</form>	
	</div></div>	
	
<br><br>
<div id='myCalendar'></div>

    <div id="calEventDialog" style="display:none;"">
	<form id="addForm">
		<input type="hidden" />
		Type&nbsp;
		<input type="radio" name="eventType" value="event" id="typeEvent" checked="checked" required/>Event<br>
		Title&nbsp;<input type="text" name="eventTitle" id="eventTitle" required/>*<br>
		Location&nbsp;<input type="text" name="eventLocation" id="eventLocation"/><br>
		URL&nbsp;<input type="url" name="eventURL" id="eventURL"/><br>
		Category&nbsp;
		<select name="eventCategory" id="eventCategory"/>
		 	<?php echo "<option selected value='$category'>$name</option>";?>
		</select><br>		
		Set Email Reminder&nbsp;
		<select name="eventEmailReminder" id="eventEmailReminder">
			<?php getReminderOptions($event_e);?>
		</select><br>
		Set Popup Reminder&nbsp;
		<select name="eventPopupReminder" id="eventPopupReminder">
			<?php getReminderOptions($event_p);?>
		</select><br>
		Description&nbsp;<textarea type="textarea" name="eventDesc" id="eventDesc"/></textarea><br>
		Repeat&nbsp;
		<input type="radio" name="eventRepeat" value="true" id="yesRepeat"/>Yes
		<input type="radio" name="eventRepeat" value="false" id="noRepeat" checked="checked"/>No<br>
		<div id="repeatFields">
			Repeat Every&nbsp;
			<input type="number" name="eventRepeatFreq" id="eventRepeatFreq" size="4"/>
			<select name="eventRepeatLength" id="eventRepeatLength">
			<option value="day">Days</option>
			<option value="week">Weeks</option>
			<option value="month">Months</option>
			<option value="year">Years</option>
			</select><br>
			End Repeat In&nbsp;<input type="text" name="eventRepeatEnd" id="eventRepeatEnd"/><br>
		</div>
		* Fields are required, no blocks will be added if not completed.
	</form>
    </div>
  <br>
<?php
require('/var/www/mytinytodo/'. 'index.php');

?>

<script type="text/javascript">
	var divOne = document.getElementById('tabs_buttons');
	divOne.style.display='none';
	var divLists = document.getElementById('hide_this');
	divLists.style.display='none';
	var mainList = <?php echo json_encode($category);?>;
	var contextView = -1;
   </script>


		<?php
echo "<h5 style='color:#$color'>Call History</h5>";
$my_calls = mysqli_query($con,"SELECT * FROM secure_login.Call_History WHERE userID = $user_id AND categoryID=$category");
while ($call = mysqli_fetch_array($my_calls)){
$datetime = strtotime($call['time']);
$date = date("M d, Y", $datetime);
$time = date("g:i A", $datetime);
	echo "<b>{$call['name']}</b> ({$call['phonenum']}) on $date at $time&emsp;Duration: {$call['duration']}<br><br>";
}

echo "<h5 style='color:#$color'>Contacts</h5>";
$my_contacts = mysqli_query($con,"SELECT * FROM secure_login.Contacts WHERE userID = $user_id AND categoryID=$category");
while ($contact = mysqli_fetch_array($my_contacts)){
	echo "<b>{$contact['name']}</b>&emsp;Phone: {$contact['phonenum']}&emsp;Email: {$contact['email']}<br><br>";
}

echo "<h5 style='color:#$color'>Text Messages</h5>";
$my_texts = mysqli_query($con,"SELECT * FROM secure_login.Text_Messages WHERE userID = $user_id AND categoryID=$category");
while ($text = mysqli_fetch_array($my_texts)){
$datetime = strtotime($text['time']);
$date = date("M d, Y", $datetime);
$time = date("g:i A", $datetime);
	echo "<b>{$text['name']}</b> ({$text['phonenumber']}) on $date at $time<br>{$text['text_content']}<br><br>";
}
?>
<br><br>

<?php

generateFooter();
?>
   <script type="text/javascript">
      var cat = <?php echo json_encode($category);?>;
      var con = "";
   </script>

   <script type='text/javascript' src="../fullcalendar/fullcalendar/fullcalendar.min.js"></script>
   <script type='text/javascript' src="../fullcalendar/calendar/calendar.js"></script>
   <script type='text/javascript' src="../fullcalendar/fullcalendar/gcal.js"></script>
   <link rel="stylesheet" type="text/css" href="../fullcalendar/calendar/cupertino/jquery-ui.min.css">
   <link rel="stylesheet" type="text/css" href="../fullcalendar/calendar/calendar.css">
   <link rel="stylesheet" type="text/css" href="../fullcalendar/fullcalendar/fullcalendar.css">
<?php
	mysqli_close($con);
?>



