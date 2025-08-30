<?php
session_start();
require_once '../config/database.php';

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$message = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("UPDATE agendamentos SET status = ? WHERE id = ?");
        $stmt->execute([$_POST['status'], $_POST['booking_id']]);
        $message = 'Status atualizado com sucesso!';
    } catch (PDOException $e) {
        $message = 'Erro ao atualizar status: ' . $e->getMessage();
    }
}

try {
    $pdo = getDBConnection();

    // Get all bookings with client information
    $stmt = $pdo->query("
        SELECT a.*, c.nome as cliente_nome, c.email as cliente_email, c.telefone as cliente_telefone
        FROM agendamentos a
        LEFT JOIN clientes c ON a.cliente_id = c.id
        ORDER BY a.data_preferida DESC, a.created_at DESC
    ");
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = 'Erro ao conectar com o banco de dados: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos - Painel Administrativo LimpaBrasil</title>
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
                <a href="agendamentos.php" class="flex items-center px-6 py-3 text-primary-600 bg-primary-50 border-r-2 border-primary-600">
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
                <h1 class="text-3xl font-bold text-secondary-900">Agendamentos</h1>
                <p class="text-secondary-600">Gerencie todos os agendamentos de serviços</p>
            </div>

            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Filter and Search -->
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Buscar</label>
                        <input type="text" id="search" placeholder="Nome do cliente..." 
                               class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Status</label>
                        <select id="filterStatus" class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Todos</option>
                            <option value="pendente">Pendente</option>
                            <option value="confirmado">Confirmado</option>
                            <option value="concluido">Concluído</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Serviço</label>
                        <select id="filterService" class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">Todos</option>
                            <option value="residencial">Residencial</option>
                            <option value="comercial">Comercial</option>
                            <option value="pos-obra">Pós-obra</option>
                            <option value="carpetes">Carpetes</option>
                            <option value="vidros">Vidros</option>
                            <option value="higienizacao">Higienização</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-secondary-700 mb-2">Data</label>
                        <input type="date" id="filterDate" 
                               class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-secondary-200">
                    <h3 class="text-lg font-medium text-secondary-900">Lista de Agendamentos</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-secondary-200">
                        <thead class="bg-secondary-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Cliente
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Serviço
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Data/Horário
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Solicitado em
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-secondary-500 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-secondary-200" id="bookingsTable">
                            <?php if (!empty($agendamentos)): ?>
                                <?php foreach ($agendamentos as $agendamento): ?>
                                    <tr class="hover:bg-secondary-50" 
                                        data-status="<?php echo $agendamento['status']; ?>"
                                        data-service="<?php echo $agendamento['tipo_servico']; ?>"
                                        data-date="<?php echo $agendamento['data_preferida']; ?>">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-secondary-900">
                                                    <?php echo htmlspecialchars($agendamento['cliente_nome'] ?? 'Cliente não encontrado'); ?>
                                                </div>
                                                <div class="text-sm text-secondary-500">
                                                    <?php echo htmlspecialchars($agendamento['cliente_email'] ?? ''); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-900">
                                                <?php echo ucfirst(str_replace('-', ' ', $agendamento['tipo_servico'])); ?>
                                            </div>
                                            <div class="text-sm text-secondary-500">
                                                <?php echo ucfirst($agendamento['frequencia']); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-secondary-900">
                                                <?php echo date('d/m/Y', strtotime($agendamento['data_preferida'])); ?>
                                            </div>
                                            <div class="text-sm text-secondary-500">
                                                <?php echo ucfirst(str_replace('_', ' ', $agendamento['horario_preferido'])); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                <?php echo $agendamento['status'] === 'pendente' ? 'bg-yellow-100 text-yellow-800' : 
                                                         ($agendamento['status'] === 'confirmado' ? 'bg-green-100 text-green-800' : 
                                                         ($agendamento['status'] === 'concluido' ? 'bg-blue-100 text-blue-800' : 'bg-red-100 text-red-800')); ?>">
                                                <?php echo ucfirst($agendamento['status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-secondary-500">
                                            <?php echo date('d/m/Y H:i', strtotime($agendamento['created_at'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex space-x-2">
                                                <button onclick="viewBooking(<?php echo $agendamento['id']; ?>)" 
                                                        class="text-primary-600 hover:text-primary-900">
                                                    <i data-feather="eye" class="w-4 h-4"></i>
                                                </button>
                                                <button onclick="updateStatus(<?php echo $agendamento['id']; ?>, '<?php echo $agendamento['status']; ?>')" 
                                                        class="text-green-600 hover:text-green-900">
                                                    <i data-feather="edit" class="w-4 h-4"></i>
                                                </button>
                                                <a href="mailto:<?php echo htmlspecialchars($agendamento['cliente_email'] ?? ''); ?>" 
                                                   class="text-blue-600 hover:text-blue-900">
                                                    <i data-feather="mail" class="w-4 h-4"></i>
                                                </a>
                                                <a href="tel:<?php echo htmlspecialchars($agendamento['cliente_telefone'] ?? ''); ?>" 
                                                   class="text-purple-600 hover:text-purple-900">
                                                    <i data-feather="phone" class="w-4 h-4"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-secondary-500">
                                        Nenhum agendamento encontrado
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Details Modal -->
    <div id="bookingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg max-w-2xl w-full mx-4 max-h-screen overflow-y-auto">
            <div class="p-6 border-b border-secondary-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-secondary-900">Detalhes do Agendamento</h3>
                    <button onclick="closeBookingModal()" class="text-secondary-400 hover:text-secondary-600">
                        <i data-feather="x" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
            <div id="bookingDetails" class="p-6">
                <!-- Booking details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div id="statusModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg max-w-md w-full mx-4">
            <div class="p-6 border-b border-secondary-200">
                <h3 class="text-lg font-medium text-secondary-900">Atualizar Status</h3>
            </div>
            <form method="POST" class="p-6">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="booking_id" id="statusBookingId">

                <div class="mb-4">
                    <label class="block text-sm font-medium text-secondary-700 mb-2">Novo Status</label>
                    <select name="status" id="statusSelect" required
                            class="w-full px-3 py-2 border border-secondary-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="pendente">Pendente</option>
                        <option value="confirmado">Confirmado</option>
                        <option value="concluido">Concluído</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>

                <div class="flex space-x-3">
                    <button type="submit" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white px-4 py-2 rounded-lg">
                        Atualizar
                    </button>
                    <button type="button" onclick="closeStatusModal()" 
                            class="flex-1 bg-secondary-600 hover:bg-secondary-700 text-white px-4 py-2 rounded-lg">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
    <script>
        // Filter functionality
        function setupFilters() {
            const searchInput = document.getElementById('search');
            const statusFilter = document.getElementById('filterStatus');
            const serviceFilter = document.getElementById('filterService');
            const dateFilter = document.getElementById('filterDate');

            function applyFilters() {
                const searchTerm = searchInput.value.toLowerCase();
                const statusValue = statusFilter.value;
                const serviceValue = serviceFilter.value;
                const dateValue = dateFilter.value;

                const rows = document.querySelectorAll('#bookingsTable tr');

                rows.forEach(row => {
                    const text = row.textContent.toLowerCase();
                    const status = row.dataset.status;
                    const service = row.dataset.service;
                    const date = row.dataset.date;

                    const matchesSearch = !searchTerm || text.includes(searchTerm);
                    const matchesStatus = !statusValue || status === statusValue;
                    const matchesService = !serviceValue || service === serviceValue;
                    const matchesDate = !dateValue || date === dateValue;

                    row.style.display = matchesSearch && matchesStatus && matchesService && matchesDate ? '' : 'none';
                });
            }

            searchInput.addEventListener('input', applyFilters);
            statusFilter.addEventListener('change', applyFilters);
            serviceFilter.addEventListener('change', applyFilters);
            dateFilter.addEventListener('change', applyFilters);
        }

        // View booking details
        function viewBooking(bookingId) {
            fetch(`../api/booking-details.php?id=${bookingId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const booking = data.booking;
                        document.getElementById('bookingDetails').innerHTML = `
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <h4 class="font-medium text-secondary-900">Informações do Cliente</h4>
                                        <div class="mt-2 space-y-1 text-sm">
                                            <p><strong>Nome:</strong> ${booking.cliente_nome || 'N/A'}</p>
                                            <p><strong>E-mail:</strong> ${booking.cliente_email || 'N/A'}</p>
                                            <p><strong>Telefone:</strong> ${booking.cliente_telefone || 'N/A'}</p>
                                            <p><strong>Endereço:</strong> ${booking.endereco || 'N/A'}</p>
                                            <p><strong>CEP:</strong> ${booking.cep || 'N/A'}</p>
                                        </div>
                                    </div>
                                    <div>
                                        <h4 class="font-medium text-secondary-900">Detalhes do Serviço</h4>
                                        <div class="mt-2 space-y-1 text-sm">
                                            <p><strong>Tipo:</strong> ${booking.tipo_servico}</p>
                                            <p><strong>Frequência:</strong> ${booking.frequencia}</p>
                                            <p><strong>Data:</strong> ${new Date(booking.data_preferida).toLocaleDateString('pt-BR')}</p>
                                            <p><strong>Horário:</strong> ${booking.horario_preferido}</p>
                                            <p><strong>Área:</strong> ${booking.area_m2 || 'N/A'} m²</p>
                                            <p><strong>Status:</strong> ${booking.status}</p>
                                        </div>
                                    </div>
                                </div>
                                ${booking.observacoes ? `
                                    <div>
                                        <h4 class="font-medium text-secondary-900">Observações</h4>
                                        <p class="mt-2 text-sm text-secondary-600">${booking.observacoes}</p>
                                    </div>
                                ` : ''}
                                <div>
                                    <p class="text-xs text-secondary-500">Solicitado em: ${new Date(booking.created_at).toLocaleString('pt-BR')}</p>
                                </div>
                            </div>
                        `;
                        document.getElementById('bookingModal').classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao carregar detalhes do agendamento');
                });
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').classList.add('hidden');
        }

        function updateStatus(bookingId, currentStatus) {
            document.getElementById('statusBookingId').value = bookingId;
            document.getElementById('statusSelect').value = currentStatus;
            document.getElementById('statusModal').classList.remove('hidden');
        }

        function closeStatusModal() {
            document.getElementById('statusModal').classList.add('hidden');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            setupFilters();
            feather.replace();
        });
    </script>
</body>
</html>