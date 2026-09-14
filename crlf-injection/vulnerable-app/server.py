import socket
import sqlite3
import secrets
import hashlib
from urllib.parse import urlparse, parse_qs, unquote

HOST = "0.0.0.0"
PORT = 8086
DB_PATH = "bank.db"

sessions = {}


def init_db():
    con = sqlite3.connect(DB_PATH)
    cur = con.cursor()
    cur.execute("""CREATE TABLE IF NOT EXISTS accounts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        password TEXT NOT NULL,
        balance REAL NOT NULL DEFAULT 0
    )""")
    cur.execute("SELECT COUNT(*) FROM accounts")
    if cur.fetchone()[0] == 0:
        cur.execute("INSERT INTO accounts (username, password, balance) VALUES (?, ?, ?)",
                    ("alice", hashlib.md5(b"alice123").hexdigest(), 1000.00))
        cur.execute("INSERT INTO accounts (username, password, balance) VALUES (?, ?, ?)",
                    ("bob", hashlib.md5(b"bob123").hexdigest(), 500.00))
    con.commit()
    con.close()


def get_account(username=None, account_id=None):
    con = sqlite3.connect(DB_PATH)
    cur = con.cursor()
    if username is not None:
        cur.execute("SELECT id, username, password, balance FROM accounts WHERE username = ?", (username,))
    else:
        cur.execute("SELECT id, username, password, balance FROM accounts WHERE id = ?", (account_id,))
    row = cur.fetchone()
    con.close()
    return row


def update_balance(account_id, delta):
    con = sqlite3.connect(DB_PATH)
    cur = con.cursor()
    cur.execute("UPDATE accounts SET balance = balance + ? WHERE id = ?", (delta, account_id))
    con.commit()
    con.close()


def parse_request(raw):
    head, _, body = raw.partition("\r\n\r\n")
    lines = head.split("\r\n")
    request_line = lines[0]
    method, raw_path, _ = request_line.split(" ")
    headers = {}
    for line in lines[1:]:
        if ":" in line:
            k, v = line.split(":", 1)
            headers[k.strip().lower()] = v.strip()
    return method, raw_path, headers, body


def get_session(headers):
    cookie = headers.get("cookie", "")
    for part in cookie.split(";"):
        if "=" in part:
            k, v = part.strip().split("=", 1)
            if k == "session" and v in sessions:
                return v
    return None


def safe_header_bytes(status_line, header_lines):
    out = status_line + "\r\n"
    for name, value in header_lines:
        out += f"{name}: {value}\r\n"
    out += "\r\n"
    return out.encode("iso-8859-1", errors="replace")


LOGIN_PAGE = """<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>SecureBank Login</title></head>
<body>
<h2>SecureBank Login</h2>
<p style="color:red">{error}</p>
<form action="/" method="post">
    <div><label>Username</label><input type="text" name="username"></div>
    <div><label>Password</label><input type="password" name="password"></div>
    <div><input type="submit" value="Login"></div>
</form>
</body></html>"""

DASHBOARD_PAGE = """<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>SecureBank Dashboard</title></head>
<body>
<h2>Welcome, {username}</h2>
<p>Current balance: ${balance:.2f}</p>
<h3>Transfer Funds</h3>
<form action="/transfer" method="post">
    <div><label>Recipient account ID</label><input type="text" name="recipient_id"></div>
    <div><label>Amount</label><input type="text" name="amount"></div>
    <input type="hidden" name="next" value="/dashboard">
    <div><input type="submit" value="Transfer"></div>
</form>
<p><a href="/logout">Log out</a></p>
</body></html>"""


def handle(conn):
    raw = conn.recv(8192).decode("iso-8859-1", errors="replace")
    if not raw:
        return

    method, raw_path, headers, body = parse_request(raw)
    parsed = urlparse(raw_path)
    path = parsed.path
    query = parse_qs(parsed.query)
    form = parse_qs(body) if method == "POST" else {}

    session_id = get_session(headers)

    if path == "/" and method == "GET":
        if session_id:
            conn.sendall(safe_header_bytes("HTTP/1.1 302 Found", [("Location", "/dashboard")]))
            return
        html = LOGIN_PAGE.format(error="")
        conn.sendall(safe_header_bytes("HTTP/1.1 200 OK", [("Content-Type", "text/html")]) + html.encode())
        return

    if path == "/" and method == "POST":
        username = form.get("username", [""])[0]
        password = form.get("password", [""])[0]
        row = get_account(username=username)
        if row and row[2] == hashlib.md5(password.encode()).hexdigest():
            sid = secrets.token_hex(16)
            sessions[sid] = row[0]
            conn.sendall(safe_header_bytes("HTTP/1.1 302 Found", [
                ("Location", "/dashboard"),
                ("Set-Cookie", f"session={sid}; HttpOnly"),
            ]))
        else:
            html = LOGIN_PAGE.format(error="Invalid username or password.")
            conn.sendall(safe_header_bytes("HTTP/1.1 200 OK", [("Content-Type", "text/html")]) + html.encode())
        return

    if path == "/dashboard" and method == "GET":
        if not session_id:
            conn.sendall(safe_header_bytes("HTTP/1.1 302 Found", [("Location", "/")]))
            return
        account = get_account(account_id=sessions[session_id])
        html = DASHBOARD_PAGE.format(username=account[1], balance=account[3])
        conn.sendall(safe_header_bytes("HTTP/1.1 200 OK", [("Content-Type", "text/html")]) + html.encode())
        return

    if path == "/transfer" and method == "POST":
        if not session_id:
            conn.sendall(safe_header_bytes("HTTP/1.1 302 Found", [("Location", "/")]))
            return

        sender_id = sessions[session_id]
        recipient_id = form.get("recipient_id", [""])[0]
        amount = form.get("amount", ["0"])[0]
        next_url = form.get("next", ["/dashboard"])[0]
        next_url = unquote(next_url)

        try:
            amount_val = float(amount)
            recipient_id_val = int(recipient_id)
            if amount_val > 0:
                update_balance(sender_id, -amount_val)
                update_balance(recipient_id_val, amount_val)
        except (ValueError, TypeError):
            pass

        # VULNERABLE: 'next' is attacker-controlled and written directly into the
        # raw response bytes with no CRLF filtering, unlike PHP's header() or
        # Werkzeug's Response, which both reject embedded \r\n.
        response = (
            "HTTP/1.1 302 Found\r\n"
            "Location: " + next_url + "\r\n"
            "\r\n"
        ).encode("iso-8859-1", errors="replace")
        conn.sendall(response)
        return

    if path == "/logout" and method == "GET":
        if session_id:
            sessions.pop(session_id, None)
        conn.sendall(safe_header_bytes("HTTP/1.1 302 Found", [
            ("Location", "/"),
            ("Set-Cookie", "session=; Max-Age=0"),
        ]))
        return

    conn.sendall(safe_header_bytes("HTTP/1.1 404 Not Found", [("Content-Type", "text/plain")]) + b"Not Found")


def main():
    init_db()
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
    server.bind((HOST, PORT))
    server.listen(5)
    print(f"SecureBank demo listening on {HOST}:{PORT}")

    while True:
        conn, _ = server.accept()
        try:
            handle(conn)
        except Exception as e:
            print("error:", e)
        finally:
            conn.close()


if __name__ == "__main__":
    main()
