<?php
// db.php — shared DB connection
$DB_HOST = "localhost";
$DB_NAME = "dbjtwqvzsj0p0e";
$DB_USER = "uei4bkjtcem6s";
$DB_PASS = "wmhalmspfjgz";
 
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
  die("DB Connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
?>
 
