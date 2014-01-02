<?php

require_once("loggedin.php");
require_once("db_connect.php");
require_once("reminder_options.php");

function generateHeader($title) {

global $host, $user, $pwd, $db;
$con=mysqli_connect($host,$user,$pwd,$db);

$user_id =  $_SESSION['user_id'];
if(!is_numeric($user_id)) {
	echo "Potential SQL injection attempt. Exiting.";
	exit();
}
$member = mysqli_query($con,"SELECT * FROM secure_login.members WHERE id = $user_id");
$member = mysqli_fetch_array($member);
$username = $member['firstname'];

(basename($_SERVER['PHP_SELF']) == 'calendar.php') ? $isactive="active" : $isactive="none";
?>

<!DOCTYPE html>
<html>
  <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
	<link rel="shortcut icon" href="images/ic_launcher.png">
	<link rel="icon" href="images/ic_launcher.png">
	<meta name="author" content="Terp Tasker Team Red">
  
    <title>Terp Tasker | <?php echo $title;?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link href="css/signin.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

<!--process logins-->
	<script type="text/javascript" src="../Login/sha512.js"></script>
	<script type="text/javascript" src="../Login/forms.js"></script>

<!--check for popup reminders-->
        <script type="text/javascript" src="popup.js"></script>	
	
	<!--color picker-->
<script src="js/jquery.min.js" type="text/javascript"></script>
<script src="colorPicker/jquery.colorPicker.js" type="text/javascript"></script>
<link rel="stylesheet" href="colorPicker/colorPicker.css" type="text/css" />

<script type="text/javascript">
  $(function() {    
	$('#cat_color').colorPicker(); 
	$('#con_color').colorPicker(); 
	$('#cat_color1').colorPicker(); 
	$('#con_color1').colorPicker(); 
  });
</script>

<!---Open Source License:
The MIT License (MIT)

Copyright (c) 2013 University Of Maryland

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
--->

  </head>
  <body onload="check_popup(); setInterval(function(){ check_popup()}, interval_sec * 1000)">  
	<div class="container-fluid">
    <div class="row-fluid">
	<div class="span12">
	    <h3 style="padding-left:20px; float:left;"><a href="chooser.php" style="color:#000"><img src="images/ic_launcher.png" width="35">&emsp;Terp Tasker</a></h3>
		<h5 style="padding-right:20px; float:right;">Welcome, <?php echo $username;?>!&emsp;<a href="logout.php">Logout</a>
		&emsp;<a href="faq.php"><img src="images/icon-help.png" width="20px"></a>
		&emsp;<a href="settings.php"><img src="images/icon-cog.png" width="20px"></a></h5>
	</div>
	
    <div class="span2">
		<ul class="nav nav-list">

		<li class=$isactive><a href="calendar.php">Calendar</a></li>
		
		<li class="nav-header">Category Views</li>

		<?php
$my_cat = mysqli_query($con,"SELECT * FROM secure_login.Category WHERE userID = $user_id");
while ($cat = mysqli_fetch_array($my_cat)){
	(basename($_SERVER['PHP_SELF'])."?".$_SERVER['QUERY_STRING'] == "category.php?c={$cat['categoryID']}") ? $isactive="active" : $isactive="none";
	echo "<li class=$isactive><a href='category.php?c={$cat['categoryID']}' style='color:#{$cat['color']}'>{$cat['name']}</a></li>";
}
?>

		<li><a data-toggle="modal" href="#category" style="padding-top:0px; padding-bottom:10px"><small>+ new category</small></a></li>
		
		<div id="category" class="modal hide fade in" style="display: none; width:400px;">
		<div class="modal-header"><a class="close" data-dismiss="modal">×</a>
		<h4>New category</h4>
		<form method="post" action="add_to_db.php">
		<h5>Name:</h5><input name="category" type="text" style="margin-bottom:10px" autofocus><br>
		<h5>Description:</h5><textarea name="description" style="margin-bottom:10px" placeholder="Optional"></textarea><br>
		<h5>Color:</h5><input name="cat_color" id="cat_color" type="text" > 
		<button class="flat-btn flat-btn-2" type="submit" style="float:right; margin-top:-20px; margin-bottom:20px;">OK</button>
		</form>	
        </div></div>
		
		<li class="nav-header">Context Views</li>

		<?php
$my_con = mysqli_query($con,"SELECT * FROM secure_login.Context WHERE userID = $user_id");
while ($con = mysqli_fetch_array($my_con)){
	(basename($_SERVER['PHP_SELF'])."?".$_SERVER['QUERY_STRING'] == "context.php?c={$con['contextID']}") ? $isactive="active" : $isactive="none";
	echo "<li class=$isactive><a href='context.php?c={$con['contextID']}' style='color:#{$con['color']}'>{$con['name']}</a></li>";
}
?>

		<li><a data-toggle="modal" href="#context" style="padding-top:0px; padding-bottom:10px"><small>+ new context</small></a></li>
		
		<div id="context" class="modal hide fade in" style="display: none; width:400px;">
		<div class="modal-header"><a class="close" data-dismiss="modal">×</a>
		<h4>New context</h4>
		<form method="post" action="add_to_db.php">
		<h5>Name:</h5><input name="context" type="text" style="margin-bottom:10px" autofocus><br>
		<h5>Description:</h5><textarea name="description" style="margin-bottom:10px" placeholder="Optional"></textarea><br>
		<h5>Color:</h5><input name="con_color" id="con_color" type="text" > 
		<button class="flat-btn flat-btn-2" type="submit" style="float:right; margin-top:-20px; margin-bottom:20px;">OK</button>
		</form>	
        </div></div>

		<?php
echo (basename($_SERVER['PHP_SELF']) == "tasks.php") ? "<li class='active'><a href='tasks.php'>All Tasks</a></li>" : "<li><a href='tasks.php'>All Tasks</a></li>";
?>

	<li class='nav-header'>Tag Data</li>

		<?php
echo (basename($_SERVER['PHP_SELF']) == 'calls.php') ? "<li class='active'><a href='calls.php'>Call History</a></li>" : "<li><a href='calls.php'>Call History</a></li>";
echo (basename($_SERVER['PHP_SELF']) == 'contacts.php') ? "<li class='active'><a href='contacts.php'>Contacts</a></li>" : "<li><a href='contacts.php'>Contacts</a></li>";
echo (basename($_SERVER['PHP_SELF']) == 'texts.php') ? "<li class='active'><a href='texts.php'>Text Messages</a></li>" : "<li><a href='texts.php'>Text Messages</a></li>";

if ($member['Administrator']==1) {
	echo "<br>";
	echo (basename($_SERVER['PHP_SELF']) == 'admin.php') ? "<li class='active'><a href='admin_edit.php'>Administrator Panel</a></li>" : "<li><a href='admin_edit.php'>Administrator Panel</a></li>";
}
?>

		</ul>
    </div>	
    <div class="span9">

<?php

mysqli_close($con);

}

?>
