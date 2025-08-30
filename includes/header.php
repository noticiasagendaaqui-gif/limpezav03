<header class="bg-white shadow-lg fixed w-full top-0 z-50">
    <nav class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <div class="flex items-center space-x-3">
                <img src="assets/images/logo.svg" alt="LimpaBrasil" class="h-10 w-10">
                <h1 class="text-2xl font-bold text-primary-600">LimpaBrasil</h1>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex space-x-8">
                <a href="index.html" class="nav-link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.html' || basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : ''; ?>">Início</a>
                <a href="sobre.html" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'sobre.html' ? 'active' : ''; ?>">Sobre</a>
                <a href="servicos.html" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'servicos.html' ? 'active' : ''; ?>">Serviços</a>
                <a href="agendamento.html" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'agendamento.html' ? 'active' : ''; ?>">Agendamento</a>
                <a href="contato.html" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contato.html' ? 'active' : ''; ?>">Contato</a>
            </div>

            <!-- CTA Button -->
            <div class="hidden md:block">
                <a href="agendamento.html" class="bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    Agendar Serviço
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden p-2">
                <i data-feather="menu" class="w-6 h-6"></i>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4">
            <a href="index.html" class="block py-2 text-secondary-700 hover:text-primary-600">Início</a>
            <a href="sobre.html" class="block py-2 text-secondary-700 hover:text-primary-600">Sobre</a>
            <a href="servicos.html" class="block py-2 text-secondary-700 hover:text-primary-600">Serviços</a>
            <a href="agendamento.html" class="block py-2 text-secondary-700 hover:text-primary-600">Agendamento</a>
            <a href="contato.html" class="block py-2 text-secondary-700 hover:text-primary-600">Contato</a>
            <a href="agendamento.html" class="block mt-4 bg-primary-600 hover:bg-primary-700 text-white px-6 py-3 rounded-lg font-medium text-center">
                Agendar Serviço
            </a>
        </div>
    </nav>
</header>

<script src="assets/js/main.js"></script>
    <script src="assets/js/ai-assistant.js"></script>