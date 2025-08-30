<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

try {
    $pdo = getDBConnection();

    // Get statistics
    $stats = [];

    // Total clients
    $stmt = $pdo->query("SELECT COUNT(*) FROM clientes");
    $stats['total_clientes'] = $stmt->fetchColumn();

    // Total bookings
    $stmt = $pdo->query("SELECT COUNT(*) FROM agendamentos");
    $stats['total_agendamentos'] = $stmt->fetchColumn();

    // Pending bookings
    $stmt = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE status = 'pendente'");
    $stats['agendamentos_pendentes'] = $stmt->fetchColumn();

    // Today's bookings
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE DATE(data_preferida) = CURDATE()");
    $stmt->execute();
    $stats['agendamentos_hoje'] = $stmt->fetchColumn();

    // Total active employees (added)
    $stmt = $pdo->query("SELECT COUNT(*) FROM funcionarios WHERE status = 'ativo'");
    $stats['total_funcionarios'] = $stmt->fetchColumn();

    // Recent bookings
    $stmt = $pdo->query("
        SELECT a.*, c.nome as cliente_nome 
        FROM agendamentos a 
        LEFT JOIN clientes c ON a.cliente_id = c.id 
        ORDER BY a.created_at DESC 
        LIMIT 5
    ");
    $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent contacts
    $stmt = $pdo->query("
        SELECT * FROM contatos 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $recent_contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Painel Administrativo LimpaBrasil</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e'
                        },
                        secondary: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            200: '#e2e8f0',
                            300: '#cbd5e1',
                            400: '#94a3b8',
                            500: '#64748b',
                            600: '#475569',
                            700: '#334155',
                            800: '#1e293b',
                            900: '#0f172a'
                        }
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/feather-icons"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-secondary-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex items-center space-x-3">
                        <img src="../assets/images/logo.svg" alt="LimpaBrasil" class="h-8 w-8">
                        <h1 class="text-xl font-bold text-primary-600">LimpaBrasil Admin</h1>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-secondary-600">Olá, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="../api/auth.php?action=logout" class="text-red-600 hover:text-red-700">
                        <i data-feather="log-out" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <!-- Sidebar -->
        <div class="w-64 bg-white shadow-lg min-h-screen">
            <nav class="mt-8">
                <a href="dashboard.php" class="flex items-center px-6 py-3 text-primary-600 bg-primary-50 border-r-2 border-primary-600">
                    <i data-feather="home" class="w-5 h-5 mr-3"></i>
                    Dashboard
                </a>
                <a href="agendamentos.php" class="flex items-center px-6 py-3 text-secondary-600 hover:text-primary-600 hover:bg-primary-50">
                    <i data-feather="calendar" class="w-5 h-5 mr-3"></i>
                    Agendamentos
                </a>
                <a href="clientes.php" class="flex items-center px-6 py-3 text-secondary-600 hover:text-primary-600 hover:bg-primary-50">
                    <i data-feather="users" class="w-5 h-5 mr-3"></i>
                    Clientes
                </a>
                <a href="funcionarios.php" class="flex items-center px-6 py-3 text-secondary-600 hover:text-primary-600 hover:bg-primary-50">
                    <i data-feather="user-check" class="w-5 h-5 mr-3"></i>
                    Funcionários
                </a>
                <a href="relatorios.php" class="flex items-center px-6 py-3 text-secondary-600 hover:text-primary-600 hover:bg-primary-50">
                    <i data-feather="bar-chart-2" class="w-5 h-5 mr-3"></i>
                    Relatórios
                </a>
                <a href="../index.html" target="_blank" class="flex items-center px-6 py-3 text-secondary-600 hover:text-primary-600 hover:bg-primary-50">
                    <i data-feather="external-link" class="w-5 h-5 mr-3"></i>
                    Ver Site
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-secondary-900">Dashboard</h1>
                <p class="text-secondary-600">Bem-vindo ao painel administrativo da LimpaBrasil</p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="bg-primary-100 rounded-full p-3">
                            <i data-feather="users" class="w-6 h-6 text-primary-600"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-sm font-medium text-secondary-600">Total Clientes</h2>
                            <p class="text-2xl font-semibold text-secondary-900"><?php echo $stats['total_clientes'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="bg-green-100 rounded-full p-3">
                            <i data-feather="calendar" class="w-6 h-6 text-green-600"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-sm font-medium text-secondary-600">Total Agendamentos</h2>
                            <p class="text-2xl font-semibold text-secondary-900"><?php echo $stats['total_agendamentos'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="bg-yellow-100 rounded-full p-3">
                            <i data-feather="clock" class="w-6 h-6 text-yellow-600"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-sm font-medium text-secondary-600">Pendentes</h2>
                            <p class="text-2xl font-semibold text-secondary-900"><?php echo $stats['agendamentos_pendentes'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="bg-blue-100 rounded-full p-3">
                            <i data-feather="calendar-check" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-sm font-medium text-secondary-600">Hoje</h2>
                            <p class="text-2xl font-semibold text-secondary-900"><?php echo $stats['agendamentos_hoje'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>

                <!-- Employee Statistics Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="bg-purple-100 rounded-full p-3">
                            <i data-feather="user-check" class="w-6 h-6 text-purple-600"></i>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-sm font-medium text-secondary-600">Funcionários Ativos</h2>
                            <p class="text-2xl font-semibold text-secondary-900"><?php echo $stats['total_funcionarios'] ?? 0; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Bookings -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-secondary-200">
                        <h3 class="text-lg font-medium text-secondary-900">Agendamentos Recentes</h3>
                    </div>
                    <div class="p-6">
                        <?php if (!empty($recent_bookings)): ?>
                            <div class="space-y-4">
                                <?php foreach ($recent_bookings as $booking): ?>
                                    <div class="flex items-center justify-between p-3 bg-secondary-50 rounded">
                                        <div>
                                            <p class="font-medium text-secondary-900"><?php echo htmlspecialchars($booking['cliente_nome'] ?? 'Cliente não encontrado'); ?></p>
                                            <p class="text-sm text-secondary-600"><?php echo htmlspecialchars($booking['tipo_servico']); ?></p>
                                            <p class="text-sm text-secondary-500"><?php echo date('d/m/Y', strtotime($booking['data_preferida'])); ?></p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            <?php echo $booking['status'] === 'pendente' ? 'bg-yellow-100 text-yellow-800' : 
                                                     ($booking['status'] === 'confirmado' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-secondary-500 text-center py-4">Nenhum agendamento encontrado</p>
                        <?php endif; ?>
                        <div class="mt-4">
                            <a href="agendamentos.php" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                                Ver todos os agendamentos →
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Contacts -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-secondary-200">
                        <h3 class="text-lg font-medium text-secondary-900">Contatos Recentes</h3>
                    </div>
                    <div class="p-6">
                        <?php if (!empty($recent_contacts)): ?>
                            <div class="space-y-4">
                                <?php foreach ($recent_contacts as $contact): ?>
                                    <div class="flex items-center justify-between p-3 bg-secondary-50 rounded">
                                        <div>
                                            <p class="font-medium text-secondary-900"><?php echo htmlspecialchars($contact['nome']); ?></p>
                                            <p class="text-sm text-secondary-600"><?php echo htmlspecialchars($contact['assunto']); ?></p>
                                            <p class="text-sm text-secondary-500"><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></p>
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            Novo
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-secondary-500 text-center py-4">Nenhum contato encontrado</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>feather.replace();</script>
</body>
</html>