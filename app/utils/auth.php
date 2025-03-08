<?php
/**
 * Authentication utilities
 * Sistem Permohonan Keluar
 */

// Include user model to get role constants
require_once dirname(__FILE__) . '/../models/UserModel.php';

/**
 * Authenticate a user
 * 
 * @param string $username The username
 * @param string $password The password
 * @return array|bool User data or false if authentication fails
 */
function authenticateUser($username, $password) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Remove password from user data
            unset($user['password']);
            return $user;
        }
    } catch (PDOException $e) {
        error_log('Authentication error: ' . $e->getMessage());
    }
    
    return false;
}

/**
 * Register a new user
 * 
 * @param array $userData The user data
 * @return int|bool The new user ID or false if registration fails
 */
function registerUser($userData) {
    global $pdo;
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, name, email, phone, department, position) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        // Hash the password
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $stmt->execute([
            $userData['username'],
            $hashedPassword,
            $userData['name'],
            $userData['email'],
            $userData['phone'] ?? null,
            $userData['department'],
            $userData['position']
        ]);
        
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log('Registration error: ' . $e->getMessage());
    }
    
    return false;
}

/**
 * Check if a user is logged in
 * 
 * @return bool True if the user is logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if the current user is an admin
 * 
 * @return bool True if the user is an admin, false otherwise
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === UserModel::ROLE_ADMIN;
}

/**
 * Check if the current user is Ketua Jabatan/Ketua Unit
 * 
 * @return bool True if the user is Ketua Jabatan/Ketua Unit, false otherwise
 */
function isKetua() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === UserModel::ROLE_KETUA;
}

/**
 * Check if the current user is Pengarah
 * 
 * @return bool True if the user is Pengarah, false otherwise
 */
function isPengarah() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === UserModel::ROLE_PENGARAH;
}

/**
 * Check if the current user is an approver (Ketua or Pengarah)
 * 
 * @return bool True if the user is an approver, false otherwise
 */
function isApprover() {
    return isKetua() || isPengarah();
}

/**
 * Require user to be logged in
 * Redirects to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit;
    }
}

/**
 * Require user to be an admin
 * Redirects to dashboard if not an admin
 */
function requireAdmin() {
    requireLogin();
    
    if (!isAdmin()) {
        header('Location: ' . SITE_URL . '/dashboard.php?error=unauthorized');
        exit;
    }
}

/**
 * Require user to be a Ketua Jabatan/Ketua Unit
 * Redirects to dashboard if not a Ketua
 */
function requireKetua() {
    requireLogin();
    
    if (!isKetua()) {
        header('Location: ' . SITE_URL . '/dashboard.php?error=unauthorized');
        exit;
    }
}

/**
 * Require user to be a Pengarah
 * Redirects to dashboard if not a Pengarah
 */
function requirePengarah() {
    requireLogin();
    
    if (!isPengarah()) {
        header('Location: ' . SITE_URL . '/dashboard.php?error=unauthorized');
        exit;
    }
}

/**
 * Require user to be an approver (Ketua or Pengarah)
 * Redirects to dashboard if not an approver
 */
function requireApprover() {
    requireLogin();
    
    if (!isApprover()) {
        header('Location: ' . SITE_URL . '/dashboard.php?error=unauthorized');
        exit;
    }
}

/**
 * Log out the current user
 */
function logoutUser() {
    session_unset();
    session_destroy();
    header('Location: ' . SITE_URL . '/login.php?logout=1');
    exit;
} 