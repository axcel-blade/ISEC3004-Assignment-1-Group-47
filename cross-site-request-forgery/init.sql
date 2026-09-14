CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100),
    password VARCHAR(100) NOT NULL
);

INSERT INTO users (username, email, password) VALUES ('demo', 'demo@example.com', md5('demo123'));
