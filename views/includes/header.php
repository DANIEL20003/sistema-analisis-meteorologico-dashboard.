<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($titulo_pagina) ? $titulo_pagina : 'Sistema de Análisis Meteorológico - ESPOCH'; ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --bg-primary: #0a0e1a;
            --bg-secondary: #12182b;
            --bg-tertiary: #1a2236;
            --neon-cyan: #00ffff;
            --neon-blue: #0099ff;
            --neon-purple: #9d4edd;
            --neon-pink: #ff006e;
            --text-primary: #e8eaf0;
            --text-secondary: #a0a8c0;
            --border-color: rgba(0, 255, 255, 0.1);
            --shadow: rgba(0, 255, 255, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .main-header {
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 1.5rem 0;
            position: relative;
            z-index: 100;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
        }

        .logo-section {
            min-width: 200px;
            /* Asegura que ambos lados tengan el mismo ancho */
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo-img {
            width: 60px;
            height: 60px;
            object-fit: contain;
            filter: drop-shadow(0 0 8px var(--neon-cyan));
        }

        .logo-img.logo-geaa {
            width: 60px;
            height: 60px;
            object-fit: contain;
            filter: drop-shadow(0 0 8px var(--neon-cyan));
            transform: scale(2);
            /* Hace la imagen 1.5 veces más grande */
            transform-origin: center;
            /* Escala desde el centro */
        }

        .logo-text h1 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.2rem;
        }

        .logo-text p {
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .project-info {
            flex: 1;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .project-title {
            width: 100%;
        }

        .project-title h2 {
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--neon-cyan) 0%, var(--neon-blue) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.3rem;
            letter-spacing: 0.5px;
        }

        .project-title h3 {
            font-size: 0.95rem;
            color: var(--text-secondary);
            font-weight: 400;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.85);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(8px);
        }

        .modal-overlay.active {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: var(--bg-secondary);
            border: 1px solid var(--neon-cyan);
            border-radius: 15px;
            padding: 2rem;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0, 255, 255, 0.3);
            animation: slideUp 0.3s ease;
            position: relative;
        }

        @keyframes slideUp {
            from {
                transform: translateY(30px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .modal-header h3 {
            font-size: 1.3rem;
            color: var(--neon-cyan);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: var(--neon-pink);
            transform: rotate(90deg);
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--neon-cyan);
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
        }

        .alert {
            padding: 0.8rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            display: none;
        }

        .alert.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .alert-error {
            background: rgba(255, 0, 110, 0.15);
            border: 1px solid var(--neon-pink);
            color: var(--neon-pink);
        }

        .alert-success {
            background: rgba(0, 255, 136, 0.15);
            border: 1px solid #00ff88;
            color: #00ff88;
        }

        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, var(--neon-cyan), var(--neon-blue));
            color: var(--bg-primary);
            border: none;
            border-radius: 8px;
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 255, 255, 0.4);
        }

        .btn-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .main-nav {
            background: var(--bg-tertiary);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 500;
        }

        .nav-content {
            display: flex;
            align-items: center;
            position: relative;
        }

        .nav-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.5rem;
        }

        .nav-toggle span {
            display: block;
            width: 25px;
            height: 2px;
            background: var(--neon-cyan);
            margin: 5px 0;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-item {
            position: relative;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 1.2rem;
            color: var(--text-secondary);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active-page {
            color: var(--neon-cyan);
            background: rgba(0, 255, 255, 0.05);
        }

        .nav-link i:first-child {
            font-size: 1.1rem;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            margin-left: 0.5rem;
        }

        .status-indicator.online i {
            color: #00ff88;
            font-size: 0.6rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .nav-admin {
            position: absolute;
            right: 0;
            display: flex;
            align-items: center;
        }

        .admin-link {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1rem;
            color: var(--bg-primary);
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--neon-cyan), var(--neon-blue));
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 10px rgba(0, 255, 255, 0.2);
            white-space: nowrap;
        }

        .admin-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 255, 255, 0.4);
        }

        .admin-link i {
            font-size: 0.9rem;
        }

        .main-content {
            padding: 0rem 0;
            min-height: calc(100vh - 400px);
        }

        @media (max-width: 1024px) {
            .header-content {
                flex-wrap: wrap;
                justify-content: center;
            }

            .project-info {
                order: -1;
                width: 100%;
            }

            .nav-content {
                flex-direction: column;
                align-items: stretch;
            }

            .nav-menu {
                flex-direction: column;
                display: none;
                width: 100%;
                gap: 0;
            }

            .nav-menu.active {
                display: flex;
            }

            .nav-toggle {
                display: block;
                align-self: flex-start;
            }

            .nav-admin {
                position: static;
                width: 100%;
                padding: 1rem 0;
            }

            .admin-link {
                width: 100%;
                justify-content: center;
                font-size: 0.85rem;
                padding: 0.7rem 1.2rem;
            }
        }

        @media (max-width: 480px) {
            .modal-content {
                padding: 1.5rem;
            }
        }

        .password-wrapper {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-primary);
            cursor: pointer;
            font-size: 1.1rem;
            padding: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            opacity: 0;
            pointer-events: none;
        }

        .password-toggle.visible {
            opacity: 0.7;
            pointer-events: auto;
        }

        .password-toggle:hover {
            opacity: 1;
            color: var(--neon-cyan);
        }

        .password-toggle i {
            pointer-events: none;
        }
    </style>
    <!-- ===== LEAFLET CSS Y JS ===== -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>

