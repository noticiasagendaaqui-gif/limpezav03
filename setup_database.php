
<?php
/**
 * Database Setup Script for LimpaBrasil
 * Run this file once to set up the database
 */

require_once 'config/database.php';

echo "<h1>LimpaBrasil - Configuração do Banco de Dados</h1>";

try {
    echo "<p>Testando conexão com o banco de dados...</p>";
    
    if (testDBConnection()) {
        echo "<p style='color: green;'>✓ Conexão com banco de dados estabelecida com sucesso!</p>";
        
        echo "<p>Inicializando tabelas...</p>";
        
        if (initializeDatabase()) {
            echo "<p style='color: green;'>✓ Banco de dados inicializado com sucesso!</p>";
            echo "<p>Tabelas criadas:</p>";
            echo "<ul>";
            echo "<li>clientes</li>";
            echo "<li>funcionarios</li>";
            echo "<li>agendamentos</li>";
            echo "<li>contatos</li>";
            echo "</ul>";
            
            echo "<p>Dados de exemplo inseridos com sucesso!</p>";
            echo "<p><strong>O sistema está pronto para uso!</strong></p>";
            echo "<p><a href='admin/login.php'>Acessar área administrativa</a></p>";
            
        } else {
            echo "<p style='color: red;'>✗ Erro ao inicializar banco de dados</p>";
        }
        
    } else {
        echo "<p style='color: red;'>✗ Erro de conexão com banco de dados</p>";
        echo "<p>Verifique se o PostgreSQL está configurado no Replit.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
