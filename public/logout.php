<?php
require_once '../app/helpers/auth.php';

use App\Helpers\Auth;

session_start();
session_destroy();
header('Location: login.php');
exit();