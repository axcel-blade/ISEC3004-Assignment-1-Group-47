<?php
const HOST = "0.0.0.0";
const PORT = 8080;

const DB_USERNAME = "admin";
const DB_PASSWORD = "admin";
const DB_NAME = "bankDB";

function db_server(): string
{
    return getenv("DB_SERVER") ?: "localhost";
}

function connect_db(): mysqli
{
    mysqli_report(MYSQLI_REPORT_OFF);
    for ($attempt = 0; $attempt < 30; $attempt++) {
        $conn = @mysqli_connect(db_server(), DB_USERNAME, DB_PASSWORD, DB_NAME);
        if ($conn instanceof mysqli) {
            return $conn;
        }
        echo "waiting for database... (" . mysqli_connect_error() . ")\n";
        sleep(2);
    }
    fwrite(STDERR, "could not connect to database\n");
    exit(1);
}
