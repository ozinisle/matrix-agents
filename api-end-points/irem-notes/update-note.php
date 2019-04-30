<?php
//DEV TEMP
//NOTE - change the header to production url in production
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Authorization, Origin, X-Requested-With, Content-Type, Accept");
header("Content-Type: application/json");

include('../../vendor/autoload.php');



use MatrixAgentsAPI\Modules\IRemNotes\IRemNotesTransactions;

// session.use_strict_mode= 1;
// session.cookie_secure = 1;
// session.use_only_cookies = 1;
// session.cookie_httponly = 1;
// session.hash_function = 1;
// session.hash_bits_per_character=6;

session_start();
session_regenerate_id();

$iremNotesTransactions = new IRemNotesTransactions();
echo $iremNotesTransactions->updateNote();
