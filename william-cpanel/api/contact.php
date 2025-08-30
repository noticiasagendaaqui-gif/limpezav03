<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';
require_once '../config/email.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

try {
    // Validate required fields
    $required_fields = ['nome', 'email', 'assunto', 'mensagem', 'aceite'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            echo json_encode(['success' => false, 'message' => "Campo '$field' é obrigatório"]);
            exit;
        }
    }

    // Sanitize input data
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone'] ?? '');
    $assunto = trim($_POST['assunto']);
    $endereco = trim($_POST['endereco'] ?? '');
    $mensagem = trim($_POST['mensagem']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'E-mail inválido']);
        exit;
    }

    // Insert into database
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("
        INSERT INTO contatos (nome, email, telefone, assunto, endereco, mensagem, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([$nome, $email, $telefone, $assunto, $endereco, $mensagem]);
    
    // Send email notification
    $email_sent = sendContactEmail($nome, $email, $telefone, $assunto, $endereco, $mensagem);
    
    if ($email_sent) {
        echo json_encode(['success' => true, 'message' => 'Mensagem enviada com sucesso!']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Mensagem salva, mas não foi possível enviar por e-mail.']);
    }
    
} catch (PDOException $e) {
    error_log('Database error in contact.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar mensagem. Tente novamente.']);
} catch (Exception $e) {
    error_log('General error in contact.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno. Tente novamente.']);
}
?>
