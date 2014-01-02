<!DOCTYPE html>
<html>
  <head>
    <title>Terp Tasker</title>
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
  </head>
  <body>
	<script type="text/javascript" src="../Login/sha512.js"></script>
	<script type="text/javascript" src="../Login/forms.js"></script>
	
	
	<div class="container-fluid">
    <div class="row-fluid">
	<div class="span12" style="margin-bottom:0px;">
		<h3 style="text-align:center;">Welcome to <img src="images/ic_launcher.png" width="75"> Terp Tasker</h3>
	</div>

<div class="span3">  </div>
	
    <div class="span3">
        <form class="form-signin" action="../Login/process_login.php" method="post" name="login_form">
	 <h1 class="form-signin-heading"><small>Login</small></h1>

         <!--display error message if incorrect credentials -->
         <?php
           if($_GET['error'] == "1") {
             echo '<div style="background-color: #FFCC66; color: red; margin-bottom: 15px; padding: 5px;">Invalid email and/or password, please try again</div>';
         }
         ?>
	
        <input name="email" type="email" class="form-control" placeholder="Email address" autofocus><br>
        <input name="password" type="password" class="form-control" placeholder="Password">
       
		<button class="flat-btn flat-btn-1" type="submit" onclick="formhash(this.form, this.form.password);">Sign in</button>
		<br><br><a href="/Bootstrap/forgotpassword.php">Forgot password?</a>
      </form>
    </div>
	
	<div class="span3">

	<form class="form-signin" id="reg_id" action="../Login/reg_script.php" method="post">
		<?php
           	if($_GET['error'] == "2") {
             	echo '<div style="background-color: #FFCC66; color: red; margin-bottom: 15px; padding: 5px;">An account with this email has already been registered</div>';
        	 }
         	?>
		<h1 class="form-signin-heading"><small>Register</small></h1>
		<input type="text" class="form-control" placeholder="First Name" name="firstname"><br>
		<input type="text" class="form-control" placeholder="Last Name" name="lastname"><br>
        	<input type="email" class="form-control" placeholder="Email address" name="email"><br>
        	<input type="password" class="form-control" placeholder="Password (8-20 chars)" name="password"><br>
		<input type="password" class="form-control" placeholder="Retype Password" name="password2"><br>
		<p><b>Password rules:</b> 8-20 characters, at least <u>ONE</u> number and <u>ONE</u> special character</p>
		
		<?php
                session_start();

                $capFail = $_SESSION['capFail'];
                if($capFail == true) {
                        $message = "Incorrect Captcha!!";
                        echo "<script type='text/javascript'>alert('$message');</script>";
                }
                session_unset();
                ?>		

		<?php
		require_once('recaptchalib.php');
          	$publickey = "6LfRsekSAAAAAH8lTdb-8qnEQs6VgGjhA94WCQE8"; 
          	echo recaptcha_get_html($publickey);		
		?>

		<input type="button" value="Register" class="flat-btn flat-btn-1"  onclick="validateFormOnSubmit(this.form);">

    </div>
<div class="span3">  </div>
<div class="span6">
<br>
<p style="text-align:justify;">
Terp Tasker is more than a to do list. It functions like a personal assistant by allowing you to categorize your time into context blocks based on how productive you are at that time of day, organizing all tasks, events, and data relevant to a project into one dashboard, and reminding you when deadlines are approaching or when you need to set aside more time to finish a task. On your personal Terp Tasker, you can plan how to spend your time, upload contacts, texts, and calls from your phone, and then let Terp Tasker help you manage your work. It isn't always easy for students with a busy schedule to manage and conduct their time efficiently, and Terp Tasker is here to help.
</p>
<br>
<h4 style="text-align:center;">Download the <a href="terptasker.apk">Android App</a></h4>
<br><br>
<p style="text-align:justify;">
Want to run your own instance of Terp Tasker? Install your own instance using our code available at <a href="https://vis.cs.umd.edu/svn/projects/redtasker">our SVN</a> under the MIT License.
</p>
<br>
<p style="text-align:center;"><b>Copyright (c) 2013 University Of Maryland</b></p>
<p>
<br>
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:<br><br>

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.<br><br>

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
</p>
</div>
    </div>
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>


<script>
function validateFormOnSubmit(theForm) {

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
</script>
