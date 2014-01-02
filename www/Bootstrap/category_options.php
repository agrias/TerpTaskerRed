<?php
/**
 * Gets categories and outputs options
 * PHP Version 5
 *
 * @author   Billy Wu
 */
require_once("/var/www/Bootstrap/loggedin.php");
require_once("/var/www/Bootstrap/db_connect.php");

function printCategory() {
   global $host, $user, $pwd, $db;
   $con=new mysqli($host, $user,$pwd, $db);
   if(mysqli_connect_errno()){  
	echo "failed to connect";
   }
   $user_id=intval($_SESSION['user_id']);
   $query=$con->stmt_init();
   $query=$con->prepare("SELECT categoryID,name FROM secure_login.Category where userID=?");
   $query->bind_param("i",$user_id);
   $query->execute();
   $query->bind_result($catID,$catName);
   while ($query->fetch()) {
      echo "<option value='$catID'>$catName</option>";
   }
}
?>
