<?php


	include '../loggedin.php';
	require_once("db_connect.php");
	$con=mysqli_connect($host,$user,$pwd,$db);
	$user_id = $_SESSION['user_id'];
	
	if($_POST['submit']=='Submit'){
	
		$currpass = $_POST['currpass'];
		$currpass = hash('sha512', $currpass);
		$query = $con->prepare("SELECT email, password, firstname, salt FROM members WHERE id = '$user_id'");
		$query->execute();

		$query->store_result();
		$query->bind_result($user_email, $user_password, $user_name, $user_salt);
		$query->fetch();

		$realpass = hash('sha512', $currpass.$user_salt);
		
		if($realpass != $user_password){
			echo "<script> alert('Your current password is incorrect!') </script>";
			
		}else{
		
			$temppass = $_POST['confirmpass'];
			
			$temp = openssl_digest($temppass, 'sha512');
			$random_salt = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
			$newpass = hash('sha512', $temp.$random_salt);
			$set_new_pass = $con->prepare("UPDATE members SET password='$newpass', salt='$random_salt' WHERE id='$user_id'");
			$set_new_pass->execute();
			echo "Password changed!";
		}
	}
?>

<html>
 <head>
    <title>Change Password</title>
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

		<form action="changepassword.php" method="post">
			<h1 class="form-changepass"><small>Change Password</small></h1>
			<input type="password" class="form-control" placeholder="Enter your current password" name="currpass"><br>
			<input type="password" class="form-control" placeholder="Enter new password" name="newpass"><br>
			<input type="password" class="form-control" placeholder="Re-enter new password" name="confirmpass"><br>
			<p><b>Password rules:</b> 8-20 characters, at least <u>ONE</u> number and <u>ONE</u> special character</p>
			<input type="submit" name="submit" class="flat-btn flat-btn-1" value="Submit" onclick="validateFormOnSubmit(this.form);">
		</form>
	</div>

</body>
<html>

<script>
function validateFormOnSubmit(theForm) {

	
	if(theForm.newpass.value != theForm.confirmpass.value){
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
		
}
</script>
