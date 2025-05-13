<?php
  // Database connection settings
  $servername = "sql210.infinityfree.com";
  $username = "if0_38649015";
  $password = "HdUB2Ufhpw";
  $dbname = "if0_38649015_p2db";
  $port = "3306";

  // Create a new database connection
  $conn = new mysqli($servername, $username, $password, $dbname, $port);

  // Check if the connection was successful
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Get the slug and text from the AJAX request
  $slug = $_POST['slug'];
  $text = $_POST['text'];

  // Insert the link into the random_links table
  $sql = "INSERT INTO random_links (slug) VALUES ('$slug')";
  if ($conn->query($sql) === TRUE) {
    $link_id = $conn->insert_id;
  } else {
    echo "Error saving link: " . $conn->error;
    exit;
  }

  // Insert the code into the html_texts table
  $sql = "INSERT INTO html_texts (random_links_id, html_text) VALUES ('$link_id', '$text')";
  if ($conn->query($sql) === TRUE) {
    echo "Link and text saved successfully!";
  } else {
    echo "Error saving code: " . $conn->error;
  }

  // Close the database connection
  $conn->close();
?>