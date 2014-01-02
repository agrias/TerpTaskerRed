<?php

include 'db_connect.php';
include 'functions.php';

 
if(isset($_POST['email'], $_POST['password']) ) { 
   $email = $_POST['email'];
   $password = $_POST['password']; // The hashed password.


   if(login($email, $password, $mysqli) == true ) {
      // Login success      
      echo 'Success';
      header("Location: user_activity.php");
   } else {
      // Login failed
      echo 'Login failed';
      header("Location: login_fail.php");
      
   }
} else { 
   // The correct POST variables were not sent to this page.
   echo 'Invalid Request';
}
if(login_check($mysqli) == true) {
 
   // Add your protected page content here!
 
} else {
   echo 'You are not authorized to access this page, please login. <br/>';
}
?>
