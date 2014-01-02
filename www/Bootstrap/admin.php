<?php

//generate pages
require_once("header_updated.php");
require_once("footer_updated.php");

//add header info
generateHeader("Admin");

?>

        <h1 style="margin-top:-15px"><small>Administrator Actions</small></h1>
        <form action="admin_edit.php" method="post">
         <?php
           if(isset($_GET['error_sql'])) {
             echo '<div style="background-color: #FFCC66; color: red; margin-bottom: 15px; padding: 5px;">Member does not exist.</div>';
           }
	  ?>
        <b>Edit Member</b><br><br>

<?php
echo "Email: <input name='email' type='email' value=''><br>";
?>

<button class="flat-btn flat-btn-1" type="submit" onClick="validateFormOnSubmit(this.form);">OK</button>
<button class="flat-btn flat-btn-1" type="reset" onClick="history.go(0)">Cancel</button>
</form>

<?php
//pretty self-explanatory
generateFooter();

//add any after page-load javascript functions below this tag
?>

<script>
function validateFormOnSubmit(theForm) {
        if (theForm.password.value.length!=0) {
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

}
</script>
