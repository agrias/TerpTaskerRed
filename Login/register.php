<script type="text/javascript" src="forms.js"></script>
<script type="text/javascript" src="sha512.js"></script>

<style type="text/css">
    #reg_id {
	text-align: center 
    }
    
    </style>
<html>
<head>
<body>
<h2> Register with Terp Tasker Red </h2>
<form id = "reg_id"action="reg_script.php" method="post">
<div class="container">
First Name: <input type="text" name="firstname"><br><br>
Last Name: <input type="text" name="lastname"><br><br>
E-mail: <input type="text" name="email"><br><br>
<font size="1" color="red">(MUST INCLUDE AT LEAST 1 NUMBER AND 1 SPECIAL CHARACHTER)</font><br>
Create a Password (8-20 chars) <input type="password" name="password"><br><br>
Retype Password <input type="password" maxLength = "20" name="password2"><br><br>
	<?php
	require_once('recaptchalib.php');
	$publickey = "6LfRsekSAAAAAH8lTdb-8qnEQs6VgGjhA94WCQE8";
	echo recaptcha_get_html($publickey);
	?>
</div>
<input type="button" value="Register" onclick="validateFormOnSubmit(this.form);">

</form>

</body>
</head>
</html>
<script>
function validateFormOnSubmit(theForm) {
	var textLength = theForm.password.value.length;
	if ( textLength < 8 || theForm.password.value != theForm.password2.value){
	 	alert("Some fields need correction");
		return false;
	}
	formhash(theForm, theForm.password);
	
}
</script>
