<?php
/**
 * Application Model
 * Sistem Permohonan Keluar
 */

class ApplicationModel {
    private $pdo;
    
    // Define application statuses
    const STATUS_PENDING = 'pending';
    const STATUS_KETUA_APPROVED = 'ketua_approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_APPROVED = 'approved';
    
    /**
     * Constructor
     * 
     * @param PDO $pdo PDO database connection
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Create a new leave application
     * 
     * @param array $data Application data
     * @return int|bool New application ID or false on failure
     */
    public function createApplication($data) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO applications (
                    user_id, purpose_type, purpose_details, duty_location, 
                    transportation_type, transportation_details, distance_estimate,
                    personal_vehicle_reason, start_date, end_date, exit_time, return_time,
                    form_240km_data, attachment_path
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['user_id'],
                $data['purpose_type'],
                $data['purpose_details'],
                $data['duty_location'],
                $data['transportation_type'],
                $data['transportation_details'] ?? null,
                $data['distance_estimate'] ?? null,
                $data['personal_vehicle_reason'] ?? null,
                $data['start_date'],
                $data['end_date'],
                $data['exit_time'],
                $data['return_time'],
                $data['form_240km_data'] ?? null,
                $data['attachment_path'] ?? null
            ]);
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log('createApplication Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get application by ID
     * 
     * @param int $id Application ID
     * @return array|bool Application data or false if not found
     */
    public function getApplicationById($id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT a.*, 
                       u.name as user_name, u.department, u.position,
                       a2.name as approver_name,
                       k.name as ketua_approver_name,
                       p.name as pengarah_approver_name
                FROM applications a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN users a2 ON a.approver_id = a2.id
                LEFT JOIN users k ON a.ketua_approver_id = k.id
                LEFT JOIN users p ON a.pengarah_approver_id = p.id
                WHERE a.id = ?
            ");
            
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log('getApplicationById Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get applications by user ID
     * 
     * @param int $userId User ID
     * @param int $limit Result limit (0 for no limit)
     * @param int $offset Result offset
     * @return array List of applications
     */
    public function getUserApplications($userId, $limit = 0, $offset = 0) {
        try {
            $query = "
                SELECT a.*, u.name as user_name
                FROM applications a
                LEFT JOIN users u ON a.user_id = u.id
                WHERE a.user_id = ?
                ORDER BY a.created_at DESC
            ";
            
            // Add limit if specified
            if ($limit > 0) {
                $query .= " LIMIT " . (int)$offset . ", " . (int)$limit;
            }
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getUserApplications Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all applications (for admin, ketua, and pengarah)
     * 
     * @param string $status Filter by status (optional)
     * @param int $limit Result limit (0 for no limit)
     * @param int $offset Result offset
     * @param array $currentUser Current user data (for filtering based on role)
     * @return array List of applications
     */
    public function getAllApplications($status = null, $limit = 0, $offset = 0, $currentUser = null) {
        try {
            $query = "
                SELECT a.*, u.name as user_name, u.department, 
                       a2.name as approver_name,
                       k.name as ketua_approver_name,
                       p.name as pengarah_approver_name
                FROM applications a
                LEFT JOIN users u ON a.user_id = u.id
                LEFT JOIN users a2 ON a.approver_id = a2.id
                LEFT JOIN users k ON a.ketua_approver_id = k.id
                LEFT JOIN users p ON a.pengarah_approver_id = p.id
            ";
            
            $params = [];
            $whereConditions = [];
            
            // Add status filter if specified
            if ($status) {
                $whereConditions[] = "a.status = ?";
                $params[] = $status;
            }
            
            // Add role-based filtering
            if ($currentUser) {
                // Don't show the approver's own applications
                $whereConditions[] = "a.user_id <> ?";
                $params[] = $currentUser['id'];
                
                // Ketua can only see applications from their own department
                if ($currentUser['role'] === 'ketua') {
                    $whereConditions[] = "u.department = ?";
                    $params[] = $currentUser['department'];
                }
            }
            
            // Add WHERE clause if conditions exist
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }
            
            $query .= " ORDER BY a.created_at DESC";
            
            // Add limit if specified
            if ($limit > 0) {
                $query .= " LIMIT " . (int)$offset . ", " . (int)$limit;
            }
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getAllApplications Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update database schema to add new fields for two-level approval workflow
     * 
     * @return bool Success status
     */
    public function updateSchema() {
        try {
            // Check if ketua_approval_status column exists
            $stmt = $this->pdo->query("SHOW COLUMNS FROM applications LIKE 'ketua_approval_status'");
            if ($stmt->rowCount() == 0) {
                // Add new columns for two-level approval workflow
                $this->pdo->exec("
                    ALTER TABLE applications 
                    ADD COLUMN ketua_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    ADD COLUMN ketua_approver_id INT NULL,
                    ADD COLUMN ketua_approval_date DATETIME NULL,
                    ADD COLUMN ketua_remarks TEXT NULL,
                    ADD COLUMN pengarah_approval_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    ADD COLUMN pengarah_approver_id INT NULL, 
                    ADD COLUMN pengarah_approval_date DATETIME NULL,
                    ADD COLUMN pengarah_remarks TEXT NULL
                ");
            }
            
            // Check if form_240km_data column exists
            $form240kmExists = false;
            $stmt = $this->pdo->query("SHOW COLUMNS FROM applications LIKE 'form_240km_data'");
            if ($stmt->rowCount() > 0) {
                $form240kmExists = true;
            }
            
            // Add form_240km_data column if it doesn't exist
            if (!$form240kmExists) {
                $this->pdo->exec("ALTER TABLE applications ADD COLUMN form_240km_data TEXT AFTER pdf_path");
            }
            
            // Check if attachment_path column exists
            $attachmentPathExists = false;
            $stmt = $this->pdo->query("SHOW COLUMNS FROM applications LIKE 'attachment_path'");
            if ($stmt->rowCount() > 0) {
                $attachmentPathExists = true;
            }
            
            // Add attachment_path column if it doesn't exist
            if (!$attachmentPathExists) {
                $this->pdo->exec("ALTER TABLE applications ADD COLUMN attachment_path VARCHAR(255) DEFAULT NULL AFTER form_240km_data");
            }
            
            return true;
        } catch (PDOException $e) {
            error_log('Schema update error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update ketua approval status
     * 
     * @param int $id Application ID
     * @param string $status New status (approved/rejected)
     * @param int $approverId Approver user ID
     * @param string $remarks Remarks (optional)
     * @return bool Success status
     */
    public function updateKetuaStatus($id, $status, $approverId, $remarks = null) {
        try {
            $query = "
                UPDATE applications 
                SET ketua_approval_status = ?, 
                    ketua_approver_id = ?, 
                    ketua_approval_date = NOW(),
                    ketua_remarks = ?,
                    status = ?
                WHERE id = ?
            ";
            
            // If ketua approves, set status to ketua_approved
            // If ketua rejects, set status to rejected
            $appStatus = ($status === 'approved') ? self::STATUS_KETUA_APPROVED : self::STATUS_REJECTED;
            
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$status, $approverId, $remarks, $appStatus, $id]);
        } catch (PDOException $e) {
            error_log('updateKetuaStatus Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update pengarah approval status
     * 
     * @param int $id Application ID
     * @param string $status New status (approved/rejected)
     * @param int $approverId Approver user ID
     * @param string $remarks Remarks (optional)
     * @return bool Success status
     */
    public function updatePengarahStatus($id, $status, $approverId, $remarks = null) {
        try {
            $query = "
                UPDATE applications 
                SET pengarah_approval_status = ?, 
                    pengarah_approver_id = ?, 
                    pengarah_approval_date = NOW(),
                    pengarah_remarks = ?,
                    status = ?
                WHERE id = ?
            ";
            
            // If pengarah approves and ketua has approved, set status to approved
            // If pengarah rejects, set status to rejected
            $appStatus = ($status === 'approved') ? self::STATUS_APPROVED : self::STATUS_REJECTED;
            
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$status, $approverId, $remarks, $appStatus, $id]);
        } catch (PDOException $e) {
            error_log('updatePengarahStatus Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get detailed approval status information
     * 
     * @param array $application Application data
     * @return array Approval statuses for each level
     */
    public function getApprovalStatusDetails($application) {
        // Default values
        $result = [
            'ketua' => [
                'status' => 'pending',
                'label' => 'Menunggu Kelulusan',
                'class' => 'is-warning',
                'approver_name' => null,
                'approval_date' => null,
                'remarks' => null
            ],
            'pengarah' => [
                'status' => 'pending', 
                'label' => 'Menunggu Kelulusan',
                'class' => 'is-warning',
                'approver_name' => null,
                'approval_date' => null,
                'remarks' => null
            ]
        ];
        
        // Ketua status
        if (isset($application['ketua_approval_status'])) {
            $result['ketua']['status'] = $application['ketua_approval_status'];
            
            if ($application['ketua_approval_status'] === 'approved') {
                $result['ketua']['label'] = 'Diluluskan';
                $result['ketua']['class'] = 'is-success';
            } else if ($application['ketua_approval_status'] === 'rejected') {
                $result['ketua']['label'] = 'Ditolak';
                $result['ketua']['class'] = 'is-danger';
            }
            
            $result['ketua']['approver_name'] = $application['ketua_approver_name'] ?? null;
            $result['ketua']['approval_date'] = $application['ketua_approval_date'] ?? null;
            $result['ketua']['remarks'] = $application['ketua_remarks'] ?? null;
        }
        
        // Pengarah status
        if (isset($application['pengarah_approval_status'])) {
            $result['pengarah']['status'] = $application['pengarah_approval_status'];
            
            if ($application['pengarah_approval_status'] === 'approved') {
                $result['pengarah']['label'] = 'Diluluskan';
                $result['pengarah']['class'] = 'is-success';
            } else if ($application['pengarah_approval_status'] === 'rejected') {
                $result['pengarah']['label'] = 'Ditolak';
                $result['pengarah']['class'] = 'is-danger';
            }
            
            $result['pengarah']['approver_name'] = $application['pengarah_approver_name'] ?? null;
            $result['pengarah']['approval_date'] = $application['pengarah_approval_date'] ?? null;
            $result['pengarah']['remarks'] = $application['pengarah_remarks'] ?? null;
        }
        
        return $result;
    }
    
    /**
     * Update application status (legacy method, kept for compatibility)
     * 
     * @param int $id Application ID
     * @param string $status New status
     * @param int $approverId Approver user ID
     * @param string $remarks Remarks (optional)
     * @return bool Success status
     */
    public function updateStatus($id, $status, $approverId, $remarks = null) {
        try {
            $query = "
                UPDATE applications 
                SET status = ?, approver_id = ?, remarks = ?
                WHERE id = ?
            ";
            
            $stmt = $this->pdo->prepare($query);
            return $stmt->execute([$status, $approverId, $remarks, $id]);
        } catch (PDOException $e) {
            error_log('updateStatus Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Count applications by status
     * 
     * @param int|null $userId User ID or null for all users
     * @param array|null $currentUser Current user data (for role-based filtering)
     * @return array Counts by status
     */
    public function countByStatus($userId = null, $currentUser = null) {
        try {
            $query = "
                SELECT a.status, COUNT(*) as count
                FROM applications a
                LEFT JOIN users u ON a.user_id = u.id
            ";
            
            $params = [];
            $whereConditions = [];
            
            // Add user filter if specified
            if ($userId) {
                $whereConditions[] = "a.user_id = ?";
                $params[] = $userId;
            } else if ($currentUser) {
                // Don't count the approver's own applications
                $whereConditions[] = "a.user_id <> ?";
                $params[] = $currentUser['id'];
                
                // Ketua can only see applications from their own department
                if ($currentUser['role'] === 'ketua') {
                    $whereConditions[] = "u.department = ?";
                    $params[] = $currentUser['department'];
                }
            }
            
            // Add WHERE clause if conditions exist
            if (!empty($whereConditions)) {
                $query .= " WHERE " . implode(' AND ', $whereConditions);
            }
            
            $query .= " GROUP BY a.status";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll();
            
            // Format results as associative array
            $counts = [
                'pending' => 0,
                'ketua_approved' => 0,
                'approved' => 0,
                'rejected' => 0,
                'total' => 0
            ];
            
            foreach ($results as $row) {
                $counts[$row['status']] = (int)$row['count'];
                $counts['total'] += (int)$row['count'];
            }
            
            return $counts;
        } catch (PDOException $e) {
            error_log('countByStatus Error: ' . $e->getMessage());
            return [
                'pending' => 0,
                'ketua_approved' => 0,
                'approved' => 0,
                'rejected' => 0,
                'total' => 0
            ];
        }
    }
    
    /**
     * Update PDF path for an application
     * 
     * @param int $id Application ID
     * @param string $pdfPath Path to the PDF file
     * @return bool Success status
     */
    public function updatePDFPath($id, $pdfPath) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE applications 
                SET pdf_path = ?
                WHERE id = ?
            ");
            
            return $stmt->execute([$pdfPath, $id]);
        } catch (PDOException $e) {
            error_log('Update PDF Path Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update attachment path for an application
     * 
     * @param int $id Application ID
     * @param string $attachmentPath Path to the attachment file
     * @return bool Success status
     */
    public function updateAttachmentPath($id, $attachmentPath) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE applications 
                SET attachment_path = ?
                WHERE id = ?
            ");
            
            return $stmt->execute([$attachmentPath, $id]);
        } catch (PDOException $e) {
            error_log('Update Attachment Path Error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get recent applications
     * 
     * @param int|null $userId User ID or null for all users
     * @param int $limit Maximum number of applications to return
     * @return array Recent applications
     */
    public function getRecentApplications($userId = null, $limit = 5) {
        try {
            $query = "
                SELECT a.*, u.name as user_name
                FROM applications a
                LEFT JOIN users u ON a.user_id = u.id
            ";
            
            $params = [];
            
            // Add user filter if specified
            if ($userId) {
                $query .= " WHERE a.user_id = ?";
                $params[] = $userId;
            }
            
            $query .= " 
                ORDER BY a.created_at DESC
                LIMIT ?
            ";
            $params[] = $limit;
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('getRecentApplications Error: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete an application
     * 
     * @param int $id Application ID
     * @return bool Success status
     */
    public function deleteApplication($id) {
        try {
            // Get application details to delete any associated files
            $application = $this->getApplicationById($id);
            
            if ($application) {
                // Delete PDF file if exists
                if (!empty($application['pdf_path'])) {
                    $pdfPath = dirname(dirname(__DIR__)) . '/' . $application['pdf_path'];
                    if (file_exists($pdfPath)) {
                        unlink($pdfPath);
                    }
                }
                
                // Delete attachment if exists
                if (!empty($application['attachment_path'])) {
                    $attachmentPath = dirname(dirname(__DIR__)) . '/' . $application['attachment_path'];
                    if (file_exists($attachmentPath)) {
                        unlink($attachmentPath);
                    }
                }
                
                // Delete the application record
                $stmt = $this->pdo->prepare("DELETE FROM applications WHERE id = ?");
                return $stmt->execute([$id]);
            }
            
            return false;
        } catch (PDOException $e) {
            error_log('deleteApplication Error: ' . $e->getMessage());
            return false;
        }
    }
} 