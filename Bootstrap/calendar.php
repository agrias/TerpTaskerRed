<?php
//code to generate pages
require_once("header_updated.php");
require_once("footer_updated.php");
require_once("category_options.php");
require_once("context_options.php");

//add the header info & navbar
generateHeader("Calendar");

//connect to the database
$con=mysqli_connect($host,$user,$pwd,$db);

//get the current user id & info associated with that user
$user_id =  $_SESSION['user_id'];
if(!is_numeric($user_id)) {
	echo "Potential SQL injection attempt. Exiting.";
	exit();
}
$member = mysqli_query($con,"SELECT * FROM secure_login.members WHERE id = $user_id");
$member = mysqli_fetch_array($member);

//save any array variables you need to pass to functions
$event_e = ($_POST['event-email']=="NULL")?NULL:$_POST['event-email'];
$event_p = ($_POST['event-popup']=="NULL")?NULL:$_POST['event-popup'];
$context_e = ($_POST['context-email']=="NULL")?NULL:$_POST['context-email'];
$context_p = ($_POST['context-popup']=="NULL")?NULL:$_POST['context-popup'];

//add the body of your page below this tag
//note that you don't need any <html> or <body> tags
?>

    <div id='myCalendar'></div>
    <div id="calEventDialog" style="display:none; width:50%;"> 
	<form id="addForm">	
		<input type="hidden" />
		Type&nbsp;
		<input type="radio" name="eventType" value="event" id="typeEvent" required/>Event
		<input type="radio" name="eventType" value="context" id="typeContext" />Context&nbsp;&nbsp;*<br>
		<div id="catInfo" style="display:none;">
		Title&nbsp;<input type="text" name="eventTitle" id="eventTitle"/>*<br>
		Location&nbsp;<input type="text" name="eventLocation" id="eventLocation"/><br>
		URL&nbsp;<input type="url" name="eventURL" id="eventURL"/>**<br>
		Category&nbsp;
			<select name="eventCategory" id="eventCategory"/>
				<?php printCategory();?>
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
		</div>
		<div id="conInfo" style="display:none;">
			Title/Context&nbsp;
			<select name="contextOption" id="contextOption"/>
				<?php printContext();?>
			</select><br>	
			Set Email Reminder&nbsp;
			<select name="contextEmailReminder" id="contextEmailReminder">
				<?php getReminderOptions($context_e);?>
			</select><br>
			Set Popup Reminder&nbsp;
			<select name="contextPopupReminder" id="contextPopupReminder">
				<?php getReminderOptions($context_p);?>
			</select><br>
		</div>
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
		<div id="eventRemFields" >
		</div>
		* Fields are required, no blocks will be added if not completed.
		<br>
		** You should copy and paste the URL from your web browser, incomplete addresses such as "google.com" will not work
	</form>
    </div>
 <div id='submit'>
        <h1 align="center">Import your .ics file</h1>
        <form align="center" id="form" method="post" enctype="multipart/form-data"  action="databaseimport.php">
            <input type="file" name="file" id="file"/>
            <button class="flat-btn flat-btn-2" type="submit" id="btn">Import</button>
        </form>
</div>
 
<?php
//pretty self-explanatory
generateFooter();

//add any after page-load javascript functions below this tag
?>
   <script type="text/javascript">
      var cat = "";
      var con = "";
   </script>
   <script type="text/javascript" src="http://code.jquery.com/ui/1.8.18/jquery-ui.min.js"></script>
   <script type='text/javascript' src="../fullcalendar/fullcalendar/fullcalendar.min.js"></script>
   <script type='text/javascript' src="../fullcalendar/calendar/calendar.js"></script>
   <script type='text/javascript' src="../fullcalendar/fullcalendar/gcal.js"></script>
   <link rel="stylesheet" type="text/css" href="../fullcalendar/calendar/cupertino/jquery-ui.min.css">
   <link rel="stylesheet" type="text/css" href="../fullcalendar/calendar/calendar.css">
   <link rel="stylesheet" type="text/css" href="../fullcalendar/fullcalendar/fullcalendar.css">

<?php
//close the connection
	mysqli_close($con);
?>
