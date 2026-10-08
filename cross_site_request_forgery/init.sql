CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL,
    email TEXT,
    password TEXT NOT NULL
);

INSERT INTO users (username, email, password) VALUES ('demo', 'demo@example.com', '62cc2d8b4bf2d8728120d052163a77df');

CREATE TABLE IF NOT EXISTS sessions (
    id TEXT PRIMARY KEY,
    data TEXT NOT NULL,
    last_access INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS csrf_tokens (
    session_id TEXT PRIMARY KEY,
    token TEXT NOT NULL,
    created_at INTEGER NOT NULL
);
