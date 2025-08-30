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

    // Get all clients with their booking count
    $stmt = $pdo->query("
        SELECT c.*, COUNT(a.id) as total_agendamentos
        FROM clientes c
        LEFT JOIN agendamentos a ON c.id = a.cliente_id
        GROUP BY c.id
        ORDER BY c.created_at DESC
    ");
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes - Painel Administrativo LimpaBrasil</title>
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
                <a href="clientes.php" class="flex items-center px-6 py-3 text-primary-600 bg-primary-50 border-r-2 border-primary-600">
                    <i data-feather="users" class="w-5 h-5 mr-3"></i>
                    Clientes
                </a>
                <a href="funcionarios.php" class="flex items-center px-6 py-3 text-secondary-600 hover:text-primary-600 hover:bg-primary-50">
                    <i data-feather="briefcase" class="w-5 h-5 mr-3"></i>
                    Funcionários
                </a>
                <a href="relatorios.php" class="flex items-center px-6 py-3 text-secondary-600 hover:text-primary-600 hover:bg-primary-50">
                    <i data-feather="bar-chart-2" class="w-5 h-5 mr-3"></i>
                    Relatórios
                </a>
                <a href="../index.html" target="_blank" class="flex items-center px-6 py-3 text-secondary-600 hover:text-primary-600 hover:bg-primary-50"></a>
                    <i data-feather="external-link" class="w-5 h-5 mr-3"></i>
                    Ver Site
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-secondary-900">Clientes</h1>
                <p class="text-secondary-600">Gerencie todos os clientes cadastrados</p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Clients Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-secondary-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-secondary-900">Lista de Clientes</h3>
                        <div class="flex items-center space-x-2">
                            <input type="text" id="search" placeholder="Buscar clientes..." 
                                   class="px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <button class="bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg">
                                <i data-feather="search" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-secondary-200">
                        <thead class="bg-secondary-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Cliente
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Contato
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Endereço
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Agendamentos
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Cadastro
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-secondary-200">
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $cliente): ?>
                                    <tr class="hover:bg-secondary-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-secondary-900">
                                                    <?php echo htmlspecialchars($cliente['nome']); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-900">
                                                <?php echo htmlspecialchars($cliente['email']); ?>
                                            </div>
                                            <div class="text-sm text-secondary-500">
                                                <?php echo htmlspecialchars($cliente['telefone']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-secondary-900">
                                                <?php echo htmlspecialchars($cliente['endereco']); ?>
                                            </div>
                                            <div class="text-sm text-secondary-500">
                                                CEP: <?php echo htmlspecialchars($cliente['cep']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                                <?php echo $cliente['total_agendamentos']; ?> agendamento<?php echo $cliente['total_agendamentos'] != 1 ? 's' : ''; ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">
                                            <?php echo date('d/m/Y', strtotime($cliente['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button onclick="viewClient(<?php echo $cliente['id']; ?>)" 
                                                        class="text-primary-600 hover:text-primary-900">
                                                    <i data-feather="eye" class="w-4 h-4"></i>
                                                </button>
                                                <a href="mailto:<?php echo htmlspecialchars($cliente['email']); ?>" 
                                                   class="text-green-600 hover:text-green-900">
                                                    <i data-feather="mail" class="w-4 h-4"></i>
                                                </a>
                                                <a href="tel:<?php echo htmlspecialchars($cliente['telefone']); ?>" 
                                                   class="text-blue-600 hover:text-blue-900">
                                                    <i data-feather="phone" class="w-4 h-4"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-secondary-500">
                                        Nenhum cliente encontrado
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Client Details Modal -->
    <div id="clientModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
            <div class="p-6 border-b border-secondary-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-secondary-900">Detalhes do Cliente</h3>
                    <button onclick="closeClientModal()" class="text-secondary-400 hover:text-secondary-600">
                        <i data-feather="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
            <div id="clientDetails" class="p-6">
                <!-- Client details will be loaded here -->
            </div>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        // Search functionality
        document.getElementById('search').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // View client details
        function viewClient(clientId) {
            fetch(`../api/client-details.php?id=${clientId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('clientDetails').innerHTML = `
                            <div class="space-y-4">
                                <div>
                                    <h4 class="font-medium text-secondary-900">Informações Pessoais</h4>
                                    <div class="mt-2 space-y-2">
                                        <p><strong>Nome:</strong> ${data.client.nome}</p>
                                        <p><strong>E-mail:</strong> ${data.client.email}</p>
                                        <p><strong>Telefone:</strong> ${data.client.telefone}</p>
                                        <p><strong>Endereço:</strong> ${data.client.endereco}</p>
                                        <p><strong>CEP:</strong> ${data.client.cep}</p>
                                        <p><strong>Cadastrado em:</strong> ${new Date(data.client.created_at).toLocaleDateString('pt-BR')}</p>
                                    </div>
                                </div>
                                <div>
                                    <h4 class="font-medium text-secondary-900">Histórico de Agendamentos</h4>
                                    <div class="mt-2">
                                        ${data.bookings.length > 0 ? 
                                            data.bookings.map(booking => `
                                                <div class="p-3 bg-secondary-50 rounded mb-2">
                                                    <p><strong>Serviço:</strong> ${booking.tipo_servico}</p>
                                                    <p><strong>Data:</strong> ${new Date(booking.data_preferida).toLocaleDateString('pt-BR')}</p>
                                                    <p><strong>Status:</strong> ${booking.status}</p>
                                                </div>
                                            `).join('') : 
                                            '<p class="text-secondary-500">Nenhum agendamento encontrado</p>'
                                        }
                                    </div>
                                </div>
                            </div>
                        `;
                        document.getElementById('clientModal').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao carregar detalhes do cliente');
                });
        }

        function closeClientModal() {
            document.getElementById('clientModal').classList.add('hidden');
        }

        feather.replace();
    </script>
</body>
</html>