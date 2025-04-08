<?php
  $servername = "sql210.infinityfree.com";
  $username = "if0_38649015";
  $password = "HdUB2Ufhpw";
  $dbname = "if0_38649015_p2db";
  $port = "3306";

  $conn = new mysqli($servername, $username, $password, $dbname, $port);

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }



  
  $conn->close();
?>