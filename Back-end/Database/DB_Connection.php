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

  // Run schema.sql automatically to update tables/columns
  if (file_exists('schema.sql')) {
    $sql = file_get_contents('schema.sql');
    if ($conn->multi_query($sql)) {
      do {
        // flush multi_query results
        if ($result = $conn->store_result()) {
          $result->free();
        }
      } while ($conn->more_results() && $conn->next_result());
    }
  }

  // Save code
  if (isset($_POST['slug']) && isset($_POST['text'])) {
    session_start();
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $slug = $_POST['slug'];
    $text = $_POST['text'];
    
    // Check if slug already exists
    $stmt = $conn->prepare("SELECT id FROM random_links WHERE slug = ?");
    $stmt->bind_param("s", $slug);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
      // Insert the slug with user_id if logged in, else NULL
      if ($user_id !== null) {
        $stmt_insert = $conn->prepare("INSERT INTO random_links (slug, user_id) VALUES (?, ?)");
        $stmt_insert->bind_param("si", $slug, $user_id);
      } else {
        $stmt_insert = $conn->prepare("INSERT INTO random_links (slug, user_id) VALUES (?, NULL)");
        $stmt_insert->bind_param("s", $slug);
      }
      if ($stmt_insert->execute()) {
        $link_id = $stmt_insert->insert_id;
      } else {
        echo json_encode(['success' => false, 'message' => 'Error saving link: ' . $conn->error]);
        $stmt_insert->close();
        $conn->close();
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
        echo json_encode(['success' => true, 'message' => 'Link and text saved successfully!']);
      } else {
        echo json_encode(['success' => false, 'message' => 'Error saving code: ' . $conn->error]);
        exit;
      }
      $stmt_code->close();
    } else {
      // Update the code if it already exists
      $stmt_update = $conn->prepare("UPDATE html_texts SET html_text = ? WHERE random_links_id = ?");
      $stmt_update->bind_param("si", $text, $link_id);
      if ($stmt_update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Text updated successfully!']);
      } else {
        echo json_encode(['success' => false, 'message' => 'Error updating code: ' . $conn->error]);
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

  // User registration
  if (isset($_POST['register_username']) && isset($_POST['register_email']) && isset($_POST['register_password'])) {
    $username = trim($_POST['register_username']);
    $email = trim($_POST['register_email']);
    $password = $_POST['register_password'];
    $confirm = isset($_POST['register_confirm']) ? $_POST['register_confirm'] : '';

    // Basic validation
    if ($password !== $confirm) {
      echo json_encode(['success' => false, 'message' => 'Wachtwoorden komen niet overeen.']);
      $conn->close();
      exit;
    }
    if (strlen($username) < 3 || strlen($password) < 6) {
      echo json_encode(['success' => false, 'message' => 'Gebruikersnaam of wachtwoord te kort.']);
      $conn->close();
      exit;
    }
    // Check if username or email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
      echo json_encode(['success' => false, 'message' => 'Gebruikersnaam of e-mail bestaat al.']);
      $stmt->close();
      $conn->close();
      exit;
    }
    $stmt->close();
    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password_hash);
    if ($stmt->execute()) {
      echo json_encode(['success' => true, 'message' => 'Account succesvol aangemaakt!']);
    } else {
      echo json_encode(['success' => false, 'message' => 'Fout bij registratie.']);
    }
    $stmt->close();
    $conn->close();
    exit;
  }

  // User login
  if (isset($_POST['login_username']) && isset($_POST['login_password'])) {
    session_start();
    $username = trim($_POST['login_username']);
    $password = $_POST['login_password'];
    $stmt = $conn->prepare("SELECT id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 1) {
      $stmt->bind_result($user_id, $password_hash);
      $stmt->fetch();
      if (password_verify($password, $password_hash)) {
        $_SESSION['user_id'] = $user_id;
        // Set a cookie for 7 days
        setcookie('user_id', $user_id, time() + 60 * 60 * 24 * 7, "/");
        echo json_encode(['success' => true, 'message' => 'Succesvol ingelogd!']);
      } else {
        echo json_encode(['success' => false, 'message' => 'Wachtwoord onjuist.']);
      }
    } else {
      echo json_encode(['success' => false, 'message' => 'Gebruiker niet gevonden.']);
    }
    $stmt->close();
    $conn->close();
    exit;
  }

  // Logout
  if (isset($_POST['logout'])) {
    session_start();
    session_unset();
    session_destroy();
    setcookie('user_id', '', time() - 3600, "/");
    echo json_encode(['success' => true, 'message' => 'Uitgelogd!']);
    $conn->close();
    exit;
  }

  // User history (only show code snippets shared by the logged-in user)
  session_start();
  if (isset($_POST['get_user_history']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare(
      "SELECT r.slug, h.html_text FROM random_links r
       JOIN html_texts h ON r.id = h.random_links_id
       WHERE r.user_id = ?
       ORDER BY h.id DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = [];
    while ($row = $result->fetch_assoc()) {
      $history[] = $row;
    }
    echo json_encode(['success' => true, 'history' => $history]);
    $stmt->close();
    $conn->close();
    exit;
  }

  // Get user info for settings page
  session_start();
  if (isset($_POST['get_user_info']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $email);
    if ($stmt->fetch()) {
      echo json_encode(['success' => true, 'username' => $username, 'email' => $email]);
    } else {
      echo json_encode(['success' => false, 'message' => 'Gebruiker niet gevonden.']);
    }
    $stmt->close();
    $conn->close();
    exit;
  }

  // Change password
  session_start();
  if (isset($_POST['change_password']) && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $old = $_POST['old_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_new_password'];
    if ($new !== $confirm) {
      echo json_encode(['success' => false, 'message' => 'Wachtwoorden komen niet overeen.']);
      $conn->close();
      exit;
    }
    if (strlen($new) < 6) {
      echo json_encode(['success' => false, 'message' => 'Nieuw wachtwoord te kort.']);
      $conn->close();
      exit;
    }
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hash);
    if ($stmt->fetch() && password_verify($old, $hash)) {
      $stmt->close();
      $new_hash = password_hash($new, PASSWORD_DEFAULT);
      $stmt2 = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
      $stmt2->bind_param("si", $new_hash, $user_id);
      if ($stmt2->execute()) {
        echo json_encode(['success' => true, 'message' => 'Wachtwoord succesvol gewijzigd!']);
      } else {
        echo json_encode(['success' => false, 'message' => 'Fout bij opslaan.']);
      }
      $stmt2->close();
    } else {
      echo json_encode(['success' => false, 'message' => 'Huidig wachtwoord onjuist.']);
    }
    $conn->close();
    exit;
  }

  echo "Invalid request.";
  $conn->close();
?>