CREATE TABLE IF NOT EXISTS accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(100) NOT NULL,
    balance DECIMAL(12,2) NOT NULL DEFAULT 0
);

INSERT INTO accounts (username, password, balance) VALUES ('alice', md5('alice123'), 1000.00);
INSERT INTO accounts (username, password, balance) VALUES ('bob', md5('bob123'), 500.00);
