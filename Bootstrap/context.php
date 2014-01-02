<?php
//code to generate pages
require_once("header_updated.php");
require_once("footer_updated.php");

//connect to the database
$con=mysqli_connect($host,$user,$pwd,$db);

//get the current user id & info associated with that user
$user_id =  $_SESSION['user_id'];
$context = $_GET['c'];
if(!is_numeric($context) || !is_numeric($user_id)) {
	echo "Potential SQL injection attempt. Exiting.";
	exit();
}
$con = mysqli_query($con,"SELECT * FROM secure_login.Context WHERE userID = $user_id AND contextID = $context;");
$con = mysqli_fetch_array($con);
$name = $con['name'];

generateHeader($name);

echo "<h1 style='margin-top:-15px; float:left;'><small style='color:#{$con['color']}'>{$con['name']}</small></h1>";

if ($con['name']!="None") {
	echo "<h5 style='margin-top:-10px; margin-right:0px; float:right;'><a data-toggle='modal' href='#edit'>Edit</a>&emsp;<a data-toggle='modal' href='#delete'>Delete</a></h5>";
}
?>
		
	<div id="edit" class="modal hide fade in" style="display: none; width:400px;">
	<div class="modal-header"><a class="close" data-dismiss="modal">x</a>
	<h4>Edit context</h4>
	<form method="post" action="update_db.php">
		<?php
	echo "<h5>Name:</h5><input name='context' type='text' style='margin-bottom:10px' value='{$con['name']}' autofocus><br>
	<h5>Description:</h5><textarea name='description' style='margin-bottom:10px'>{$con['description']}</textarea><br>
	<h5>Color:</h5><input name='con_color1' id='con_color1' type='text' value='#{$con['color']}'> 
	<input name='context_id' type='hidden' value=$context>"
?>
	<button class="flat-btn flat-btn-2" type="submit" style="float:right; margin-top:-20px; margin-bottom:20px;">OK</button>
	</form>	
	</div></div>
	
	<div id="delete" class="modal hide fade in" style="display: none; width:400px;">
	<div class="modal-header"><a class="close" data-dismiss="modal">x</a>
	<h4>Delete context</h4>
	<form method="post" action="delete_from_db.php">
		<?php
	echo "Are you sure you want to delete the context {$con['name']}?<br><br>
	<input type='checkbox' name='time_blocks' value='yes'> Also delete all {$con['name']} time blocks 
	<input name='context_id' type='hidden' value=$context>"
?>
	<button class="flat-btn flat-btn-2" type="submit" style="float:right; margin-bottom:20px;">OK</button>
	</form>	
	</div></div>	
	
<br><br>
    <div id='myCalendar'></div>

    <div id="calEventDialog" style="display:none;">
	<form id="addForm">
		<input type="hidden" />
		Type&nbsp;
		<input type="radio" name="eventType" value="context" id="typeContext" checked="checked" required/>Context<br>
		Title/Context&nbsp;
		<select name="contextOption" id="contextOption"/>
		 	<?php echo "<option selected value='$context'>$name</option>";?>
		</select><br>	
		Set Email Reminder&nbsp;
		<select name="contextEmailReminder" id="contextEmailReminder">
			<?php getReminderOptions($context_e);?>
		</select><br>
		Set Popup Reminder&nbsp;
		<select name="contextPopupReminder" id="contextPopupReminder">
			<?php getReminderOptions($context_p);?>
		</select><br>		
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
		<input type="button" id="postponeBlock" value="Postpone Block" title="Postpone by 15 minutes"/>
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
	var newtask = document.getElementById('htab_newtask');
	newtask.style.display='none';
	var mainList = -1;
	var contextView = <?php echo json_encode($context);?>;
   </script>
<?php
//pretty self-explanatory
generateFooter();

//add any after page-load javascript functions below this tag
?>

   <script type="text/javascript">
      var con = <?php echo json_encode($context);?>;
      var cat = "";
   </script>

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
