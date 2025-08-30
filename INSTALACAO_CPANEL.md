
# Instalação no cPanel da HostGator

## Pré-requisitos
- Conta de hospedagem na HostGator com cPanel
- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Acesso ao cPanel

## Passo 1: Upload dos arquivos

1. **Acesse o cPanel** da sua hospedagem HostGator
2. **Abra o Gerenciador de Arquivos** (File Manager)
3. **Navegue até a pasta public_html** (ou subpasta se for um subdomínio)
4. **Faça upload** de todos os arquivos do projeto, exceto:
   - `.replit`
   - `replit.md`
   - `README.md`
   - `limpabrasil-website.zip`

## Passo 2: Configuração do banco de dados

### 2.1 Criar o banco de dados

1. No cPanel, acesse **MySQL Databases**
2. Em "Create New Database", digite: `limpabrasil`
3. Clique em **Create Database**

### 2.2 Criar usuário do banco

1. Em "MySQL Users", crie um novo usuário:
   - Username: `limpabrasil_user`
   - Password: `[sua_senha_segura]`
2. Clique em **Create User**

### 2.3 Associar usuário ao banco

1. Em "Add User To Database":
   - User: `limpabrasil_user`
   - Database: `limpabrasil`
2. Marque **ALL PRIVILEGES**
3. Clique em **Make Changes**

### 2.4 Configurar conexão

1. Edite o arquivo `config/database.php`
2. Substitua as informações de conexão:

```php
// Database configuration - Update these values for your cPanel environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'seu_cpanel_user_limpabrasil'); // Formato: cpanel_user_database_name
define('DB_USER', 'seu_cpanel_user_limpabrasil_user'); // Formato: cpanel_user_database_user
define('DB_PASS', 'sua_senha_segura');
define('DB_CHARSET', 'utf8mb4');
```

**Importante**: No cPanel da HostGator, os nomes são prefixados com seu nome de usuário:
- Se seu usuário cPanel é `exemplo123`
- Database name será: `exemplo123_limpabrasil`
- Username será: `exemplo123_limpabrasil_user`

## Passo 3: Importar estrutura do banco

### 3.1 Via phpMyAdmin (Recomendado)

1. No cPanel, acesse **phpMyAdmin**
2. Selecione o banco `limpabrasil` criado
3. Clique na aba **Import**
4. Faça upload do arquivo `database/schema.sql`
5. Clique em **Go**

### 3.2 Via código (Alternativo)

1. Crie um arquivo temporário `install.php` na raiz:

```php
<?php
require_once 'config/database.php';

if (initializeDatabase()) {
    echo "Banco de dados configurado com sucesso!";
} else {
    echo "Erro ao configurar banco de dados. Verifique os logs.";
}
?>
```

2. Acesse `seusite.com/install.php` no navegador
3. **Delete o arquivo** `install.php` após a instalação

## Passo 4: Configuração de e-mail

1. Edite `config/email.php`
2. Configure com suas informações de e-mail:

```php
// Email configuration - Update for your cPanel setup
define('SMTP_HOST', 'localhost'); // HostGator usa localhost
define('SMTP_PORT', 587); // ou 465 para SSL
define('SMTP_USERNAME', 'contato@seudominio.com');
define('SMTP_PASSWORD', 'sua_senha_email');
define('SMTP_ENCRYPTION', 'tls'); // tls ou ssl

// Company email settings
define('COMPANY_EMAIL', 'contato@seudominio.com');
define('COMPANY_NAME', 'LimpaBrasil');
define('ADMIN_EMAIL', 'admin@seudominio.com');
```

## Passo 5: Configuração de permissões

No Gerenciador de Arquivos, ajuste as permissões:

1. **Pastas**: 755
   - `api/`
   - `admin/`
   - `assets/`
   - `config/`
   - `includes/`

2. **Arquivos PHP**: 644
   - Todos os arquivos `.php`

3. **Arquivos estáticos**: 644
   - `.html`, `.css`, `.js`

## Passo 6: Teste da instalação

1. Acesse seu site: `https://seudominio.com`
2. Teste o formulário de contato
3. Teste o agendamento de serviços
4. Acesse o painel admin: `https://seudominio.com/admin`

## Configurações avançadas

### SSL/HTTPS (Recomendado)

1. No cPanel, acesse **SSL/TLS**
2. Ative o **Let's Encrypt** gratuito
3. Force redirecionamento HTTPS

### Backup automático

1. Configure backups no cPanel
2. Faça backup da pasta `public_html`
3. Exporte o banco de dados regularmente

### Monitoramento

1. Ative logs de erro no cPanel
2. Monitore o arquivo `error_log`
3. Configure alertas de uptime

## Troubleshooting

### Erro de conexão com banco
- Verifique se os nomes incluem o prefixo do usuário cPanel
- Confirme a senha do usuário do banco
- Teste a conexão via phpMyAdmin

### Erro 500
- Verifique permissões dos arquivos
- Consulte o error_log no cPanel
- Confirme versão do PHP (mínimo 7.4)

### E-mail não funciona
- Verifique configurações SMTP
- Teste envio pelo webmail do cPanel
- Configure SPF/DKIM se necessário

### Formulários não funcionam
- Verifique se `allow_url_fopen` está habilitado
- Confirme permissões da pasta `api/`
- Teste conectividade com banco de dados

## Suporte

Para suporte técnico:
- Documentação HostGator: [https://www.hostgator.com.br/help](https://www.hostgator.com.br/help)
- Logs de erro: cPanel > Error Logs
- phpMyAdmin: Para diagnóstico do banco

## Notas importantes

- **Segurança**: Altere senhas padrão
- **Backup**: Sempre faça backup antes de atualizações
- **Atualizações**: Mantenha PHP e MySQL atualizados
- **Monitoramento**: Configure alertas de performance

---

*Última atualização: 30/08/2025*
