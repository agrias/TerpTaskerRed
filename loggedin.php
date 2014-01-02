<?php

session_start();

//if not logged in, redirect to login page
if(!isset($_SESSION['user_id'], $_SESSION['login_string'])){
  header("Location: Bootstrap/index.php");
  exit;
}

?>
