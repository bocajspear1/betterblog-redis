<?php
$DB_AVAILABLE = false;
$mysqli = new mysqli($_CONFIG->database_host, $_CONFIG->database_user, $_CONFIG->database_password, $_CONFIG->database_name);

if (!$mysqli->connect_errno) {
    $DB_AVAILABLE = true;
}
?>