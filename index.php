<?php

include 'loggedin.php';
//include 'Login/user_activity.php';
//include 'session_vars.php';

// ---content for logged in user below here
//include 'delete_task.php';



echo '<h2>Calendar</h2><br/><h2>Tasks</h2>';
?>
<link href="Bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
<html>
<head>
<body>
<a href="/Bootstrap/changepassword.php">Change your password</a>
<p id = "user_name"></p>
<button onclick="taskInsert()" > Create a Task </button>
<form style="display: none;"id = "insert_task">
Task Name: <input type="text" id = "task-name" name="task_title"><br>
Description: <input type="text" id = "task-description" name="description"><br>
Start Date: <input type="text" id = "task-start" name="start"><br>
Deadline: <input type="text" id = "task-deadline" name="deadline"><br>
Estimated Time: <input type="text" id = "task-time" name="time_estimate"><br>
Category: <input type="text" id = "task-category" name="category"><br>
Prerequisite: <input type="text" id = "task-prereq" name="prereq"><br>
Status: <input type="text" id = "task-status" name="prereq"><br>
<input type="button" onclick="sendTask()" value="Submit Task">
</form>

<button onclick="taskDelete()" > Delete a Task </button>
<form style="display: none;"id = "delete_task">
Task ID:   <input type="text" id = "task-id" name = "task_id"><br>
<input type="button" onclick="removeTask()" value = "Remove Task">
</form>

<button onclick="taskEdit()" > Edit a Task </button>
<table border="1" id = "task_table">
</table>
<form style="display: none;"id = "edit_task">
Task ID: <input type="text" id = "task-id" name = "task_id"><br>
Category: <input type="text" id = "task-category" name="category"><br>
<input type="button" onclick="editTask()" value = "Edit Task">
</form>


<script src="jquery-1.10.2.min.js">
</script>
<script>

//userActivity();
var user;
    $.ajax({ 
        type: 'GET', 
        url: 'session_vars.php', 
        data: { get_param: 'value' }, 
        success: function (data) { 
            user = jQuery.parseJSON(data);
	username();
	
        }
    });
//var user = jQuery.parseJSON<?php echo json_encode(json_decode($user,TRUE)); ?>;

function username(){ 
$("#user_name").text("Hi " + user["firstname"]+ ",");
}
function taskInsert(){
	document.getElementById("insert_task").style.display = "block";
}
function taskDelete() {
	document.getElementById("delete_task").style.display = "block";
}
function taskEdit() {
	//for
	//$("#task_table").
	document.getElementById("edit_task").style.display = "block";
}
function sendTask() {
        var task = {
            title: $("#task-name").val(),
	    description: $("#task-description").val(),
	    start: $("#task-start").val(),
     	    deadline: $("#task-deadline").val(),
 	    time: $("#task-time").val(),
	    category: $("#task-category").val(),
	    prereq: $("#task-prereq").val()    
        };

	task = JSON.stringify(task);
        $.ajax({
            url: 'add_task.php',
            type: 'post',
            dataType: 'json',
  	    data:  { 'task' : task},
            success: function (data) {
              //  $('#target').html(data.msg);
            },
	
        });
    }
function removeTask() {
	var task = {
	   taskid: $("#task-id").val()
	};

	task = JSON.stringify(task);
	$.ajax({
	   url: 'delete_task.php',
	   type: 'post',
	   dataType: 'json',
	   data: { 'task' : task}
	});
}
function editTask() {
	var task = {
 	    taskid: $("task-id").val(),
	    title: $("#task-name").val(),
            //description: $("#task-description").val(),
            //start: $("#task-start").val(),
            //deadline: $("#task-deadline").val(),
            //time: $("#task-time").val(),
            //category: $("#task-category").val(),
            //prereq: $("#task-prereq").val()
        };

        task = JSON.stringify(task);
        $.ajax({
            url: 'edit_task.php',
            type: 'post',
            dataType: 'json',
            data:  { 'task' : task},
	    success: function (data) {
		// $('#target').html(data.msg);
	    },
        });
}
function userActivity() {
        $.ajax({
            url: 'user_activity.php',
            type: 'post',
            dataType: 'json',
            success: function (data) {
              //  $('#target').html(data.msg);
            },
	
        });
}
</script>
</body>
</head>
</html>
<?php
echo '<br/><a href="logout.php">sign out</a><br/>';


// ---end content


?>
