<?php
namespace App\Helpers;

class Auth {
    public static function startSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn() {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: login.php');
            exit();
        }
    }

    public static function redirectIfLoggedIn() {
        if (self::isLoggedIn()) {
            header('Location: dashboard.php');
            exit();
        }
    }

    public static function requireAdmin() {
        self::startSession();
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ../login.php');
            exit();
        }
    }
}