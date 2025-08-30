
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config/database.php';

$method = $_SERVER['REQUEST_METHOD'];
$response = ['success' => false, 'message' => '', 'data' => null];

try {
    $pdo = getDBConnection();
    
    switch ($method) {
        case 'GET':
            // Listar funcionários ou buscar por ID
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM funcionarios WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $funcionario = $stmt->fetch();
                
                if ($funcionario) {
                    $response['success'] = true;
                    $response['data'] = $funcionario;
                } else {
                    $response['message'] = 'Funcionário não encontrado';
                }
            } else {
                // Listar todos com filtros opcionais
                $search = $_GET['search'] ?? '';
                $status = $_GET['status'] ?? '';
                $cargo = $_GET['cargo'] ?? '';
                
                $sql = "SELECT * FROM funcionarios WHERE 1=1";
                $params = [];
                
                if ($search) {
                    $sql .= " AND (nome LIKE ? OR email LIKE ?)";
                    $params[] = "%$search%";
                    $params[] = "%$search%";
                }
                
                if ($status) {
                    $sql .= " AND status = ?";
                    $params[] = $status;
                }
                
                if ($cargo) {
                    $sql .= " AND cargo = ?";
                    $params[] = $cargo;
                }
                
                $sql .= " ORDER BY nome ASC";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $funcionarios = $stmt->fetchAll();
                
                $response['success'] = true;
                $response['data'] = $funcionarios;
            }
            break;
            
        case 'POST':
            // Criar novo funcionário
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                $input = $_POST;
            }
            
            $required = ['nome', 'email', 'telefone', 'cargo', 'salario', 'data_admissao'];
            foreach ($required as $field) {
                if (empty($input[$field])) {
                    $response['message'] = "Campo obrigatório: $field";
                    break;
                }
            }
            
            if (empty($response['message'])) {
                $stmt = $pdo->prepare("INSERT INTO funcionarios (nome, email, telefone, cargo, salario, data_admissao, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $input['nome'],
                    $input['email'],
                    $input['telefone'],
                    $input['cargo'],
                    $input['salario'],
                    $input['data_admissao'],
                    $input['status'] ?? 'ativo'
                ]);
                
                $response['success'] = true;
                $response['message'] = 'Funcionário criado com sucesso';
                $response['data'] = ['id' => $pdo->lastInsertId()];
            }
            break;
            
        case 'PUT':
            // Atualizar funcionário
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                $response['message'] = 'ID do funcionário é obrigatório';
                break;
            }
            
            $stmt = $pdo->prepare("UPDATE funcionarios SET nome=?, email=?, telefone=?, cargo=?, salario=?, data_admissao=?, status=? WHERE id=?");
            $stmt->execute([
                $input['nome'],
                $input['email'],
                $input['telefone'],
                $input['cargo'],
                $input['salario'],
                $input['data_admissao'],
                $input['status'],
                $input['id']
            ]);
            
            $response['success'] = true;
            $response['message'] = 'Funcionário atualizado com sucesso';
            break;
            
        case 'DELETE':
            // Deletar funcionário
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                $response['message'] = 'ID do funcionário é obrigatório';
                break;
            }
            
            $stmt = $pdo->prepare("DELETE FROM funcionarios WHERE id = ?");
            $stmt->execute([$input['id']]);
            
            $response['success'] = true;
            $response['message'] = 'Funcionário excluído com sucesso';
            break;
            
        default:
            $response['message'] = 'Método não permitido';
            break;
    }
    
} catch (PDOException $e) {
    $response['message'] = 'Erro no banco de dados: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Erro interno: ' . $e->getMessage();
}

echo json_encode($response);
?>
