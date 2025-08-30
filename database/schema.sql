-- LimpaBrasil Database Schema
-- Compatible with MySQL 5.7+ and MariaDB

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create database (uncomment if needed)
-- CREATE DATABASE IF NOT EXISTS `limpabrasil` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE `limpabrasil`;

-- --------------------------------------------------------

-- Table structure for table `clientes`
CREATE TABLE IF NOT EXISTS `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `endereco` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `agendamentos`
CREATE TABLE IF NOT EXISTS `agendamentos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cliente_id` int(11) NOT NULL,
  `tipo_servico` enum('residencial','comercial','pos-obra','carpetes','vidros','higienizacao') NOT NULL,
  `frequencia` enum('unica','semanal','quinzenal','mensal') NOT NULL DEFAULT 'unica',
  `data_preferida` date NOT NULL,
  `horario_preferido` enum('manha','tarde','noite','flexivel') NOT NULL DEFAULT 'manha',
  `area_m2` int(11) DEFAULT NULL,
  `observacoes` text DEFAULT NULL,
  `status` enum('pendente','confirmado','concluido','cancelado') NOT NULL DEFAULT 'pendente',
  `valor_estimado` decimal(10,2) DEFAULT NULL,
  `valor_final` decimal(10,2) DEFAULT NULL,
  `data_execucao` datetime DEFAULT NULL,
  `avaliacao` tinyint(1) DEFAULT NULL CHECK (`avaliacao` >= 1 AND `avaliacao` <= 5),
  `comentario_avaliacao` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_agendamentos_cliente` (`cliente_id`),
  KEY `idx_status` (`status`),
  KEY `idx_data_preferida` (`data_preferida`),
  KEY `idx_tipo_servico` (`tipo_servico`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `fk_agendamentos_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `contatos`
CREATE TABLE IF NOT EXISTS `contatos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `assunto` enum('orcamento','agendamento','duvidas','reclamacao','elogio','outros') NOT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `mensagem` text NOT NULL,
  `status` enum('novo','respondido','arquivado') NOT NULL DEFAULT 'novo',
  `resposta` text DEFAULT NULL,
  `respondido_por` varchar(255) DEFAULT NULL,
  `respondido_em` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_assunto` (`assunto`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `servicos`
CREATE TABLE IF NOT EXISTS `servicos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco_base` decimal(10,2) DEFAULT NULL,
  `unidade` enum('m2','hora','visita','projeto') NOT NULL DEFAULT 'visita',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `ordem` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_ordem` (`ordem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `funcionarios`
CREATE TABLE IF NOT EXISTS `funcionarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `data_admissao` date DEFAULT NULL,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ativo` (`ativo`),
  KEY `idx_cargo` (`cargo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `agendamento_funcionarios`
CREATE TABLE IF NOT EXISTS `agendamento_funcionarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agendamento_id` int(11) NOT NULL,
  `funcionario_id` int(11) NOT NULL,
  `funcao` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ag_func_agendamento` (`agendamento_id`),
  KEY `fk_ag_func_funcionario` (`funcionario_id`),
  CONSTRAINT `fk_ag_func_agendamento` FOREIGN KEY (`agendamento_id`) REFERENCES `agendamentos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ag_func_funcionario` FOREIGN KEY (`funcionario_id`) REFERENCES `funcionarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `configuracoes`
CREATE TABLE IF NOT EXISTS `configuracoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chave` varchar(100) NOT NULL,
  `valor` text DEFAULT NULL,
  `descricao` varchar(255) DEFAULT NULL,
  `tipo` enum('texto','numero','boolean','json') NOT NULL DEFAULT 'texto',
  `categoria` varchar(50) DEFAULT 'geral',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave` (`chave`),
  KEY `idx_categoria` (`categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Table structure for table `logs`
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario` varchar(255) DEFAULT NULL,
  `acao` varchar(255) NOT NULL,
  `tabela` varchar(100) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `dados_anteriores` json DEFAULT NULL,
  `dados_novos` json DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario` (`usuario`),
  KEY `idx_acao` (`acao`),
  KEY `idx_tabela` (`tabela`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Insert default services
INSERT INTO `servicos` (`nome`, `slug`, `descricao`, `preco_base`, `unidade`, `ativo`, `ordem`) VALUES
('Limpeza Residencial', 'residencial', 'Limpeza completa de residências, apartamentos e casas com produtos seguros e ecológicos.', 80.00, 'visita', 1, 1),
('Limpeza Comercial', 'comercial', 'Serviços especializados para escritórios, lojas, restaurantes e estabelecimentos comerciais.', 200.00, 'visita', 1, 2),
('Limpeza Pós-Obra', 'pos-obra', 'Limpeza especializada após reformas e construções, removendo resíduos e sujeira pesada.', 0.00, 'projeto', 1, 3),
('Limpeza de Carpetes', 'carpetes', 'Limpeza profissional de carpetes, tapetes e estofados com equipamentos especializados.', 15.00, 'm2', 1, 4),
('Limpeza de Vidros', 'vidros', 'Limpeza especializada de vidros residenciais e comerciais com técnicas profissionais.', 8.00, 'm2', 1, 5),
('Higienização', 'higienizacao', 'Serviços de higienização e sanitização para eliminar vírus, bactérias e fungos.', 120.00, 'visita', 1, 6);

-- --------------------------------------------------------

-- Insert default configurations
INSERT INTO `configuracoes` (`chave`, `valor`, `descricao`, `tipo`, `categoria`) VALUES
('empresa_nome', 'LimpaBrasil', 'Nome da empresa', 'texto', 'empresa'),
('empresa_email', 'contato@limpabrasil.com.br', 'E-mail principal da empresa', 'texto', 'empresa'),
('empresa_telefone', '(11) 99999-9999', 'Telefone principal da empresa', 'texto', 'empresa'),
('empresa_endereco', 'São Paulo, SP - Brasil', 'Endereço da empresa', 'texto', 'empresa'),
('empresa_cnpj', '00.000.000/0001-00', 'CNPJ da empresa', 'texto', 'empresa'),
('site_manutencao', '0', 'Site em manutenção (0=não, 1=sim)', 'boolean', 'sistema'),
('admin_email_notificacoes', '1', 'Enviar notificações por e-mail (0=não, 1=sim)', 'boolean', 'sistema'),
('agendamento_antecedencia_minima', '24', 'Horas de antecedência mínima para agendamento', 'numero', 'agendamento'),
('agendamento_limite_diario', '10', 'Limite de agendamentos por dia', 'numero', 'agendamento'),
('horario_funcionamento_inicio', '08:00', 'Horário de início do funcionamento', 'texto', 'funcionamento'),
('horario_funcionamento_fim', '18:00', 'Horário de fim do funcionamento', 'texto', 'funcionamento'),
('dias_funcionamento', '1,2,3,4,5,6', 'Dias da semana de funcionamento (1=seg, 7=dom)', 'texto', 'funcionamento');

-- --------------------------------------------------------

-- Create indexes for better performance
CREATE INDEX `idx_clientes_nome` ON `clientes` (`nome`);
CREATE INDEX `idx_agendamentos_data_status` ON `agendamentos` (`data_preferida`, `status`);
CREATE INDEX `idx_contatos_email_status` ON `contatos` (`email`, `status`);

-- --------------------------------------------------------

-- Create views for reporting
CREATE OR REPLACE VIEW `view_agendamentos_completos` AS
SELECT 
    a.id,
    a.tipo_servico,
    a.frequencia,
    a.data_preferida,
    a.horario_preferido,
    a.status,
    a.valor_estimado,
    a.valor_final,
    a.created_at,
    c.nome as cliente_nome,
    c.email as cliente_email,
    c.telefone as cliente_telefone,
    c.endereco as cliente_endereco,
    s.nome as servico_nome,
    s.preco_base as servico_preco_base
FROM agendamentos a
JOIN clientes c ON a.cliente_id = c.id
LEFT JOIN servicos s ON s.slug = a.tipo_servico;

-- --------------------------------------------------------

CREATE OR REPLACE VIEW `view_estatisticas_mensais` AS
SELECT 
    YEAR(created_at) as ano,
    MONTH(created_at) as mes,
    COUNT(*) as total_agendamentos,
    COUNT(CASE WHEN status = 'concluido' THEN 1 END) as agendamentos_concluidos,
    COUNT(CASE WHEN status = 'cancelado' THEN 1 END) as agendamentos_cancelados,
    SUM(CASE WHEN status = 'concluido' THEN valor_final ELSE 0 END) as receita_mensal,
    AVG(CASE WHEN status = 'concluido' AND avaliacao IS NOT NULL THEN avaliacao END) as avaliacao_media
FROM agendamentos 
GROUP BY YEAR(created_at), MONTH(created_at)
ORDER BY ano DESC, mes DESC;

-- --------------------------------------------------------

-- Create triggers for audit log
DELIMITER $$

CREATE TRIGGER `tr_clientes_insert` AFTER INSERT ON `clientes`
FOR EACH ROW
BEGIN
    INSERT INTO logs (acao, tabela, registro_id, dados_novos, created_at)
    VALUES ('INSERT', 'clientes', NEW.id, JSON_OBJECT(
        'nome', NEW.nome,
        'email', NEW.email,
        'telefone', NEW.telefone
    ), NOW());
END$$

CREATE TRIGGER `tr_agendamentos_update` AFTER UPDATE ON `agendamentos`
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO logs (acao, tabela, registro_id, dados_anteriores, dados_novos, created_at)
        VALUES ('UPDATE', 'agendamentos', NEW.id, 
            JSON_OBJECT('status', OLD.status),
            JSON_OBJECT('status', NEW.status),
            NOW());
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

-- Sample data (uncomment for development/testing)

-- Inserts de exemplo (opcional)
INSERT INTO clientes (nome, email, telefone, endereco) VALUES 
('João Silva', 'joao@email.com', '(11) 99999-9999', 'Rua A, 123 - São Paulo, SP'),
('Maria Santos', 'maria@email.com', '(11) 88888-8888', 'Av. B, 456 - São Paulo, SP');

-- Funcionários de exemplo
INSERT INTO funcionarios (nome, email, telefone, cargo, salario, data_admissao, status) VALUES 
('Carlos Manager', 'carlos@limpabrasil.com.br', '(11) 97777-7777', 'Gerente', 5000.00, '2024-01-15', 'ativo'),
('Ana Supervisor', 'ana@limpabrasil.com.br', '(11) 96666-6666', 'Supervisor', 3500.00, '2024-02-01', 'ativo'),
('Pedro Limpeza', 'pedro@limpabrasil.com.br', '(11) 95555-5555', 'Faxineiro', 2200.00, '2024-03-10', 'ativo');

COMMIT;