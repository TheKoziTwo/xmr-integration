<?php if(!defined('APP')) exit;
// Establish a database connection
$db = new mysqli(
    $config['database']['hostname'],
    $config['database']['username'],
    $config['database']['password'],
    $config['database']['database']);

if ($db->connect_errno) 
{
    echo "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error; exit;
}