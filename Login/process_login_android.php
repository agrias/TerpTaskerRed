<script type="text/javascript" src="sha512.js"></script>
<?php

include 'db_connect.php';
include 'functions.php';

 
if(isset($_POST['email'], $_POST['password'])) { 
   $email = $_POST['email'];
   $password = $_POST['password']; // The hashed password
   $p2 = hex_sha512($password).
   if(login($email, $p2, $mysqli) == true) {
      // Login success
      echo 'Success';
      //header("Location: ../Bootstrap/calendar.html?");
   } else {
      // Login failed
      echo 'Login failed';
      //header('Location: ../Bootstrap/index.php?error=1');
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