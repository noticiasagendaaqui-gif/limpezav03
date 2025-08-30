
<?php
/**
 * Database Configuration for LimpaBrasil - cPanel HostGator
 */

// Database configuration for cPanel HostGator
define('DB_HOST', 'localhost');
define('DB_NAME', 'agend700_limpeza01');
define('DB_USER', 'agend700_limpeza01');
define('DB_PASS', '}02vd%R2_t;L');
define('DB_PORT', 3306);
define('DB_TYPE', 'mysql');
define('DB_CHARSET', 'utf8mb4');

/**
 * Get database connection using PDO
 * @return PDO Database connection
 * @throws PDOException If connection fails
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw new PDOException('Erro de conexão com o banco de dados');
        }
    }
    
    return $pdo;
}

/**
 * Test database connection
 * @return bool True if connection successful
 */
function testDBConnection() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->query('SELECT 1');
        return $stmt !== false;
    } catch (Exception $e) {
        error_log('Database test failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Execute a prepared statement safely
 * @param string $sql SQL query with placeholders
 * @param array $params Parameters for the query
 * @return PDOStatement|false
 */
function executeQuery($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log('Query execution error: ' . $e->getMessage() . ' SQL: ' . $sql);
        return false;
    }
}

/**
 * Get last inserted ID
 * @return string Last insert ID
 */
function getLastInsertId() {
    try {
        $pdo = getDBConnection();
        return $pdo->lastInsertId();
    } catch (Exception $e) {
        error_log('Get last insert ID error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Begin database transaction
 * @return bool True on success
 */
function beginTransaction() {
    try {
        $pdo = getDBConnection();
        return $pdo->beginTransaction();
    } catch (Exception $e) {
        error_log('Begin transaction error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Commit database transaction
 * @return bool True on success
 */
function commitTransaction() {
    try {
        $pdo = getDBConnection();
        return $pdo->commit();
    } catch (Exception $e) {
        error_log('Commit transaction error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Rollback database transaction
 * @return bool True on success
 */
function rollbackTransaction() {
    try {
        $pdo = getDBConnection();
        return $pdo->rollBack();
    } catch (Exception $e) {
        error_log('Rollback transaction error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Sanitize input for database
 * @param string $input Input string to sanitize
 * @return string Sanitized string
 */
function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool True if valid
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Generate secure random token
 * @param int $length Token length
 * @return string Random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Hash password securely
 * @param string $password Plain text password
 * @return string Hashed password
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

/**
 * Verify password against hash
 * @param string $password Plain text password
 * @param string $hash Hashed password
 * @return bool True if password matches
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Production settings
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(0);

// Set timezone
date_default_timezone_set('America/Sao_Paulo');
?>