<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo-section">
                    <img src="<?php echo (isset($ruta_base) ? $ruta_base : './'); ?>public/img/logo-espoch.png"
                        alt="Logo ESPOCH" class="logo-img">
                    <div class="logo-text">
                        <h1>ESPOCH</h1>
                        <p>Excelencia Académica</p>
                    </div>
                </div>

                <div class="project-info">
                    <div class="project-title">
                        <h2>SISTEMA DE ANÁLISIS METEOROLÓGICO</h2>
                        <h3>PARA ENERGÍAS RENOVABLES</h3>
                    </div>
                </div>

                <div class="logo-section">
                    <img src="<?php echo (isset($ruta_base) ? $ruta_base : './'); ?>public/img/logo_invertido1.png"
                        alt="Logo Facultad" class="logo-img logo-geaa">
                    <div class="logo-text">

                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="modal-overlay" id="loginModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-shield-alt"></i>
                    Iniciar Sesión Admin
                </h3>
                <button class="modal-close" id="closeLoginModal">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="alertMessage" class="alert"></div>

            <form id="loginForm" method="POST">
                <div class="form-group">
                    <label for="correo">
                        <i class="fas fa-envelope"></i> Correo Electrónico
                    </label>
                    <input type="email" id="correo" name="correo" placeholder="admin@espoch.edu.ec" required>
                </div>

                <div class="form-group">
                    <label for="contra">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <div class="password-wrapper">
                        <input type="password" id="contra" name="contra" placeholder="••••••••" required>
                        <button type="button" class="password-toggle" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit" id="btnSubmit">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Iniciar Sesión</span>
                </button>
            </form>
        </div>
    </div>

    <nav class="main-nav">
        <div class="container">
            <div class="nav-content">
                <button class="nav-toggle" id="navToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>

                <ul class="nav-menu" id="navMenu">
                    <li class="nav-item">
                        <a href="<?php echo (isset($ruta_base) ? $ruta_base : './'); ?>index.php"
                            class="nav-link <?php echo (isset($pagina_activa) && $pagina_activa == 'inicio') ? 'active-page' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Inicio</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?php echo (isset($ruta_base) ? $ruta_base : './'); ?>views/VGeoportal.php"
                            class="nav-link <?php echo (isset($pagina_activa) && $pagina_activa == 'geoportal') ? 'active-page' : ''; ?>">
                            <i class="fas fa-globe-americas"></i>
                            <span>Geoportal</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?php echo (isset($ruta_base) ? $ruta_base : './'); ?>views/VEstaciones.php"
                            class="nav-link <?php echo (isset($pagina_activa) && $pagina_activa == 'estaciones') ? 'active-page' : ''; ?>">
                            <i class="fas fa-database"></i>
                            <span>Datos</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?php echo (isset($ruta_base) ? $ruta_base : './'); ?>views/VDashboard.php"
                            class="nav-link <?php echo (isset($pagina_activa) && $pagina_activa == 'dashboard') ? 'active-page' : ''; ?>">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?php echo (isset($ruta_base) ? $ruta_base : './'); ?>views/VTiempoReal.php"
                            class="nav-link <?php echo (isset($pagina_activa) && $pagina_activa == 'tiemporeal') ? 'active-page' : ''; ?>">
                            <i class="fas fa-satellite-dish"></i>
                            <span>Tiempo Real</span>
                            <span class="status-indicator online">
                                <i class="fas fa-circle"></i>
                            </span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?php echo (isset($ruta_base) ? $ruta_base : './'); ?>views/VMapas.php"
                            class="nav-link <?php echo (isset($pagina_activa) && $pagina_activa == 'mapas') ? 'active-page' : ''; ?>">
                            <i class="fas fa-map-marked-alt"></i>
                            <span>Mapas</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="<?php echo (isset($ruta_base) ? $ruta_base : './'); ?>views/VReportes.php"
                            class="nav-link <?php echo (isset($pagina_activa) && $pagina_activa == 'reportes') ? 'active-page' : ''; ?>">
                            <i class="fas fa-gauge"></i>
                            <span>Sensores</span>
                        </a>
                    </li>
                </ul>

                <div class="nav-admin">
                    <button class="admin-link" id="openLoginModal">
                        <i class="fas fa-user-shield"></i>
                        <span>Administrar</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <main class="main-content">
        <div class="container">

            <script>
                const navToggle = document.getElementById('navToggle');
                const navMenu = document.getElementById('navMenu');
                const loginModal = document.getElementById('loginModal');
                const openLoginBtn = document.getElementById('openLoginModal');
                const closeLoginBtn = document.getElementById('closeLoginModal');
                const loginForm = document.getElementById('loginForm');
                const alertMessage = document.getElementById('alertMessage');
                const btnSubmit = document.getElementById('btnSubmit');

                const contraInput = document.getElementById('contra');
                const togglePasswordBtn = document.getElementById('togglePassword');
                const toggleIcon = togglePasswordBtn.querySelector('i');

                contraInput.addEventListener('input', () => {
                    if (contraInput.value.length > 0) {
                        togglePasswordBtn.classList.add('visible');
                    } else {
                        togglePasswordBtn.classList.remove('visible');
                    }
                });

                togglePasswordBtn.addEventListener('click', () => {
                    const type = contraInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    contraInput.setAttribute('type', type);

                    if (type === 'text') {
                        toggleIcon.classList.remove('fa-eye');
                        toggleIcon.classList.add('fa-eye-slash');
                    } else {
                        toggleIcon.classList.remove('fa-eye-slash');
                        toggleIcon.classList.add('fa-eye');
                    }
                });

                const originalReset = loginForm.reset.bind(loginForm);
                loginForm.reset = function () {
                    originalReset();
                    togglePasswordBtn.classList.remove('visible');
                    contraInput.setAttribute('type', 'password');
                    toggleIcon.classList.remove('fa-eye-slash');
                    toggleIcon.classList.add('fa-eye');
                };

                navToggle.addEventListener('click', () => {
                    navMenu.classList.toggle('active');
                });

                openLoginBtn.addEventListener('click', () => {
                    loginModal.classList.add('active');
                });

                closeLoginBtn.addEventListener('click', () => {
                    loginModal.classList.remove('active');
                    alertMessage.classList.remove('show');
                    loginForm.reset();
                });

                loginModal.addEventListener('click', (e) => {
                    if (e.target === loginModal) {
                        loginModal.classList.remove('active');
                        alertMessage.classList.remove('show');
                        loginForm.reset();
                    }
                });

                loginForm.addEventListener('submit', async (e) => {
                    e.preventDefault();

                    const correo = document.getElementById('correo').value;
                    const contra = document.getElementById('contra').value;

                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';

                    try {
                        // Verificar conexión a BD antes de intentar iniciar sesión
                        const connCheckResp = await fetch('<?php echo (isset($ruta_base) ? $ruta_base : './'); ?>model/MIniciarSesionAdmin.php?action=testConexion');
                        const connCheckJson = await connCheckResp.json();

                        if (!connCheckJson.success) {
                            alertMessage.className = 'alert alert-error show';
                            const errText = connCheckJson.error || connCheckJson.message || 'No hay conexión con la base de datos';
                            alertMessage.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + errText;
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
                            return; // abortar intento de login
                        }

                        const formData = new FormData();
                        formData.append('correo', correo);
                        formData.append('contra', contra);

                        const response = await fetch('<?php echo (isset($ruta_base) ? $ruta_base : './'); ?>model/MIniciarSesionAdmin.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await response.json();

                        if (result.success) {
                            alertMessage.className = 'alert alert-success show';
                            alertMessage.innerHTML = '<i class="fas fa-check-circle"></i> ' + result.message;

                            setTimeout(() => {
                                window.location.href = '<?php echo (isset($ruta_base) ? $ruta_base : './'); ?>controller/Cusercontroller.php?opcion=1';
                            }, 1500);
                        } else {
                            alertMessage.className = 'alert alert-error show';
                            alertMessage.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + result.message;
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
                        }
                    } catch (error) {
                        alertMessage.className = 'alert alert-error show';
                        alertMessage.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error de conexión';
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = '<i class="fas fa-sign-in-alt"></i> Iniciar Sesión';
                    }
                });
            </script>

            <!-- ===== WIDGET MANUAL DE USUARIO (Botón flotante + Modal) ===== -->
            <?php include(__DIR__ . '/manual_widget.php'); ?>