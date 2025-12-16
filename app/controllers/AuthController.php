<?php
namespace App\Controllers;

require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/sanitize.php';

use App\Models\User;
use App\Helpers\Sanitize;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register($data) {
        $errors = [];
        
        // Validate inputs
        if (empty($data['username'])) {
            $errors[] = "Username is required";
        } elseif (strlen($data['username']) < 3) {
            $errors[] = "Username must be at least 3 characters";
        } elseif ($this->userModel->checkUsernameExists($data['username'])) {
            $errors[] = "Username already exists";
        }

        if (empty($data['email'])) {
            $errors[] = "Email is required";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } elseif ($this->userModel->checkEmailExists($data['email'])) {
            $errors[] = "Email already registered";
        }

        if (empty($data['password'])) {
            $errors[] = "Password is required";
        } elseif (strlen($data['password']) < 6) {
            $errors[] = "Password must be at least 6 characters";
        }

        if ($data['password'] !== $data['confirm_password']) {
            $errors[] = "Passwords do not match";
        }

        if (empty($data['full_name'])) {
            $errors[] = "Full name is required";
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Sanitize data
        $username = Sanitize::input($data['username']);
        $email = Sanitize::input($data['email']);
        $password = Sanitize::input($data['password']);
        $full_name = Sanitize::input($data['full_name']);
        $phone = Sanitize::input($data['phone'] ?? '');

        // Create user
        $this->userModel->username = $username;
        $this->userModel->email = $email;
        $this->userModel->password = $password;
        $this->userModel->full_name = $full_name;
        $this->userModel->phone = $phone;
        $this->userModel->role = 'user';

        if ($this->userModel->register()) {
            return ['success' => true, 'message' => 'Registration successful! Please login.'];
        } else {
            return ['success' => false, 'errors' => ['Registration failed. Please try again.']];
        }
    }

    public function login($username, $password) {
        $username = Sanitize::input($username);
        $password = Sanitize::input($password);

        $user = $this->userModel->login($username, $password);

        if ($user) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            return ['success' => true, 'user' => $user];
        } else {
            return ['success' => false, 'errors' => ['Invalid username/email or password']];
        }
    }

    public function logout() {
        session_destroy();
        return ['success' => true];
    }
}