<?php
//code to generate pages

include_once("header_updated.php");
include_once("footer_updated.php");

include_once("loggedin.php");
//require_once("db_connect.php");
//include '../Login/functions.php';

//add the header info & navbar
generateHeader("Admin");

//connect to the database
$con=mysqli_connect("127.0.0.1","root","Hack5hack%hack","secure_login");

$edit_email = $_POST['email'];

$stmt = mysqli_prepare($con, "SELECT id, firstname, lastname, Administrator FROM members WHERE email = ?");
mysqli_stmt_bind_param($stmt, 's', $edit_email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
$numrows = mysqli_stmt_num_rows($stmt);
mysqli_stmt_bind_result($stmt, $id, $first, $last, $admin);
mysqli_stmt_fetch($stmt);




$_SESSION['member_id'] = $id;

if($admin == 1) {
	$admin_val = "Yes";
} else {
	$admin_val = "No";
}

//add the body of your page below this tag
//note that you don't need any <html> or <body> tags
?>
        <h1 style="margin-top:-15px"><small>Administrator Actions</small></h1>
       
         <?php
           if(isset($_GET['success'])) {
             echo '<div style="background-color: #A6D785; color: green; margin-bottom: 15px; padding: 5px;">Success!</div>';
           } 
         ?>
        <b>Edit Information</b><br><br>
	<p>Click on Edit to update member information, then click Save.</p><br>
<div id="dialog-confirm" title="Empty the recycle bin?">
<form id = "userform" style = "display:none; margin-left:auto; margin-right: auto;">
<?php

 echo "<b>Member:</b>&emsp;&nbsp;&nbsp; {$first} {$last}<br><br>";
 echo "First name:&nbsp;&nbsp;<input id = 'edit_first' name='firstname' type='text' ><br>";
 echo "Last name:&nbsp;&nbsp;<input id = 'edit_last' name='lastname' type='text' ><br>";
 echo "Email:&emsp;&emsp;&nbsp;&nbsp;&nbsp;<input id = 'edit_email' name='email' type='email' '><br>";
 echo "Admin:&emsp;&emsp;&nbsp;&nbsp<input id = 'edit_admin'name='admin' type='checkbox' <br>"; 
 echo "Active:&emsp;&emsp;&nbsp;&nbsp<input id = 'edit_active'name='active' type='checkbox' <br>"; 

?>
<br><br>
<button class="flat-btn flat-btn-1"  onclick="edit_user();">Save</button>
<button class="flat-btn flat-btn-1" type="reset" onClick="history.go(0)">Cancel</button>
</form >
<div id ='my_table'" >
<table  class="table table-striped  table-bordered table-hover" id = "members_table" > <tbody><tr style = "background-color:#C0C0C0; overflow: visible;"><td>First Name</td><td>Last Name</td><td>Email Name</td><td>Admin </td> <td>Actions</td></b></tr></table> </div>
<span id ="user_id" ></span>

<?php
//pretty self-explanatory
generateFooter();

//add any after page-load javascript functions below this tag
?>

<script>

var users;

    $.ajax({ 
        type: 'GET', 
        url: 'get_all_members.php', 
        data: { get_param: 'value' }, 
        success: function (data) { 
        	users = jQuery.parseJSON(data);
		//alert(users);
		create_table();
	
        } 
    });
/*

$.ajax({url: "get_all_members.php", dataType: 'json'}).done(function(data){
    //console.log($.parseJSON(data));
 	users = jQuery.parseJSON(data);
	create_table();
});
*/
//creates user table
function create_table(){
$('#my_table').css({overflow: 'scroll', height: '70%', width : '1000px'});
$( "tr:first" ).css( "font-weight", "bold");
var adminList = new Array();
for( var i = 0; i < users.length; i++){
	if (users[i]['Administrator'] == 1)
	{
		adminList[i] = "Yes";
	}
	else
	{
		adminList[i] ="No";
	}
}
for( var i = 0; i < users.length; i++){
$('#members_table').append( '<tr><td>' +  users[i]['firstname'] + '</td>'+
'<td>' + users[i]['lastname'] + '</td>' + '<td>' + users[i]['email'] + '</td>' + 
'<td>' + adminList[i] + '</td> <td><button class="flat-btn flat-btn-1" type="submit" onclick="fill_user('+ i+');">Edit</button><button class="btn btn-danger" type="submit" onclick="delete_user('+ i+');">Delete</button> </td></tr>');
}

$('td').css({width : '200px'});
}
function fill_user(id){
var edit;
$("#edit_first").val(users[id]['firstname']);
$("#edit_last").val(users[id]['lastname']);
$("#edit_email").val(users[id]['email']);
if(users[id]['Administrator'] == 1)
{
	$("#edit_admin").attr('checked', true);
}
else
{
	$("#edit_admin").attr('checked', false);;
}
if(users[id]['active'] == 0)
{
	$("#edit_active").attr('checked', false);
}
else
{
	$("#edit_active").attr('checked', true);;
}

$("#user_id").attr('name', id)
//alert($("#user_id").attr('name'));
$("#userform").fadeIn();
}
function edit_user(){
var user;
var firstname = $("#edit_first").val();
var lastname = $("#edit_last").val();
var email = $("#edit_email").val();
var admin = 0;
var active = 0;
if ($("#edit_admin").is(':checked')){
admin = 1;
}
if ($("#edit_active").is(':checked')){
active = 1;
}
var id = users[$("#user_id").attr('name')]['id'];
user = {id : id, first_name: firstname,last_name: lastname,email : email, admin: admin, active: active};
 user = JSON.stringify(user);
//alert(user);
$.ajax({
            url: 'edit_member.php',
            type: 'post',
            dataType: 'json',
            data:  { 'user' : user},
	    success: function (data) {
		// $('#target').html(data.msg);
	    },
        });
}
function delete_user(userID){

 $(function() {
    $( "#dialog-confirm" ).dialog({
      resizable: false,
      height:140,
      modal: true,
      buttons: {
        "Delete all items": function() {
          $( this ).dialog( "close" );
        },
        Cancel: function() {
          $( this ).dialog( "close" );
        }
      }
    });
  });
/*
var user;
var id = user
user = {id : id};
 user = JSON.stringify(user);
//alert(user);
$.ajax({
            url: 'delete_member.php',
            type: 'post',
            dataType: 'json',
            data:  { 'user' : user},
	    success: function (data) {
		// $('#target').html(data.msg);
	    },
        });
*/
}

</script>
<?php mysqli_close($con); ?>
