<?php
namespace App\Helpers;

class Sanitize {
    public static function input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    public static function escape($data) {
        global $conn;
        if(isset($conn)) {
            return mysqli_real_escape_string($conn, $data);
        }
        return $data;
    }

    public static function output($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}