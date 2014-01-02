<?php
//code to generate pages
require_once("header_updated.php");
require_once("footer_updated.php");

//add the header info & navbar
generateHeader("Text Messages");

?>

<h1 style='margin-top:-15px; float:left;'><small>Help</small></h1>
<br><br>
//Help file goes here


//and ends here

		<?php
generateFooter();

	mysqli_close($con);
?>
