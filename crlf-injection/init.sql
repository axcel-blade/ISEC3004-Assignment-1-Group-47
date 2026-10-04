CREATE TABLE IF NOT EXISTS accounts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    password TEXT NOT NULL,
    balance REAL NOT NULL DEFAULT 0
);

INSERT INTO accounts (username, password, balance) VALUES ('alice', '7abdccbea8473767e91378e37850d296', 1000.00);
INSERT INTO accounts (username, password, balance) VALUES ('bob', '2acba7f51acfd4fd5102ad090fc612ee', 500.00);
