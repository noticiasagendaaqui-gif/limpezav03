
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
4. **Faça upload** de todos os arquivos da pasta `william-cpanel` para public_html
   - Esta pasta já contém apenas os arquivos necessários para produção
   - Não inclua arquivos de desenvolvimento (.replit, replit.md, etc.)

## Passo 2: Configuração do banco de dados

### 2.1 Usar banco existente (Configurado)

O banco de dados já está configurado com:
- **Database name**: `agend700_limpeza01`
- **Username**: `agend700_limpeza01`
- **Password**: `}02vd%R2_t;L`
- **Host**: `localhost`

### 2.2 Criar novo banco (Opcional)

Se preferir criar um novo banco:

1. No cPanel, acesse **MySQL Databases**
2. Em "Create New Database", digite: `limpabrasil`
3. Clique em **Create Database**

### 2.3 Criar usuário do banco (Se criar novo)

1. Em "MySQL Users", crie um novo usuário:
   - Username: `limpabrasil_user`
   - Password: `[sua_senha_segura]`
2. Clique em **Create User**

### 2.4 Associar usuário ao banco (Se criar novo)

1. Em "Add User To Database":
   - User: `limpabrasil_user`
   - Database: `limpabrasil`
2. Marque **ALL PRIVILEGES**
3. Clique em **Make Changes**

### 2.5 Configurar conexão

O arquivo `config/database.php` já está configurado. Se necessário editar:

```php
// Database configuration - Update these values for your cPanel environment
define('DB_HOST', 'localhost');
define('DB_NAME', 'agend700_limpeza01'); // Ou seu_cpanel_user_limpabrasil
define('DB_USER', 'agend700_limpeza01'); // Ou seu_cpanel_user_limpabrasil_user
define('DB_PASS', '}02vd%R2_t;L'); // Ou sua_senha_segura
define('DB_CHARSET', 'utf8mb4');
```

**Importante**: No cPanel da HostGator, os nomes são prefixados com seu nome de usuário:
- Se seu usuário cPanel é `william123`
- Database name será: `william123_limpabrasil`
- Username será: `william123_limpabrasil_user`

## Passo 3: Importar estrutura do banco

### 3.1 Via arquivo install.php (Recomendado)

1. Após fazer upload dos arquivos, acesse: `seusite.com/install.php`
2. O script instalará automaticamente todas as tabelas necessárias
3. **Delete o arquivo** `install.php` após a instalação por segurança

### 3.2 Via phpMyAdmin (Alternativo)

1. No cPanel, acesse **phpMyAdmin**
2. Selecione o banco de dados criado
3. Clique na aba **Import**
4. Faça upload do arquivo `database/schema.sql`
5. Clique em **Go**

### 3.3 Teste de conexão

Você pode usar o arquivo `test_connection.php` para testar:
1. Acesse: `seusite.com/test_connection.php`
2. Verifique se a conexão foi bem-sucedida
3. **Delete o arquivo** após o teste

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
   - `database/`

2. **Arquivos PHP**: 644
   - Todos os arquivos `.php`

3. **Arquivos estáticos**: 644
   - `.html`, `.css`, `.js`, `.svg`

## Passo 6: Configuração inicial do sistema

### 6.1 Acesso ao painel administrativo

1. Acesse: `https://seudominio.com/admin/`
2. **Login padrão**:
   - Usuário: `admin`
   - Senha: `admin123`
3. **IMPORTANTE**: Altere a senha imediatamente após o primeiro login

### 6.2 Funcionalidades disponíveis

- **Dashboard**: Visão geral dos agendamentos e estatísticas
- **Agendamentos**: Gerenciar todos os agendamentos de serviços
- **Clientes**: Cadastro e gestão de clientes
- **Funcionários**: Gerenciar equipe de funcionários
- **Relatórios**: Análises e relatórios do negócio

## Passo 7: Teste da instalação

1. **Site principal**: `https://seudominio.com`
   - Teste o formulário de contato
   - Teste o agendamento de serviços
   - Verifique se todas as páginas carregam corretamente

2. **Painel administrativo**: `https://seudominio.com/admin`
   - Faça login com credenciais padrão
   - Teste todas as funcionalidades
   - Verifique se os dados são salvos corretamente

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
- Use o arquivo `test_connection.php` para diagnóstico

### Erro 500
- Verifique permissões dos arquivos (644 para arquivos, 755 para pastas)
- Consulte o error_log no cPanel
- Confirme versão do PHP (mínimo 7.4)

### E-mail não funciona
- Verifique configurações SMTP no `config/email.php`
- Teste envio pelo webmail do cPanel
- Configure SPF/DKIM se necessário

### Formulários não funcionam
- Verifique se `allow_url_fopen` está habilitado
- Confirme permissões da pasta `api/`
- Teste conectividade com banco de dados

### Página em branco ou erro de session
- Verifique se as sessões PHP estão habilitadas
- Confirme permissões da pasta de sessões
- Teste sem cache do navegador

### Ícones não aparecem (shield-check)
- O sistema usa Feather Icons via CDN
- Verifique conexão com a internet
- Alguns ícones podem não existir na biblioteca

## Estrutura de arquivos

```
william-cpanel/
├── admin/              # Painel administrativo
├── api/               # APIs para formulários e dados
├── assets/            # CSS, JS e imagens
├── config/            # Configurações do sistema
├── database/          # Schema do banco de dados
├── includes/          # Arquivos de include (header/footer)
├── index.html         # Página principal
├── agendamento.html   # Página de agendamento
├── contato.html       # Página de contato
├── servicos.html      # Página de serviços
├── sobre.html         # Página sobre a empresa
├── install.php        # Script de instalação (deletar após uso)
└── test_connection.php # Teste de conexão (deletar após uso)
```

## Segurança

### Após instalação:
1. **Delete arquivos temporários**:
   - `install.php`
   - `test_connection.php`

2. **Altere senhas padrão**:
   - Login administrativo
   - Banco de dados (se aplicável)

3. **Configure backup regular**

4. **Monitore logs de acesso**

## Suporte

Para suporte técnico:
- **Documentação HostGator**: [https://www.hostgator.com.br/help](https://www.hostgator.com.br/help)
- **Logs de erro**: cPanel > Error Logs
- **phpMyAdmin**: Para diagnóstico do banco
- **File Manager**: Para verificar arquivos e permissões

## Notas importantes

- **Performance**: O CDN do Tailwind CSS gera warning em produção (normal)
- **Segurança**: Sempre use HTTPS em produção
- **Backup**: Configure backup automático mensal
- **Atualizações**: Mantenha PHP e MySQL atualizados
- **Monitoramento**: Configure alertas de performance

---

*Última atualização: 30/08/2025*
*Versão: William CPanel v1.0*
