
<?php
/**
 * Database Configuration for LimpaBrasil
 * Compatible with Replit PostgreSQL
 */

// Check if we're in Replit environment and use PostgreSQL
if (isset($_ENV['DATABASE_URL'])) {
    // Replit PostgreSQL configuration
    $database_url = $_ENV['DATABASE_URL'];
    $url_parts = parse_url($database_url);
    
    define('DB_HOST', $url_parts['host']);
    define('DB_NAME', ltrim($url_parts['path'], '/'));
    define('DB_USER', $url_parts['user']);
    define('DB_PASS', $url_parts['pass']);
    define('DB_PORT', $url_parts['port'] ?? 5432);
    define('DB_TYPE', 'pgsql');
} else {
    // Local/cPanel MySQL configuration
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'limpabrasil_db');
    define('DB_USER', 'your_db_user');
    define('DB_PASS', 'your_db_password');
    define('DB_PORT', 3306);
    define('DB_TYPE', 'mysql');
}

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
            if (DB_TYPE === 'pgsql') {
                $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
            } else {
                $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            }
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            // Add charset for MySQL only
            if (DB_TYPE === 'mysql') {
                $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES " . DB_CHARSET;
            }
            
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
 * Initialize database tables if they don't exist
 * Call this function on first setup
 */
function initializeDatabase() {
    try {
        $pdo = getDBConnection();
        
        // Create tables for PostgreSQL
        if (DB_TYPE === 'pgsql') {
            // Clientes table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS clientes (
                    id SERIAL PRIMARY KEY,
                    nome VARCHAR(255) NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    telefone VARCHAR(20),
                    cep VARCHAR(10),
                    endereco TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // Funcionarios table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS funcionarios (
                    id SERIAL PRIMARY KEY,
                    nome VARCHAR(255) NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    telefone VARCHAR(20),
                    cargo VARCHAR(100),
                    salario DECIMAL(10,2),
                    data_admissao DATE,
                    status VARCHAR(20) DEFAULT 'ativo',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // Agendamentos table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS agendamentos (
                    id SERIAL PRIMARY KEY,
                    cliente_id INTEGER REFERENCES clientes(id),
                    tipo_servico VARCHAR(50) NOT NULL,
                    frequencia VARCHAR(20) DEFAULT 'unica',
                    data_preferida DATE,
                    horario_preferido VARCHAR(20),
                    observacoes TEXT,
                    status VARCHAR(20) DEFAULT 'pendente',
                    preco DECIMAL(10,2),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // Contatos table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS contatos (
                    id SERIAL PRIMARY KEY,
                    nome VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    telefone VARCHAR(20),
                    assunto VARCHAR(255),
                    endereco TEXT,
                    mensagem TEXT,
                    status VARCHAR(20) DEFAULT 'novo',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            // Insert sample data
            $pdo->exec("
                INSERT INTO clientes (nome, email, telefone, cep, endereco) VALUES 
                ('João Silva', 'joao@email.com', '(11) 99999-1111', '01234-567', 'Rua das Flores, 123 - Centro, São Paulo - SP'),
                ('Maria Santos', 'maria@email.com', '(11) 99999-2222', '02345-678', 'Av. Paulista, 456 - Bela Vista, São Paulo - SP'),
                ('Pedro Oliveira', 'pedro@email.com', '(11) 99999-3333', '03456-789', 'Rua Augusta, 789 - Consolação, São Paulo - SP')
                ON CONFLICT (email) DO NOTHING
            ");
            
            $pdo->exec("
                INSERT INTO funcionarios (nome, email, telefone, cargo, salario, data_admissao, status) VALUES 
                ('Carlos Manager', 'carlos@limpabrasil.com.br', '(11) 97777-7777', 'Gerente', 5000.00, '2024-01-15', 'ativo'),
                ('Ana Supervisor', 'ana@limpabrasil.com.br', '(11) 96666-6666', 'Supervisor', 3500.00, '2024-02-01', 'ativo'),
                ('Pedro Limpeza', 'pedro@limpabrasil.com.br', '(11) 95555-5555', 'Faxineiro', 2200.00, '2024-03-10', 'ativo')
                ON CONFLICT (email) DO NOTHING
            ");
            
        } else {
            // Read and execute MySQL schema file for other environments
            $schemaFile = __DIR__ . '/../database/schema.sql';
            if (file_exists($schemaFile)) {
                $schema = file_get_contents($schemaFile);
                $statements = explode(';', $schema);
                
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log('Database initialization error: ' . $e->getMessage());
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

// Environment-specific settings
if (defined('DEVELOPMENT') && DEVELOPMENT) {
    // Development settings
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Production settings
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Set timezone
date_default_timezone_set('America/Sao_Paulo');

// Auto-initialize database if needed
if (isset($_ENV['DATABASE_URL'])) {
    try {
        initializeDatabase();
    } catch (Exception $e) {
        error_log('Auto-initialization failed: ' . $e->getMessage());
    }
}
?>
