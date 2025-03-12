<?php
namespace App\Services;

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Models\User;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Exception;

class AuthService {
    private $auth;
    
    
}