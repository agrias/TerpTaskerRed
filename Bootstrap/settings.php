<?php
//code to generate pages
require_once("header_updated.php");
require_once("footer_updated.php");

//add the header info & navbar
generateHeader("Settings");

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
$task_e = $member['task_email_remind'];
$task_p = $member['task_popup_remind'];
$event_e = $member['event_email_remind'];
$event_p = $member['event_popup_remind'];
$context_e = $member['context_email_remind'];
$context_p = $member['context_popup_remind'];

//add the body of your page below this tag
//note that you don't need any <html> or <body> tags
?>

	<h1 style="margin-top:-15px"><small>Settings</small></h1>
	<form action="settings_submit.php" method="post">
         <?php
           if(isset($_GET['error_delete'])) {
             echo '<div style="background-color: #FFCC66; color: red; margin-bottom: 15px; padding: 5px;">Invalid password; account not deleted. Please try again.</div>';
         }
           if(isset($_GET['error_password'])) {
             echo '<div style="background-color: #FFCC66; color: red; margin-bottom: 15px; padding: 5px;">Invalid password; password not reset. Please try again.</div>';
         }
           if(isset($_GET['success'])) {
             echo '<div style="background-color: #A6D785; color: green; margin-bottom: 15px; padding: 5px;">Success!</div>';
         }
         ?>
	<b>Basic Information</b><br>
<?php
 echo "First name: <input name='firstname' type='text' value='{$member['firstname']}'><br>"; 
 echo "Last name: <input name='lastname' type='text' value='{$member['lastname']}'><br>"; 
 echo "Email:&emsp;&emsp;&nbsp; <input name='email' type='email' value='{$member['email']}'><br><br>"; 
//Current:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input name="curr_pass" type="password" placeholder="Current Password"><br>
?>	
	<b>Change Password</b><br>
	New:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input name="password" type="password" placeholder="New Password" value=""><br>
	Retype new: <input name="password2" type="password" placeholder="Retype New Password" value="">
	<p><b>Password rules:</b> 8-20 characters, at least <u>ONE</u> number and <u>ONE</u> special character</p><br>
		
	<b>Default Reminder Settings</b><br>
	<p>You can change these settings for each individual task, event, and context block.</p>
	<b>Tasks:</b> Send me a reminder before a task deadline<br>
	Email: 
	<select name="task-email" style="width:150px">
		<?php getReminderOptions($task_e); ?>
	</select>
	&emsp;Pop up/app notification:
	<select name="task-popup" style="width:150px">
		<?php getReminderOptions($task_p); ?>
	</select>
<br>
	<b>Events:</b> Send me a reminder before an event starts<br>
	Email: 
	<select name="event-email" style="width:150px">
		<?php getReminderOptions($event_e); ?>
	</select>
	&emsp;Pop up/app notification:
	<select name="event-popup" style="width:150px">
		<?php getReminderOptions($event_p); ?>
	</select>
<br>
	<b>Context Blocks:</b> Send me a reminder before a context block begins<br>
	Email: 
	<select name="context-email" style="width:150px">
		<?php getReminderOptions($context_e); ?>
	</select>
	&emsp;Pop up/app notification:
	<select name="context-popup" style="width:150px">
		<?php getReminderOptions($context_p); ?>
	</select>
<br><br>
<h5><a data-toggle="modal" href="#delete">Delete Your Account</a></h5>

	<button class="flat-btn flat-btn-1" type="submit" onclick="validateFormOnSubmit(this.form);">OK</button>
	<button class="flat-btn flat-btn-1" type="reset" onClick="history.go(0)">Cancel</button>
  </form>

	<div id="delete" class="modal hide fade in" style="display: none; width:400px;">
	<div class="modal-header"><a class="close" data-dismiss="modal">x</a>
	<h4>Delete your account</h4>
	<form method="post" action="delete_from_db.php">
	Are you sure you want to delete your account? <br>This will permanently remove all events, tasks, and data associated with your account from our database.
<br><br>Enter your password: <input name="password_delete" type="password">
	<button class="flat-btn flat-btn-2" type="submit" style="float:right; margin-bottom:20px;" onclick="formhash(this.form, this.form.password_delete);">OK</button>
	</form>	
	</div></div>	
  
<?php
//pretty self-explanatory
generateFooter();

//add any after page-load javascript functions below this tag
?>

<script>
function validateFormOnSubmit(theForm) {
	if (theForm.password.value.length!=0) {
	var textLength = theForm.password.value.length;
	if ( textLength < 8){
		alert("Your password length is too small!");
		return false
	}
	if(theForm.password.value != theForm.password2.value){
	 	alert("Your passwords don't match!");
		return false;
	}
	var pass = theForm.password.value;
	var matches = pass.match(/\W+/g);
	if( matches == null )
	{
		alert("You password does not contain a number and/or special character!")
		return false;
	}
			
	formhash(theForm, theForm.password);
	}
	
}
</script>

<?php
//close the connection
	mysqli_close($con);
?>