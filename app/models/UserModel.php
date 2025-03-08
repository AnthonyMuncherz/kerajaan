<?php
/**
 * User Model
 * Sistem Permohonan Keluar
 */

class UserModel {
    private $pdo;
    
    // Define role constants
    const ROLE_USER = 'user';
    const ROLE_ADMIN = 'admin';
    const ROLE_KETUA = 'ketua';
    const ROLE_PENGARAH = 'pengarah';
    
    /**
     * Constructor
     * 
     * @param PDO $pdo PDO database connection
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Get user by ID
     * 
     * @param int $id User ID
     * @return array|bool User data or false if not found
     */
    public function getUserById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            
            if ($user) {
                unset($user['password']);
                return $user;
            }
        } catch (PDOException $e) {
            error_log('getUserById Error: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Get user by username
     * 
     * @param string $username Username
     * @return array|bool User data or false if not found
     */
    public function getUserByUsername($username) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user) {
                return $user;
            }
        } catch (PDOException $e) {
            error_log('getUserByUsername Error: ' . $e->getMessage());
        }
        
        return false;
    }
    
    /**
     * Update user profile
     * 
     * @param int $id User ID
     * @param array $data User data to update
     * @return bool Success status
     */
    public function updateProfile($id, $data) {
        try {
            $query = "UPDATE users SET 
                      name = ?, 
                      email = ?, 
                      phone = ?,
                      department = ?, 
                      position = ?";
            
            $params = [
                $data['name'],
                $data['email'],
                $data['phone'] ?? null,
                $data['department'],
                $data['position']
            ];
            
            // Add signature path if provided
            if (isset($data['signature_path'])) {
                $query .= ", signature_path = ?";
                $params[] = $data['signature_path'];
            }
            
            $query .= " WHERE id = ?";
            $params[] = $id;
            
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log('updateProfile Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user password
     * 
     * @param int $id User ID
     * @param string $password New password (plaintext)
     * @return bool Success status
     */
    public function updatePassword($id, $password) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            return $stmt->execute([$hashedPassword, $id]);
        } catch (PDOException $e) {
            error_log('updatePassword Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Upload signature image
     * 
     * @param int $userId User ID
     * @param array $file File upload data ($_FILES array)
     * @return string|bool Path to uploaded signature or false on failure
     */
    public function uploadSignature($userId, $file) {
        // Validate file
        if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return false;
        }
        
        // Check file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        // Ensure directory exists
        if (!is_dir(SIGNATURE_PATH)) {
            mkdir(SIGNATURE_PATH, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'signature_' . $userId . '_' . uniqid() . '.' . $extension;
        $targetPath = SIGNATURE_PATH . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            return $filename;
        }
        
        return false;
    }
    
    /**
     * Get all admin users
     * 
     * @return array List of admin users
     */
    public function getAdmins() {
        try {
            $stmt = $this->pdo->prepare("SELECT id, name, email FROM users WHERE role = 'admin'");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getAdmins Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all users
     * 
     * @param int $limit Result limit (0 for no limit)
     * @param int $offset Result offset
     * @param string $search Search term (optional)
     * @param string $sortBy Column to sort by (default: 'name')
     * @param string $sortDir Sort direction ('asc' or 'desc', default: 'asc')
     * @return array List of users
     */
    public function getAllUsers($limit = 0, $offset = 0, $search = '', $sortBy = 'name', $sortDir = 'asc') {
        try {
            $query = "SELECT id, username, name, email, phone, department, position, role FROM users";
            $params = [];
            
            // Add search condition if provided
            if (!empty($search)) {
                $query .= " WHERE name LIKE ? OR username LIKE ? OR email LIKE ? OR department LIKE ?";
                $searchTerm = "%$search%";
                $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
            }
            
            // Validate sort column to prevent SQL injection
            $validColumns = ['id', 'username', 'name', 'email', 'department', 'position', 'role'];
            if (!in_array($sortBy, $validColumns)) {
                $sortBy = 'name'; // Default to name if invalid column
            }
            
            // Validate sort direction
            $sortDir = strtolower($sortDir) === 'desc' ? 'DESC' : 'ASC';
            
            $query .= " ORDER BY $sortBy $sortDir";
            
            // Add limit if specified
            if ($limit > 0) {
                $query .= " LIMIT " . (int)$offset . ", " . (int)$limit;
            }
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getAllUsers Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Count total users
     * 
     * @param string $search Search term (optional)
     * @return int Total number of users
     */
    public function countUsers($search = '') {
        try {
            $query = "SELECT COUNT(*) FROM users";
            $params = [];
            
            // Add search condition if provided
            if (!empty($search)) {
                $query .= " WHERE name LIKE ? OR username LIKE ? OR email LIKE ? OR department LIKE ?";
                $searchTerm = "%$search%";
                $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
            }
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('countUsers Error: ' . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Update user role
     * 
     * @param int $id User ID
     * @param string $role New role
     * @return bool Success status
     */
    public function updateUserRole($id, $role) {
        try {
            // Validate role
            $validRoles = array_keys($this->getUserRoles());
            if (!in_array($role, $validRoles)) {
                return false;
            }
            
            $stmt = $this->pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
            return $stmt->execute([$role, $id]);
        } catch (PDOException $e) {
            error_log('updateUserRole Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update user role and department
     * 
     * @param int $id User ID
     * @param string $role New role
     * @param string $department New department
     * @return bool Success status
     */
    public function updateUserRoleAndDepartment($id, $role, $department) {
        try {
            // Validate role
            $validRoles = array_keys($this->getUserRoles());
            if (!in_array($role, $validRoles)) {
                return false;
            }
            
            $stmt = $this->pdo->prepare("UPDATE users SET role = ?, department = ? WHERE id = ?");
            return $stmt->execute([$role, $department, $id]);
        } catch (PDOException $e) {
            error_log('updateUserRoleAndDepartment Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user roles as array
     * 
     * @return array Available user roles
     */
    public function getUserRoles() {
        return [
            self::ROLE_USER => 'Pengguna',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_KETUA => 'Ketua Jabatan/Ketua Unit',
            self::ROLE_PENGARAH => 'Pengarah'
        ];
    }
    
    /**
     * Check if user is Ketua Jabatan/Ketua Unit
     * 
     * @return boolean True if user is Ketua
     */
    public function isKetua() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === self::ROLE_KETUA;
    }
    
    /**
     * Check if user is Pengarah
     * 
     * @return boolean True if user is Pengarah
     */
    public function isPengarah() {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === self::ROLE_PENGARAH;
    }
} 