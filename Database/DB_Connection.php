<?php
  // Database connection settings
  $servername = "localhost";
  $username = "root";
  $password = "";
  $port = 3306;
  $dbname = "testproject2";

  // Create a new database connection
  $conn = new mysqli($servername, $username, $password, $dbname, $port);

  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  // Save code
  if (isset($_POST['slug']) && isset($_POST['text'])) {
    $slug = $_POST['slug'];
    $text = $_POST['text'];
    
    // Check if slug already exists
    $stmt = $conn->prepare("SELECT id FROM random_links WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
      // Insert the slug
      $stmt_insert = $conn->prepare("INSERT INTO random_links (slug) VALUES (?)");
      $stmt_insert->bind_param("s", $slug);
      if ($stmt_insert->execute()) {
        $link_id = $stmt_insert->insert_id;
      } else {
        echo "Error saving link: " . $conn->error;
        exit;
      }
      $stmt_insert->close();
    } else {
      // Slug exists, get its id
      $stmt->bind_result($link_id);
      $stmt->fetch();
    }
    $stmt->close();

    // Check if code already exists for this link_id
    $stmt_check = $conn->prepare("SELECT id FROM html_texts WHERE random_links_id = ?");
    $stmt_check->bind_param("i", $link_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows === 0) {
      // Insert the code
      $stmt_code = $conn->prepare("INSERT INTO html_texts (random_links_id, html_text) VALUES (?, ?)");
      $stmt_code->bind_param("is", $link_id, $text);
      if ($stmt_code->execute()) {
        echo "Link and text saved successfully!\n";
      } else {
        echo "Error saving code: " . $conn->error;
        exit;
      }
      $stmt_code->close();
    } else {
      // Update the code if it already exists
      $stmt_update = $conn->prepare("UPDATE html_texts SET html_text = ? WHERE random_links_id = ?");
      $stmt_update->bind_param("si", $text, $link_id);
      if ($stmt_update->execute()) {
        echo "Text updated successfully!\n";
      } else {
        echo "Error updating code: " . $conn->error;
        exit;
      }
      $stmt_update->close();
    }
    $stmt_check->close();
    $conn->close();
    exit;
  }

  // Retrieve code by slug
  if (isset($_POST['get_slug'])) {
    $slug = $_POST['get_slug'];
    $stmt = $conn->prepare(
      "SELECT h.html_text 
       FROM random_links r 
       JOIN html_texts h ON r.id = h.random_links_id 
       WHERE r.slug = ? 
       LIMIT 1"
    );
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $stmt->bind_result($retrieved_text);
    if ($stmt->fetch()) {
      echo $retrieved_text;
    } else {
      echo "No code found for this slug.";
    }
    $stmt->close();
    $conn->close();
    exit;
  }

  echo "Invalid request.";
  $conn->close();
?>