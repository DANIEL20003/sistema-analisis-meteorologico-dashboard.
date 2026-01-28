<?php
session_start();

// Verificar que el admin esté logueado
if (!isset($_SESSION['admin_logged']) || $_SESSION['admin_logged'] !== true) {
    header('Location: ../controller/Cusercontroller.php?opcion=2');
    exit;
}

$admin_nombre = $_SESSION['admin_nombre'] ?? 'Administrador';

// Detectar la página activa (se puede pasar como variable desde cada página)
$pagina_activa = $pagina_activa ?? 'inicio';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina ?? 'Sistema de Análisis Meteorológico - Panel Admin'; ?></title>
    <!-- Favicon opcional -->
    <link rel="icon" type="image/png" href="../public/img/logo-espoch.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* Modal de Cerrar Sesión */
        .logout-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 14, 26, 0.9);
            z-index: 10000;
            justify-content: center;
            align-items: center;
        }

        .logout-modal-overlay.show {
            display: flex;
        }

        .logout-modal {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            width: 90%;
            max-width: 450px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        }

        .logout-modal-header {
            padding: 2rem;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .logout-modal-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 1rem;
            background: linear-gradient(135deg, var(--neon-red), #cc0033);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-modal-icon i {
            font-size: 1.5rem;
            color: #fff;
        }

        .logout-modal-header h3 {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .logout-modal-body {
            padding: 2rem;
            text-align: center;
        }

        .logout-modal-body p {
            font-size: 1rem;
            color: var(--text-primary);
            margin: 0 0 0.5rem 0;
            line-height: 1.6;
        }

        .logout-modal-subtitle {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .logout-modal-footer {
            padding: 1.5rem 2rem 2rem;
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .logout-modal-btn {
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            min-width: 130px;
            justify-content: center;
        }

        .btn-cancelar {
            background: var(--bg-tertiary);
            color: var(--neon-cyan);
            border: 1px solid var(--neon-cyan);
        }

        .btn-cancelar:hover {
            background: rgba(0, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .btn-confirmar {
            background: linear-gradient(135deg, var(--neon-red), #cc0033);
            color: #fff;
        }

        .btn-confirmar:hover {
            background: linear-gradient(135deg, #ff1a53, #990029);
            transform: translateY(-2px);
        }

        @media (max-width: 480px) {
            .logout-modal {
                width: 95%;
            }

            .logout-modal-footer {
                flex-direction: column;
            }

            .logout-modal-btn {
                width: 100%;
            }
        }

        :root {
            --bg-primary: #0a0e1a;
            --bg-secondary: #12182b;
            --bg-tertiary: #1a2236;
            --neon-cyan: #00ffff;
            --neon-blue: #0099ff;
            --neon-purple: #9d4edd;
            --neon-pink: #ff006e;
            --neon-red: #ff0040;
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
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 2rem;
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

        .admin-info-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1rem;
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid var(--neon-cyan);
            border-radius: 8px;
            font-size: 0.85rem;
            color: var(--neon-cyan);
        }

        .admin-info-badge i {
            font-size: 1rem;
        }

        .admin-name {
            font-weight: 600;
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
            position: relative;
        }

        .nav-link:hover {
            color: var(--neon-cyan);
            background: rgba(0, 255, 255, 0.05);
        }

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

        .logout-link {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.55rem 1rem;
            color: #fff;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: 600;
            background: linear-gradient(135deg, var(--neon-red), #cc0033);
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 10px rgba(255, 0, 64, 0.3);
            white-space: nowrap;
        }

        .logout-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 0, 64, 0.5);
            background: linear-gradient(135deg, #ff1a53, #990029);
        }

        .logout-link i {
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

            .admin-info-badge {
                width: 100%;
                justify-content: center;
                margin-top: 1rem;
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

            .logout-link {
                width: 100%;
                justify-content: center;
                font-size: 0.85rem;
                padding: 0.7rem 1.2rem;
            }
        }

        /* ============================================
           SISTEMA DE NOTIFICACIONES
        ============================================ */
        .notifications-container {
            position: relative;
            display: flex;
            align-items: center;
            margin-right: 1rem;
        }

        .notification-bell {
            position: relative;
            background: rgba(0, 255, 255, 0.1);
            border: 1px solid var(--neon-cyan);
            border-radius: 50%;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .notification-bell:hover {
            background: rgba(0, 255, 255, 0.2);
            transform: scale(1.05);
        }

        .notification-bell i {
            font-size: 1.2rem;
            color: var(--neon-cyan);
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: linear-gradient(135deg, var(--neon-red), #cc0033);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 700;
            min-width: 20px;
            height: 20px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
            box-shadow: 0 2px 8px rgba(255, 0, 64, 0.5);
            animation: badge-pulse 2s infinite;
        }

        @keyframes badge-pulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        .notification-badge.hidden {
            display: none;
        }

        /* Dropdown de notificaciones */
        .notifications-dropdown {
            position: absolute;
            top: 60px;
            right: 0;
            width: 420px;
            max-height: 600px;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
        }

        .notifications-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notifications-header {
            padding: 1.2rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .notifications-header h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .notifications-count {
            background: rgba(0, 255, 255, 0.2);
            color: var(--neon-cyan);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
        }

        .notifications-list {
            max-height: 480px;
            overflow-y: auto;
            padding: 0.5rem 0;
        }

        .notifications-list::-webkit-scrollbar {
            width: 6px;
        }

        .notifications-list::-webkit-scrollbar-track {
            background: var(--bg-tertiary);
        }

        .notifications-list::-webkit-scrollbar-thumb {
            background: var(--neon-cyan);
            border-radius: 3px;
        }

        .notification-item {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(0, 255, 255, 0.05);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .notification-item:hover {
            background: rgba(0, 255, 255, 0.05);
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            margin-bottom: 0.8rem;
        }

        .notification-icon.error-variables {
            background: linear-gradient(135deg, var(--neon-red), #cc0033);
            color: #fff;
        }

        .notification-icon.error-duplicado {
            background: linear-gradient(135deg, var(--neon-purple), #7b2cbf);
            color: #fff;
        }

        .notification-icon.error-datos {
            background: linear-gradient(135deg, #ff9500, #ff6b00);
            color: #fff;
        }

        .notification-icon.error-general {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-secondary);
        }

        .notification-content {
            flex: 1;
        }

        .notification-station {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--neon-cyan);
            margin-bottom: 0.3rem;
        }

        .notification-description {
            font-size: 0.8rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .notification-variables {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.6rem;
            max-height: 50px;
            overflow: hidden;
            position: relative;
        }

        .notification-variables.collapsed {
            max-height: 50px;
        }

        .notification-variables-count {
            background: rgba(255, 0, 64, 0.2);
            color: var(--neon-red);
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            border: 1px solid rgba(255, 0, 64, 0.3);
            white-space: nowrap;
        }

        .variable-tag {
            background: rgba(255, 0, 64, 0.2);
            color: var(--neon-red);
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            border: 1px solid rgba(255, 0, 64, 0.3);
        }

        .notification-time {
            font-size: 0.7rem;
            color: var(--text-secondary);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .notification-time i {
            font-size: 0.65rem;
        }

        .notifications-empty {
            padding: 3rem 1.5rem;
            text-align: center;
            color: var(--text-secondary);
        }

        .notifications-empty i {
            font-size: 3rem;
            color: var(--text-secondary);
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        .notifications-empty p {
            font-size: 0.9rem;
        }

        .notifications-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-color);
            text-align: center;
        }

        .view-all-notifications {
            color: var(--neon-cyan);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.2s ease;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .view-all-notifications:hover {
            color: var(--neon-blue);
        }

        /* Modal de Historial Completo */
        .history-modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 14, 26, 0.95);
            z-index: 10000;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .history-modal-overlay.show {
            display: flex;
        }

        .history-modal {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            width: 100%;
            max-width: 900px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.7);
        }

        .history-modal-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .history-modal-title {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .history-modal-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--neon-red), #cc0033);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.2rem;
        }

        .history-modal-title h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .history-modal-title p {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin: 0.2rem 0 0 0;
        }

        .history-modal-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 1.5rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s ease;
        }

        .history-modal-close:hover {
            background: rgba(255, 0, 64, 0.1);
            color: var(--neon-red);
        }

        .history-modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.5rem;
        }

        .history-modal-body::-webkit-scrollbar {
            width: 8px;
        }

        .history-modal-body::-webkit-scrollbar-track {
            background: var(--bg-tertiary);
        }

        .history-modal-body::-webkit-scrollbar-thumb {
            background: var(--neon-cyan);
            border-radius: 4px;
        }

        .history-item {
            background: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .history-item:hover {
            border-color: var(--neon-cyan);
            transform: translateX(5px);
        }

        .history-item-header {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .history-item-icon {
            width: 50px;
            height: 50px;
            min-width: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .history-item-info {
            flex: 1;
        }

        .history-item-station {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--neon-cyan);
            margin-bottom: 0.3rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .history-item-error {
            font-size: 0.9rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .history-item-time {
            font-size: 0.8rem;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .history-item-variables {
            background: rgba(255, 0, 64, 0.05);
            border: 1px solid rgba(255, 0, 64, 0.2);
            border-radius: 8px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .history-item-variables-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--neon-red);
            margin-bottom: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .history-variables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 0.5rem;
        }

        .history-variable-tag {
            background: rgba(255, 0, 64, 0.15);
            color: var(--neon-red);
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.4rem 0.7rem;
            border-radius: 6px;
            border: 1px solid rgba(255, 0, 64, 0.3);
            text-align: center;
        }

        .history-empty {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--text-secondary);
        }

        .history-empty i {
            font-size: 4rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        .history-empty p {
            font-size: 1rem;
        }

        @media (max-width: 768px) {
            .notifications-dropdown {
                width: calc(100vw - 40px);
                right: -10px;
            }

            .notifications-container {
                margin-right: 0.5rem;
            }

            .history-modal {
                max-width: 100%;
                margin: 10px;
            }

            .history-modal-header {
                padding: 1rem 1.5rem;
            }

            .history-modal-title h3 {
                font-size: 1.1rem;
            }

            .history-variables-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }
    </style>
</head>

<body>
    <header class="main-header">
        <div class="container">
            <div class="header-content">
                <div class="logo-section">
                    <img src="../public/img/logo-espoch.png" alt="Logo ESPOCH" class="logo-img">
                    <div class="logo-text">
                        <h1>ESPOCH</h1>
                        <p>Excelencia Académica</p>
                    </div>
                </div>

                <div class="project-info">
                    <div class="project-title">
                        <h2>SISTEMA DE ANÁLISIS METEOROLÓGICO</h2>
                        <h3>PANEL DE ADMINISTRACIÓN</h3>
                    </div>
                </div>

                <div style="display: flex; align-items: center; gap: 1rem;">
                    <!-- SISTEMA DE NOTIFICACIONES -->
                    <div class="notifications-container">
                        <div class="notification-bell" id="notificationBell">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge hidden" id="notificationBadge">0</span>
                        </div>

                        <div class="notifications-dropdown" id="notificationsDropdown">
                            <div class="notifications-header">
                                <h3><i class="fas fa-exclamation-triangle" style="color: var(--neon-red); margin-right: 0.5rem;"></i>Errores Tiempo Real</h3>
                                <span class="notifications-count" id="notificationsCount">0</span>
                            </div>

                            <div class="notifications-list" id="notificationsList">
                                <!-- Las notificaciones se cargan dinámicamente -->
                            </div>

                            <div class="notifications-footer">
                                <a href="#" class="view-all-notifications" id="viewHistoryBtn">
                                    <i class="fas fa-history"></i>
                                    Ver historial completo
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Modal de Historial Completo -->
                    <div class="history-modal-overlay" id="historyModal">
                        <div class="history-modal">
                            <div class="history-modal-header">
                                <div class="history-modal-title">
                                    <div class="history-modal-icon">
                                        <i class="fas fa-history"></i>
                                    </div>
                                    <div>
                                        <h3>Historial de Errores</h3>
                                        <p>Últimas 15 notificaciones del sistema de tiempo real</p>
                                    </div>
                                </div>
                                <button class="history-modal-close" onclick="cerrarHistorialModal()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <div class="history-modal-body" id="historyModalBody">
                                <!-- Se carga dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <div class="admin-info-badge">
                        <i class="fas fa-user-shield"></i>
                        <span class="admin-name"><?php echo htmlspecialchars($admin_nombre); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </header>

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
                        <a href="../controller/Cusercontroller.php?opcion=1"
                            class="nav-link <?php echo ($pagina_activa == 'inicio') ? 'active-page' : ''; ?>">
                            <i class="fas fa-home"></i>
                            <span>Inicio</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../controller/Cusercontroller.php?opcion=6"
                            class="nav-link <?php echo ($pagina_activa == 'geoportal') ? 'active-page' : ''; ?>">
                            <i class="fas fa-globe-americas"></i>
                            <span>Geoportal</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="../controller/Cusercontroller.php?opcion=4"
                            class="nav-link <?php echo ($pagina_activa == 'estaciones') ? 'active-page' : ''; ?>">
                            <i class="fas fa-database"></i>
                            <span>Datos</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="../controller/Cusercontroller.php?opcion=3"
                            class="nav-link <?php echo ($pagina_activa == 'dashboard') ? 'active-page' : ''; ?>">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="../controller/Cusercontroller.php?opcion=5"
                            class="nav-link <?php echo ($pagina_activa == 'tiempo-real') ? 'active-page' : ''; ?>">
                            <i class="fas fa-satellite-dish"></i>
                            <span>Tiempo Real</span>
                            <span class="status-indicator online">
                                <i class="fas fa-circle"></i>
                            </span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="../controller/Cusercontroller.php?opcion=7"
                            class="nav-link <?php echo ($pagina_activa == 'reportes') ? 'active-page' : ''; ?>">
                            <i class="fas fa-file-alt"></i>
                            <span>Administrar</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="../controller/Cusercontroller.php?opcion=8"
                            class="nav-link <?php echo ($pagina_activa == 'cargar') ? 'active-page' : ''; ?>">
                            <i class="fas fa-upload"></i>
                            <span>Cargar</span>
                        </a>
                    </li>
                </ul>

                <div class="nav-admin">
                    <button class="logout-link" id="logoutBtn">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Modal de Cerrar Sesión -->
    <div class="logout-modal-overlay" id="logoutModal">
        <div class="logout-modal">
            <div class="logout-modal-header">
                <div class="logout-modal-icon">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <h3>Cerrar Sesión</h3>
            </div>
            <div class="logout-modal-body">
                <p>¿Está seguro que desea cerrar sesión?</p>
                <p class="logout-modal-subtitle">Se finalizará su sesión actual en el sistema.</p>
            </div>
            <div class="logout-modal-footer">
                <button class="logout-modal-btn btn-cancelar" onclick="cerrarModalLogout()">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button class="logout-modal-btn btn-confirmar" onclick="confirmarLogout()">
                    <i class="fas fa-check"></i>
                    Confirmar
                </button>
            </div>
        </div>
    </div>

    <main class="main-content">
        <div class="container">

            <script>
                // ============================================
                // NAVEGACIÓN Y LOGOUT
                // ============================================
                const navToggle = document.getElementById('navToggle');
                const navMenu = document.getElementById('navMenu');
                const logoutBtn = document.getElementById('logoutBtn');
                const logoutModal = document.getElementById('logoutModal');

                navToggle.addEventListener('click', () => {
                    navMenu.classList.toggle('active');
                });

                logoutBtn.addEventListener('click', () => {
                    logoutModal.classList.add('show');
                });

                function cerrarModalLogout() {
                    logoutModal.classList.remove('show');
                }

                function confirmarLogout() {
                    window.location.href = '../model/MCerrarSesion.php';
                }

                logoutModal.addEventListener('click', (e) => {
                    if (e.target === logoutModal) {
                        cerrarModalLogout();
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && logoutModal.classList.contains('show')) {
                        cerrarModalLogout();
                    }
                });

                // ============================================
                // SISTEMA DE NOTIFICACIONES
                // ============================================
                const notificationBell = document.getElementById('notificationBell');
                const notificationsDropdown = document.getElementById('notificationsDropdown');
                const notificationBadge = document.getElementById('notificationBadge');
                const notificationsCount = document.getElementById('notificationsCount');
                const notificationsList = document.getElementById('notificationsList');
                const historyModal = document.getElementById('historyModal');
                const historyModalBody = document.getElementById('historyModalBody');
                const viewHistoryBtn = document.getElementById('viewHistoryBtn');

                // Abrir/cerrar dropdown de notificaciones
                notificationBell.addEventListener('click', (e) => {
                    e.stopPropagation();
                    notificationsDropdown.classList.toggle('show');
                });

                // Cerrar dropdown al hacer clic fuera
                document.addEventListener('click', (e) => {
                    if (!notificationsDropdown.contains(e.target) && !notificationBell.contains(e.target)) {
                        notificationsDropdown.classList.remove('show');
                    }
                });

                // Función para cargar notificaciones
                async function cargarNotificaciones() {
                    try {
                        const response = await fetch('../controller/CNotificaciones.php');
                        const data = await response.json();

                        console.log('Datos recibidos:', data); // Para debugging

                        if (data.exito) {
                            actualizarNotificaciones(data.notificaciones, data.total);
                        } else {
                            console.error('Error al cargar notificaciones:', data.mensaje);
                            mostrarNotificacionesVacias();
                        }
                    } catch (error) {
                        console.error('Error en la petición de notificaciones:', error);
                        mostrarNotificacionesVacias();
                    }
                }

                // Función para actualizar el UI de notificaciones
                function actualizarNotificaciones(notificaciones, total) {
                    // Actualizar badge
                    if (total > 0) {
                        notificationBadge.textContent = total > 99 ? '99+' : total;
                        notificationBadge.classList.remove('hidden');
                    } else {
                        notificationBadge.classList.add('hidden');
                    }

                    // Actualizar contador en header del dropdown
                    notificationsCount.textContent = total;

                    // Limpiar lista
                    notificationsList.innerHTML = '';

                    // Si no hay notificaciones
                    if (!notificaciones || notificaciones.length === 0) {
                        mostrarNotificacionesVacias();
                        return;
                    }

                    // Agregar cada notificación
                    notificaciones.forEach(notif => {
                        const item = crearItemNotificacion(notif);
                        notificationsList.appendChild(item);
                    });
                }

                // Mostrar mensaje cuando no hay notificaciones
                function mostrarNotificacionesVacias() {
                    notificationsList.innerHTML = `
                        <div class="notifications-empty">
                            <i class="fas fa-check-circle"></i>
                            <p>No hay errores recientes</p>
                        </div>
                    `;
                }

                // Función para crear un item de notificación
                function crearItemNotificacion(notif) {
                    const div = document.createElement('div');
                    div.className = 'notification-item';

                    // Determinar icono según tipo de error
                    let iconClass = 'error-general';
                    let icon = 'fa-exclamation-circle';

                    switch (notif.error_tipo) {
                        case 'VARIABLES_FALTANTES':
                            iconClass = 'error-variables';
                            icon = 'fa-exclamation-triangle';
                            break;
                        case 'DUPLICADO':
                            iconClass = 'error-duplicado';
                            icon = 'fa-copy';
                            break;
                        case 'SIN_DATOS':
                            iconClass = 'error-datos';
                            icon = 'fa-file-excel';
                            break;
                    }

                    // HTML del item
                    let html = `
                        <div class="notification-icon ${iconClass}">
                            <i class="fas ${icon}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-station">
                                <i class="fas fa-broadcast-tower" style="margin-right: 0.3rem;"></i>
                                ${notif.nombre_estacion}
                            </div>
                            <div class="notification-description">
                                ${notif.error_descripcion}
                            </div>
                    `;

                    // Agregar variables faltantes RESUMIDAS (máximo 5 + contador)
                    if (notif.variables_faltantes && notif.variables_faltantes.length > 0) {
                        html += '<div class="notification-variables">';

                        const maxVisible = 5;
                        const totalVariables = notif.variables_faltantes.length;

                        // Mostrar máximo 5 variables
                        notif.variables_faltantes.slice(0, maxVisible).forEach(variable => {
                            html += `<span class="variable-tag">${variable}</span>`;
                        });

                        // Si hay más de 5, mostrar contador
                        if (totalVariables > maxVisible) {
                            const remaining = totalVariables - maxVisible;
                            html += `<span class="notification-variables-count">+${remaining} más</span>`;
                        }

                        html += '</div>';
                    }

                    html += `
                            <div class="notification-time">
                                <i class="fas fa-clock"></i>
                                ${notif.fecha_formateada}
                            </div>
                        </div>
                    `;

                    div.innerHTML = html;
                    return div;
                }

                // ============================================
                // MODAL DE HISTORIAL COMPLETO
                // ============================================
                viewHistoryBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    abrirHistorialModal();
                });

                function abrirHistorialModal() {
                    historyModal.classList.add('show');
                    notificationsDropdown.classList.remove('show');
                    cargarHistorialCompleto();
                }

                function cerrarHistorialModal() {
                    historyModal.classList.remove('show');
                }

                historyModal.addEventListener('click', (e) => {
                    if (e.target === historyModal) {
                        cerrarHistorialModal();
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && historyModal.classList.contains('show')) {
                        cerrarHistorialModal();
                    }
                });

                // Cargar historial completo
                async function cargarHistorialCompleto() {
                    historyModalBody.innerHTML = '<div style="text-align: center; padding: 3rem;"><i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--neon-cyan);"></i></div>';

                    try {
                        const response = await fetch('../controller/CNotificaciones.php');
                        const data = await response.json();

                        console.log('Historial recibido:', data); // Para debugging

                        if (data.exito && data.notificaciones && data.notificaciones.length > 0) {
                            renderHistorialCompleto(data.notificaciones);
                        } else {
                            historyModalBody.innerHTML = `
                                <div class="history-empty">
                                    <i class="fas fa-check-circle"></i>
                                    <p>No hay errores registrados</p>
                                </div>
                            `;
                        }
                    } catch (error) {
                        console.error('Error al cargar historial:', error);
                        historyModalBody.innerHTML = `
                            <div class="history-empty">
                                <i class="fas fa-exclamation-triangle"></i>
                                <p>Error al cargar el historial</p>
                            </div>
                        `;
                    }
                }

                // Renderizar historial completo
                function renderHistorialCompleto(notificaciones) {
                    historyModalBody.innerHTML = '';

                    notificaciones.forEach(notif => {
                        const item = crearItemHistorial(notif);
                        historyModalBody.appendChild(item);
                    });
                }

                // Crear item de historial (versión completa)
                function crearItemHistorial(notif) {
                    const div = document.createElement('div');
                    div.className = 'history-item';

                    let iconClass = 'error-general';
                    let icon = 'fa-exclamation-circle';

                    switch (notif.error_tipo) {
                        case 'VARIABLES_FALTANTES':
                            iconClass = 'error-variables';
                            icon = 'fa-exclamation-triangle';
                            break;
                        case 'DUPLICADO':
                            iconClass = 'error-duplicado';
                            icon = 'fa-copy';
                            break;
                        case 'SIN_DATOS':
                            iconClass = 'error-datos';
                            icon = 'fa-file-excel';
                            break;
                    }

                    let html = `
                        <div class="history-item-header">
                            <div class="history-item-icon ${iconClass}">
                                <i class="fas ${icon}"></i>
                            </div>
                            <div class="history-item-info">
                                <div class="history-item-station">
                                    <i class="fas fa-broadcast-tower"></i>
                                    ${notif.nombre_estacion}
                                    <span style="color: var(--text-secondary); font-weight: 400; font-size: 0.85rem;">(${notif.codigo_estacion})</span>
                                </div>
                                <div class="history-item-error">${notif.error_descripcion}</div>
                                <div class="history-item-time">
                                    <i class="fas fa-clock"></i>
                                    ${notif.fecha_formateada}
                                </div>
                            </div>
                        </div>
                    `;

                    // Agregar TODAS las variables faltantes si existen
                    if (notif.variables_faltantes && notif.variables_faltantes.length > 0) {
                        html += `
                            <div class="history-item-variables">
                                <div class="history-item-variables-title">
                                    <i class="fas fa-list"></i>
                                    Variables no registradas (${notif.variables_faltantes.length})
                                </div>
                                <div class="history-variables-grid">
                        `;

                        notif.variables_faltantes.forEach(variable => {
                            html += `<div class="history-variable-tag">${variable}</div>`;
                        });

                        html += `
                                </div>
                            </div>
                        `;
                    }

                    div.innerHTML = html;
                    return div;
                }

                // Cargar notificaciones al inicio
                cargarNotificaciones();

                // Actualizar cada 30 segundos
                setInterval(cargarNotificaciones, 30000);
            </script>