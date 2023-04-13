<?php
$DB_AVAILABLE = false;
if ($_CONFIG->database_host != 'DISABLED') {
    $redis = new Redis(); 
    $redis->connect($_CONFIG->database_host, 6379); 
    $DB_AVAILABLE = true;
}

?>