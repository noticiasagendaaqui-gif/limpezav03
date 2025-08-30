
<?php
/**
 * Script de instalação para cPanel
 * Execute este arquivo uma vez após fazer upload
 */

require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação LimpaBrasil</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .step { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🧹 LimpaBrasil - Instalação no cPanel</h1>
    
    <?php
    echo "<div class='step'>";
    echo "<h3>Passo 1: Testando conexão com banco de dados</h3>";
    
    try {
        if (testDBConnection()) {
            echo "<p class='success'>✓ Conexão com MySQL estabelecida com sucesso!</p>";
            
            echo "<h3>Passo 2: Criando tabelas do banco de dados</h3>";
            
            // Ler e executar schema SQL
            $schemaFile = 'database/schema.sql';
            if (file_exists($schemaFile)) {
                $schema = file_get_contents($schemaFile);
                $statements = explode(';', $schema);
                
                $pdo = getDBConnection();
                $tablesCreated = 0;
                
                foreach ($statements as $statement) {
                    $statement = trim($statement);
                    if (!empty($statement) && !preg_match('/^(--|\#)/', $statement)) {
                        try {
                            $pdo->exec($statement);
                            if (stripos($statement, 'CREATE TABLE') !== false) {
                                $tablesCreated++;
                            }
                        } catch (PDOException $e) {
                            // Ignora erros de tabelas que já existem
                            if (strpos($e->getMessage(), 'already exists') === false) {
                                echo "<p class='error'>Erro ao executar: " . htmlspecialchars(substr($statement, 0, 50)) . "...</p>";
                            }
                        }
                    }
                }
                
                echo "<p class='success'>✓ Banco de dados configurado com sucesso!</p>";
                echo "<p class='info'>Tabelas criadas: $tabelsCreated</p>";
                
                echo "<div class='step'>";
                echo "<h3>🎉 Instalação Concluída!</h3>";
                echo "<p><strong>O sistema está pronto para uso!</strong></p>";
                echo "<ul>";
                echo "<li><a href='index.html' target='_blank'>📱 Acessar site principal</a></li>";
                echo "<li><a href='admin/' target='_blank'>⚙️ Acessar painel administrativo</a></li>";
                echo "</ul>";
                echo "<p class='info'><strong>IMPORTANTE:</strong> Delete este arquivo (install.php) após a instalação por segurança.</p>";
                echo "</div>";
                
            } else {
                echo "<p class='error'>✗ Arquivo schema.sql não encontrado!</p>";
            }
            
        } else {
            echo "<p class='error'>✗ Erro de conexão com banco de dados</p>";
            echo "<div class='step'>";
            echo "<h3>❌ Verifique as configurações:</h3>";
            echo "<ol>";
            echo "<li>Edite o arquivo <code>config/database.php</code></li>";
            echo "<li>Confirme o nome do banco (formato: usuario_limpabrasil)</li>";
            echo "<li>Confirme o usuário do banco (formato: usuario_limpabrasil_user)</li>";
            echo "<li>Confirme a senha do banco de dados</li>";
            echo "</ol>";
            echo "<p>Exemplo para usuário cPanel 'william123':</p>";
            echo "<pre>";
            echo "DB_NAME: william123_limpabrasil\n";
            echo "DB_USER: william123_limpabrasil_user\n";
            echo "DB_PASS: sua_senha_segura";
            echo "</pre>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>✗ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
    
    echo "</div>";
    ?>
    
    <div class="step">
        <h3>📋 Próximos passos:</h3>
        <ol>
            <li>Delete este arquivo <code>install.php</code> por segurança</li>
            <li>Configure o e-mail no arquivo <code>config/email.php</code></li>
            <li>Teste o funcionamento do site</li>
            <li>Configure SSL/HTTPS no cPanel (recomendado)</li>
        </ol>
    </div>

</body>
</html>
