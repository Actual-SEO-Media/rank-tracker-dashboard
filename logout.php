<?php
require_once 'vendor/autoload.php';

use App\Configs\Session;

$session = Session::getInstance();
$session->clear();

header('Location: /login.php');
exit; 