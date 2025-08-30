<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID do agendamento inválido']);
    exit;
}

try {
    $pdo = getDBConnection();
    $booking_id = intval($_GET['id']);
    
    // Get booking details with client information
    $stmt = $pdo->prepare("
        SELECT 
            a.*,
            c.nome as cliente_nome,
            c.email as cliente_email,
            c.telefone as cliente_telefone,
            c.endereco,
            c.cep,
            s.nome as servico_nome,
            s.preco_base as servico_preco_base
        FROM agendamentos a
        LEFT JOIN clientes c ON a.cliente_id = c.id
        LEFT JOIN servicos s ON s.slug = a.tipo_servico
        WHERE a.id = ?
    ");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Agendamento não encontrado']);
        exit;
    }
    
    // Format data for display
    $booking['data_preferida_formatted'] = date('d/m/Y', strtotime($booking['data_preferida']));
    $booking['created_at_formatted'] = date('d/m/Y H:i', strtotime($booking['created_at']));
    $booking['updated_at_formatted'] = date('d/m/Y H:i', strtotime($booking['updated_at']));
    $booking['horario_preferido_formatted'] = ucfirst(str_replace('_', ' ', $booking['horario_preferido']));
    $booking['tipo_servico_formatted'] = ucfirst(str_replace('-', ' ', $booking['tipo_servico']));
    $booking['frequencia_formatted'] = ucfirst($booking['frequencia']);
    $booking['status_formatted'] = ucfirst($booking['status']);
    
    if ($booking['data_execucao']) {
        $booking['data_execucao_formatted'] = date('d/m/Y H:i', strtotime($booking['data_execucao']));
    }
    
    // Get assigned employees if any
    $stmt = $pdo->prepare("
        SELECT f.nome, f.cargo, af.funcao
        FROM agendamento_funcionarios af
        JOIN funcionarios f ON af.funcionario_id = f.id
        WHERE af.agendamento_id = ?
    ");
    $stmt->execute([$booking_id]);
    $funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'booking' => $booking,
        'funcionarios' => $funcionarios
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in booking-details.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar detalhes do agendamento']);
} catch (Exception $e) {
    error_log('General error in booking-details.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
?>
