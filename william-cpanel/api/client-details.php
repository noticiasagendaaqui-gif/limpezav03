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
    echo json_encode(['success' => false, 'message' => 'ID do cliente inválido']);
    exit;
}

try {
    $pdo = getDBConnection();
    $client_id = intval($_GET['id']);
    
    // Get client details
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$client) {
        echo json_encode(['success' => false, 'message' => 'Cliente não encontrado']);
        exit;
    }
    
    // Get client's bookings
    $stmt = $pdo->prepare("
        SELECT a.*, s.nome as servico_nome 
        FROM agendamentos a 
        LEFT JOIN servicos s ON s.slug = a.tipo_servico
        WHERE a.cliente_id = ? 
        ORDER BY a.data_preferida DESC
    ");
    $stmt->execute([$client_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format dates for display
    foreach ($bookings as &$booking) {
        $booking['data_preferida_formatted'] = date('d/m/Y', strtotime($booking['data_preferida']));
        $booking['created_at_formatted'] = date('d/m/Y H:i', strtotime($booking['created_at']));
        $booking['horario_preferido_formatted'] = ucfirst(str_replace('_', ' ', $booking['horario_preferido']));
        $booking['tipo_servico_formatted'] = ucfirst(str_replace('-', ' ', $booking['tipo_servico']));
    }
    
    echo json_encode([
        'success' => true,
        'client' => $client,
        'bookings' => $bookings
    ]);
    
} catch (PDOException $e) {
    error_log('Database error in client-details.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro ao buscar detalhes do cliente']);
} catch (Exception $e) {
    error_log('General error in client-details.php: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno']);
}
?>
