<?php
const STYLESHEET = <<<CSS
* { box-sizing: border-box; }
body {
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    background: #f0f2f5;
    color: #1c1e21;
    margin: 0;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}
.card {
    background: #fff;
    width: 100%;
    max-width: 420px;
    padding: 32px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}
h2 {
    margin: 0 0 4px;
    color: #0b5cff;
}
h3 {
    margin: 28px 0 12px;
    font-size: 1.05rem;
    border-top: 1px solid #e4e6eb;
    padding-top: 20px;
}
.balance {
    font-size: 1.4rem;
    font-weight: 600;
    margin: 4px 0 0;
}
label {
    display: block;
    font-size: 0.85rem;
    font-weight: 600;
    margin: 14px 0 4px;
    color: #606770;
}
input[type="text"],
input[type="password"] {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #ccd0d5;
    border-radius: 8px;
    font-size: 1rem;
}
input[type="text"]:focus,
input[type="password"]:focus {
    outline: none;
    border-color: #0b5cff;
    box-shadow: 0 0 0 3px rgba(11, 92, 255, 0.15);
}
input[type="submit"] {
    width: 100%;
    margin-top: 20px;
    padding: 11px;
    background: #0b5cff;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
}
input[type="submit"]:hover { background: #0a4fd8; }
.error { color: #d93025; min-height: 1.1em; margin: 8px 0 0; }
a { color: #0b5cff; }
.logout { display: inline-block; margin-top: 20px; }
CSS;

const LOGIN_PAGE = '<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>SecureBank Login</title><link rel="stylesheet" href="/style.css"></head>
<body>
<div class="card">
<h2>SecureBank</h2>
<p class="error">%s</p>
<form action="/" method="post">
    <label>Username</label><input type="text" name="username">
    <label>Password</label><input type="password" name="password">
    <input type="submit" value="Login">
</form>
</div>
</body></html>';

const DASHBOARD_PAGE = '<!DOCTYPE html>
<html><head><meta charset="UTF-8"><title>SecureBank Dashboard</title><link rel="stylesheet" href="/style.css"></head>
<body>
<div class="card">
<h2>Welcome, %s</h2>
<p class="balance">Current balance: $%.2f</p>
<h3>Transfer Funds</h3>
<form action="/transfer" method="post">
    <label>Recipient account ID</label><input type="text" name="recipient_id">
    <label>Amount</label><input type="text" name="amount">
    <input type="hidden" name="next" value="/dashboard">
    <input type="submit" value="Transfer">
</form>
<a class="logout" href="/logout">Log out</a>
</div>
</body></html>';
