
<?php
require_once("db_connect.php");
require_once('phpmailer/class.phpmailer.php');
error_reporting(0);
$con= new mysqli($host,$user,$pwd,$db);
$email=$_POST['email'];

if($_POST['submit']=='Submit')
{	
	$query = $con->prepare("SELECT email, password, firstname, salt FROM members WHERE email = '$email'");
	$query->execute();

	$query->store_result();
	$query->bind_result($user_email, $user_password, $user_name, $user_salt);
	$query->fetch();
	

	if($user_email != "null")
	{


		$randpass = generateRandomString(10);
		$body = "Hi $user_name, \n\n Your password to TerpTasker has been reset. Enter the following password to log in: $randpass. \n Please go to your settings to set a new password after logging in.";

		$phpmailer          = new PHPMailer();


		$phpmailer->IsSMTP(); // telling the class to use SMTP
		$phpmailer->Host       = "ssl://smtp.gmail.com"; // SMTP server
		$phpmailer->SMTPAuth   = true;                  // enable SMTP authentication
		$phpmailer->Port       = 465;          // set the SMTP port for the GMAIL server; 465 for ssl and 587 for tls
		$phpmailer->Username   = "terptasker@gmail.com"; // Gmail account username
		$phpmailer->Password   = "HAck%hack5hack";        // Gmail account password

		$phpmailer->SetFrom('terptasker@gmail.com', 'ttasker'); //set from name

		$phpmailer->Subject    = "Your Terp Tasker password has been reset";
		$phpmailer->MsgHTML($body);

		$phpmailer->AddAddress($email, $user_name);

		if(!$phpmailer->Send()) {
			echo "Mailer Error: " . $phpmailer->ErrorInfo;
		} else {
		
			$temp = openssl_digest($randpass, 'sha512');
			$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
			$newpass = hash('sha512', $temp.$random_salt);
			$set_new_pass = $con->prepare("UPDATE members SET password='$newpass', salt='$random_salt' WHERE firstname='$user_name' AND email='$user_email'");
			$set_new_pass->execute();
			echo "Message sent!";
		}
		
	}
	else
	{
		echo "No user exist with this email id";
	}
}

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';		
    $special = '!@#$%^&*';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    $randomString .= $special[rand(0, strlen($special) - 1)];
    return $randomString;
}
?>
<html>
 <head>
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet" media="screen">
	<link href="css/signin.css" rel="stylesheet">

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>

    <![endif]-->
  </head>

<body>

	<div>

		<form action="forgotpassword.php" method="post">
			<h1 class="form-lostpass"><small>Lost Password Reset</small></h1>
			<input type="text" class="form-control" placeholder="Enter your email" name="email"><br>
			<input type="submit" name="submit" class="flat-btn flat-btn-1" value="Submit">
		</form>
	</div>

</body>
<html>
