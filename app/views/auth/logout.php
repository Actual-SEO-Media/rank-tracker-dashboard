<?php
require_once 'vendor/autoload.php';

use App\Configs\Session;

$session = Session::getInstance();
$session->logout();

header('Location: index.php?action=login');
exit; 