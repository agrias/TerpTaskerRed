<html> 
<?php 

include 'db_connect.php';
session_start();
$con=mysqli_connect("127.0.0.1","root","Hack5hack%hack","secure_login");

if (mysqli_connect_errno($con))
  {
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
  }
//captcha verification
require_once('recaptchalib.php');
$privatekey = "6LfRsekSAAAAAEbfwYOhAqDG1Af7uXj6n1SBKRqa";
$resp = recaptcha_check_answer ($privatekey,
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

if (!$resp->is_valid) {
  // What happens when the CAPTCHA was entered incorrectly
  $_SESSION['capFail'] = true;
  header('Location: ' . $_SERVER['HTTP_REFERER']);
  exit;
} else {
  echo "Entered Correctly!";

}  


echo $_POST["firstname"]; 
echo $_POST["lastname"]; 
echo $_POST["password"]; 
echo $_POST["email"]; 


// The hashed password from the form
$firstname = $_POST['firstname']; 
$lastname = $_POST['lastname']; 
$password = $_POST['password']; 
$email = $_POST['email']; 
$admin = 0;

// Create a random salt
$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
// Create salted password (Careful not to over season)
$password = hash('sha512', $password.$random_salt);

// Add your insert to database script here. 
// Make sure you use prepared statements!
$check= mysqli_query($con,"SELECT * FROM secure_login.members WHERE email = '$email'");
$check = mysqli_fetch_assoc($check);
//var_dump($check);
var_dump(count($check));
//account already exists
if(count($check) > 0)
{
header('Location: ../Bootstrap/index.php?error=2');
}
else if ($insert_stmt = $mysqli->prepare("INSERT INTO members (firstname, lastname, email, password, salt, Administrator) VALUES (?, ?, ?, ?, ?, ?)")) {    
   $insert_stmt->bind_param('sssssi', $firstname, $lastname, $email, $password, $random_salt, $admin); 
   // Execute the prepared query.
   $insert_stmt->execute();
   date_default_timezone_set("America/New_York");
   $time = date("Y\-m\-d h:i:s",strtotime("-20 minutes"));
   $id = mysqli_query($con,"SELECT id FROM secure_login.members WHERE email = '$email'");
   $id = mysqli_fetch_array($id);
   $id = $id['id'];
   $action = "$email has registered";
   $ip_address = $_SERVER['REMOTE_ADDR']; 
   $stmt = mysqli_prepare($con, "INSERT INTO secure_login.user_Activity (userID, timestamp, ip_address, action) 
				VALUES (?,?,?,?)");


   mysqli_stmt_bind_param($stmt, 'isss',$id, $time, $ip_address,$action);

   mysqli_stmt_execute($stmt);

   mysqli_stmt_close($stmt);

   //New user add Category "None" to database
   $stmt = mysqli_prepare($con, "INSERT INTO secure_login.Category (userID, name, d_created) VALUES (?,?,?)");
   $none = "None";
   $d_created = time();
   mysqli_stmt_bind_param($stmt, 'isi',$id, $none, $d_created);
   mysqli_stmt_execute($stmt);
   mysqli_stmt_close($stmt);

   //New user add 4 contexts to database
   $stmt = mysqli_prepare($con, "INSERT INTO secure_login.Context (userID, name, color) VALUES (?,?,?)");
   $one_cup = "None";
   $color1 = "808080";
   mysqli_stmt_bind_param($stmt, 'iss',$id, $one_cup, $color1);
   mysqli_stmt_execute($stmt);
   mysqli_stmt_close($stmt);

   $stmt = mysqli_prepare($con, "INSERT INTO secure_login.Context (userID, name, color) VALUES (?,?,?)");
   $one_cup = "2 Cups of Coffee";
   $color1 = "FF0000";
   mysqli_stmt_bind_param($stmt, 'iss',$id, $one_cup, $color1);
   mysqli_stmt_execute($stmt);
   mysqli_stmt_close($stmt);

   $stmt = mysqli_prepare($con, "INSERT INTO secure_login.Context (userID, name, color) VALUES (?,?,?)");
   $one_cup = "3 Cups of Coffee";
   $color1 = "FF9900";
   mysqli_stmt_bind_param($stmt, 'iss',$id, $one_cup, $color1);
   mysqli_stmt_execute($stmt);
   mysqli_stmt_close($stmt);

   $stmt = mysqli_prepare($con, "INSERT INTO secure_login.Context (userID, name, color) VALUES (?,?,?)");
   $one_cup = "1 Cup of Coffee";
   $color1 = "666699";
   mysqli_stmt_bind_param($stmt, 'iss',$id, $one_cup, $color1);
   mysqli_stmt_execute($stmt);
   mysqli_stmt_close($stmt);

   header("Location: reg_success.php");
}


?>

</html>
