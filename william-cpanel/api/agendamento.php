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
    $required_fields = ['nome', 'email', 'telefone', 'cep', 'endereco', 'tipo_servico', 'data_preferida', 'aceite'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            echo json_encode(['success' => false, 'message' => "Campo '$field' é obrigatório"]);
            exit;
        }
    }

    // Sanitize input data
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $cep = trim($_POST['cep']);
    $endereco = trim($_POST['endereco']);
    $tipo_servico = trim($_POST['tipo_servico']);
    $frequencia = trim($_POST['frequencia'] ?? 'unica');
    $data_preferida = trim($_POST['data_preferida']);
    $horario_preferido = trim($_POST['horario_preferido'] ?? 'manha');
    $area_m2 = !empty($_POST['area_m2']) ? intval($_POST['area_m2']) : null;
    $observacoes = trim($_POST['observacoes'] ?? '');

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'E-mail inválido']);
        exit;
    }

    // Validate date (must be future date)
    $selected_date = new DateTime($data_preferida);
    $today = new DateTime();
    if ($selected_date < $today) {
        echo json_encode(['success' => false, 'message' => 'Data deve ser no futuro']);
        exit;
    }

    $pdo = getDBConnection();
    
    // Check if client exists, if not create one
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE email = ?");
    $stmt->execute([$email]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($cliente) {
        $cliente_id = $cliente['id'];
        // Update client information
        $stmt = $pdo->prepare("
            UPDATE clientes 
            SET nome = ?, telefone = ?, cep = ?, endereco = ?, updated_at = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$nome, $telefone, $cep, $endereco, $cliente_id]);
    } else {
        // Create new client
        $stmt = $pdo->prepare("
            INSERT INTO clientes (nome, email, telefone, cep, endereco, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$nome, $email, $telefone, $cep, $endereco]);
        $cliente_id = $pdo->lastInsertId();
    }
    
    // Insert booking
    $stmt = $pdo->prepare("
        INSERT INTO agendamentos (
            cliente_id, tipo_servico, frequencia, data_preferida, 
            horario_preferido, area_m2, observacoes, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pendente', NOW())
    ");
    
    $stmt->execute([
        $cliente_id, $tipo_servico, $frequencia, $data_preferida,
        $horario_preferido, $area_m2, $observacoes
    ]);
    
    $agendamento_id = $pdo->lastInsertId();
    
    // Send email notifications
    $email_sent = sendBookingEmail(
        $nome, $email, $telefone, $tipo_servico, $frequencia,
        $data_preferida, $horario_preferido, $endereco, $observacoes, $agendamento_id
    );
    
    if ($email_sent) {
        echo json_encode(['success' => true, 'message' => 'Agendamento realizado com sucesso!']);
    } else {
        echo json_encode(['success' => true, 'message' => 'Agendamento salvo, mas não foi possível enviar por e-mail.']);
    }
    
} catch (PDOException $e) {
    error_log('Database error in agendamento.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar agendamento. Tente novamente.']);
} catch (Exception $e) {
    error_log('General error in agendamento.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno. Tente novamente.']);
}
?>
