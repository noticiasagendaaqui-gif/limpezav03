
<?php
/**
 * Teste de Conexão com Banco de Dados - cPanel HostGator
 * Execute este arquivo para verificar se a conexão está funcionando
 */

require_once 'config/database.php';

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Teste de Conexão - LimpaBrasil</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }";
echo ".success { color: green; background: #d4edda; padding: 10px; border: 1px solid green; border-radius: 5px; margin: 10px 0; }";
echo ".error { color: red; background: #f8d7da; padding: 10px; border: 1px solid red; border-radius: 5px; margin: 10px 0; }";
echo ".info { color: blue; background: #d1ecf1; padding: 10px; border: 1px solid blue; border-radius: 5px; margin: 10px 0; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<h1>🧪 Teste de Conexão - LimpaBrasil</h1>";

echo "<div class='info'>";
echo "<h3>📊 Informações da Configuração:</h3>";
echo "<ul>";
echo "<li><strong>Host:</strong> " . DB_HOST . "</li>";
echo "<li><strong>Banco:</strong> " . DB_NAME . "</li>";
echo "<li><strong>Usuário:</strong> " . DB_USER . "</li>";
echo "<li><strong>Tipo:</strong> " . DB_TYPE . "</li>";
echo "</ul>";
echo "</div>";

try {
    echo "<h3>🔌 Testando Conexão...</h3>";
    
    if (testDBConnection()) {
        echo "<div class='success'>✅ <strong>Conexão estabelecida com sucesso!</strong></div>";
        
        echo "<h3>📋 Verificando Tabelas...</h3>";
        
        $pdo = getDBConnection();
        
        // Verificar se as tabelas existem
        $tables = ['clientes', 'funcionarios', 'agendamentos', 'contatos'];
        $existing_tables = [];
        
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$table]);
            if ($stmt->fetch()) {
                $existing_tables[] = $table;
                echo "<div class='success'>✅ Tabela '$table' encontrada</div>";
            } else {
                echo "<div class='error'>❌ Tabela '$table' não encontrada</div>";
            }
        }
        
        if (count($existing_tables) === count($tables)) {
            echo "<div class='success'>🎉 <strong>Todas as tabelas estão configuradas corretamente!</strong></div>";
            echo "<div class='info'>📝 O sistema está pronto para uso. Você pode deletar este arquivo.</div>";
        } else {
            echo "<div class='error'>⚠️ <strong>Algumas tabelas estão faltando.</strong> Execute o arquivo schema.sql no phpMyAdmin.</div>";
        }
        
        // Testar inserção de dados
        echo "<h3>🧮 Teste de Operações Básicas...</h3>";
        
        if (in_array('clientes', $existing_tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM clientes");
            $result = $stmt->fetch();
            echo "<div class='info'>👥 Total de clientes: " . $result['total'] . "</div>";
        }
        
        if (in_array('funcionarios', $existing_tables)) {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM funcionarios");
            $result = $stmt->fetch();
            echo "<div class='info'>👷 Total de funcionários: " . $result['total'] . "</div>";
        }
        
    } else {
        echo "<div class='error'>❌ <strong>Erro de conexão com o banco de dados</strong></div>";
        echo "<div class='error'>Verifique se:</div>";
        echo "<ul>";
        echo "<li>O banco de dados 'agend700_limpeza01' foi criado</li>";
        echo "<li>O usuário 'agend700_limpeza01' tem permissões corretas</li>";
        echo "<li>A senha está correta</li>";
        echo "</ul>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ <strong>Erro:</strong> " . htmlspecialchars($e->getMessage()) . "</div>";
}

echo "<hr>";
echo "<p><strong>📋 Próximos passos:</strong></p>";
echo "<ol>";
echo "<li>Se a conexão estiver OK, importe o arquivo <code>database/schema.sql</code> no phpMyAdmin</li>";
echo "<li>Acesse <a href='admin/login.php'>admin/login.php</a> para testar o painel administrativo</li>";
echo "<li>Delete este arquivo (<code>test_connection.php</code>) por segurança</li>";
echo "</ol>";

echo "</body>";
echo "</html>";
?>
