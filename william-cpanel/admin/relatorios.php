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

    // Get date range from filters (default last 30 days)
    $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
    $end_date = $_GET['end_date'] ?? date('Y-m-d');

    // Statistics queries
    $stats = [];

    // Total agendamentos no período
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamentos WHERE DATE(created_at) BETWEEN ? AND ?");
    $stmt->execute([$start_date, $end_date]);
    $stats['total_periodo'] = $stmt->fetchColumn();

    // Agendamentos por status
    $stmt = $pdo->prepare("
        SELECT status, COUNT(*) as total 
        FROM agendamentos 
        WHERE DATE(created_at) BETWEEN ? AND ? 
        GROUP BY status
    ");
    $stmt->execute([$start_date, $end_date]);
    $stats['por_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Serviços mais solicitados
    $stmt = $pdo->prepare("
        SELECT tipo_servico, COUNT(*) as total 
        FROM agendamentos 
        WHERE DATE(created_at) BETWEEN ? AND ? 
        GROUP BY tipo_servico 
        ORDER BY total DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $stats['servicos_populares'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Frequência mais escolhida
    $stmt = $pdo->prepare("
        SELECT frequencia, COUNT(*) as total 
        FROM agendamentos 
        WHERE DATE(created_at) BETWEEN ? AND ? 
        GROUP BY frequencia 
        ORDER BY total DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $stats['frequencias'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agendamentos por dia (últimos 30 dias)
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as data, COUNT(*) as total 
        FROM agendamentos 
        WHERE DATE(created_at) BETWEEN ? AND ? 
        GROUP BY DATE(created_at) 
        ORDER BY data ASC
    ");
    $stmt->execute([$start_date, $end_date]);
    $stats['por_dia'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Horários mais solicitados
    $stmt = $pdo->prepare("
        SELECT horario_preferido, COUNT(*) as total 
        FROM agendamentos 
        WHERE DATE(created_at) BETWEEN ? AND ? 
        GROUP BY horario_preferido 
        ORDER BY total DESC
    ");
    $stmt->execute([$start_date, $end_date]);
    $stats['horarios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Top clientes (mais agendamentos)
    $stmt = $pdo->prepare("
        SELECT c.nome, c.email, COUNT(a.id) as total_agendamentos
        FROM clientes c
        LEFT JOIN agendamentos a ON c.id = a.cliente_id
        WHERE DATE(a.created_at) BETWEEN ? AND ?
        GROUP BY c.id
        ORDER BY total_agendamentos DESC
        LIMIT 10
    ");
    $stmt->execute([$start_date, $end_date]);
    $stats['top_clientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - Painel Administrativo LimpaBrasil</title>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <a href="dashboard.php" class="flex items-center px-6 py-3 text-secondary-600 hover:text-primary-600 hover:bg-primary-50">
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
                    <i data-feather="briefcase" class="w-5 h-5 mr-3"></i>
                    Funcionários
                </a>
                <a href="relatorios.php" class="flex items-center px-6 py-3 text-primary-600 bg-primary-50 border-r-2 border-primary-600">
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
                <h1 class="text-3xl font-bold text-secondary-900">Relatórios e Analytics</h1>
                <p class="text-secondary-600">Visualize estatísticas detalhadas dos seus serviços</p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Data Inicial</label>
                        <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>" 
                               class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Data Final</label>
                        <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>" 
                               class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg">
                            Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-blue-100">
                            <i data-feather="calendar" class="w-6 h-6 text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-600">Total Agendamentos</p>
                            <p class="text-2xl font-bold text-secondary-900"><?php echo $stats['total_periodo']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100">
                            <i data-feather="check-circle" class="w-6 h-6 text-green-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-600">Confirmados</p>
                            <p class="text-2xl font-bold text-secondary-900">
                                <?php 
                                $confirmados = array_filter($stats['por_status'], function($s) { return $s['status'] === 'confirmado'; });
                                echo $confirmados ? reset($confirmados)['total'] : 0;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-yellow-100">
                            <i data-feather="clock" class="w-6 h-6 text-yellow-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-600">Pendentes</p>
                            <p class="text-2xl font-bold text-secondary-900">
                                <?php 
                                $pendentes = array_filter($stats['por_status'], function($s) { return $s['status'] === 'pendente'; });
                                echo $pendentes ? reset($pendentes)['total'] : 0;
                                ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-purple-100">
                            <i data-feather="users" class="w-6 h-6 text-purple-600"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-secondary-600">Clientes Ativos</p>
                            <p class="text-2xl font-bold text-secondary-900"><?php echo count($stats['top_clientes']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Agendamentos por Dia -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-secondary-900 mb-4">Agendamentos por Dia</h3>
                    <canvas id="agendamentosPorDia" width="400" height="200"></canvas>
                </div>

                <!-- Status Distribution -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-secondary-900 mb-4">Distribuição por Status</h3>
                    <canvas id="statusChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Services and Time Charts -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Serviços Populares -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-secondary-900 mb-4">Serviços Mais Solicitados</h3>
                    <canvas id="servicosChart" width="400" height="200"></canvas>
                </div>

                <!-- Horários Preferidos -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-secondary-900 mb-4">Horários Preferidos</h3>
                    <canvas id="horariosChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Top Clients Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-secondary-200">
                    <h3 class="text-lg font-medium text-secondary-900">Top Clientes no Período</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-secondary-200">
                        <thead class="bg-secondary-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Cliente
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    E-mail
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Total Agendamentos
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-secondary-200">
                            <?php if (!empty($stats['top_clientes'])): ?>
                                <?php foreach ($stats['top_clientes'] as $cliente): ?>
                                    <tr class="hover:bg-secondary-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-secondary-900">
                                                <?php echo htmlspecialchars($cliente['nome']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-600">
                                                <?php echo htmlspecialchars($cliente['email']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                                <?php echo $cliente['total_agendamentos']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-secondary-500">
                                        Nenhum cliente encontrado no período
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Prepare data for charts
        const agendamentosPorDiaData = <?php echo json_encode($stats['por_dia']); ?>;
        const statusData = <?php echo json_encode($stats['por_status']); ?>;
        const servicosData = <?php echo json_encode($stats['servicos_populares']); ?>;
        const horariosData = <?php echo json_encode($stats['horarios']); ?>;

        // Agendamentos por Dia Chart
        const ctx1 = document.getElementById('agendamentosPorDia').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: agendamentosPorDiaData.map(item => new Date(item.data).toLocaleDateString('pt-BR')),
                datasets: [{
                    label: 'Agendamentos',
                    data: agendamentosPorDiaData.map(item => item.total),
                    borderColor: '#0ea5e9',
                    backgroundColor: 'rgba(14, 165, 233, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Status Chart
        const ctx2 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: statusData.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
                datasets: [{
                    data: statusData.map(item => item.total),
                    backgroundColor: ['#f59e0b', '#10b981', '#3b82f6', '#ef4444']
                }]
            },
            options: {
                responsive: true
            }
        });

        // Serviços Chart
        const ctx3 = document.getElementById('servicosChart').getContext('2d');
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: servicosData.map(item => item.tipo_servico.replace('-', ' ')),
                datasets: [{
                    label: 'Solicitações',
                    data: servicosData.map(item => item.total),
                    backgroundColor: '#0ea5e9'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Horários Chart
        const ctx4 = document.getElementById('horariosChart').getContext('2d');
        new Chart(ctx4, {
            type: 'pie',
            data: {
                labels: horariosData.map(item => item.horario_preferido.replace('_', ' ')),
                datasets: [{
                    data: horariosData.map(item => item.total),
                    backgroundColor: ['#8b5cf6', '#06b6d4', '#84cc16', '#f97316']
                }]
            },
            options: {
                responsive: true
            }
        });

        // Initialize Feather icons
        feather.replace();
    </script>
</body>
</html>