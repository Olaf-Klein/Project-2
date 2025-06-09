-- Table for storing slugs
CREATE TABLE IF NOT EXISTS random_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(255) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for storing code/text associated with a link
CREATE TABLE IF NOT EXISTS html_texts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    random_links_id INT NOT NULL,
    html_text TEXT NOT NULL,
    FOREIGN KEY (random_links_id) REFERENCES random_links(id) ON DELETE CASCADE
);

-- Table for storing users
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);