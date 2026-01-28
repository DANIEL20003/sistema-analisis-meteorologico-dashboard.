<?php
// views/Administrador/VDatosAdmin.php
$titulo_pagina = 'Panel de Datos - Administrador';
$pagina_activa = 'estaciones';
$ruta_base = '../../';

// El header abre <main class="main-content"> y <div class="container">
include('headerAdmin.php');
?>

<style>
    /* ===== VARIABLES DE COLORES PROFESIONALES ===== */
    :root {
        --primary-cyan: #00ffff;
        --primary-blue: #0096ff;
        --accent-green: #00ff88;
        --accent-orange: #ff9500;
        --accent-red: #ff073a;
        --accent-purple: #9d4edd;
        --accent-yellow: #ffd60a;
        --accent-pink: #ff006e;
        --text-primary: #ffffff;
        --text-secondary: rgba(255, 255, 255, 0.8);
        --text-muted: rgba(255, 255, 255, 0.6);
        --bg-primary: rgba(10, 14, 26, 0.95);
        --bg-secondary: rgba(18, 24, 43, 0.92);
        --bg-tertiary: rgba(26, 34, 54, 0.88);
        --bg-card: rgba(255, 255, 255, 0.08);
        --border-primary: rgba(0, 255, 255, 0.3);
        --border-secondary: rgba(255, 255, 255, 0.1);
        --shadow-glow: 0 8px 32px rgba(0, 255, 255, 0.15);
        --shadow-card: 0 4px 20px rgba(0, 0, 0, 0.3);
    }

    /* ===== FONDO FIJO CHIMBORAZO ===== */
    .estaciones-page-wrapper {
        position: relative;
        min-height: auto;
    }

    .estaciones-fixed-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('../public/img/chimborazo.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        z-index: -1;
    }

    .estaciones-fixed-background::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(10, 14, 26, 0.92) 0%, rgba(18, 24, 43, 0.88) 100%);
        z-index: 1;
    }

    /* ===== LAYOUT PRINCIPAL ===== */
    .estaciones-container {
        position: relative;
        z-index: 3;
        max-width: 1400px;
        margin: 0 auto;
        padding: 1rem;
        min-height: auto;
    }

    /* ===== HEADER DE LA PÁGINA ===== */
    .estaciones-header {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 15px;
        padding: 2rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-glow);
        margin-bottom: 2rem;
        text-align: center;
    }

    .estaciones-title {
        font-size: 1.2rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .estaciones-subtitle {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin: 0;
    }

    /* ===== LAYOUT PRINCIPAL CON FILTROS Y CONTENIDO ===== */
    .main-layout {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 1.5rem;
        min-height: auto;
    }

    /* ===== PANEL DE FILTROS (IZQUIERDA) ===== */
    .filters-panel {
        background: var(--bg-primary);
        border: 1px solid rgba(0, 255, 255, 0.4);
        border-radius: 15px;
        padding: 1.5rem;
        backdrop-filter: blur(20px);
        box-shadow: 0 8px 32px rgba(0, 255, 255, 0.2);
        height: fit-content;
        position: sticky;
        top: 1rem;
    }

    .filters-header {
        text-align: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .filters-title {
        font-size: 0.85rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
    }

    .filter-section {
        margin-bottom: 1.5rem;
    }

    .filter-section:last-child {
        margin-bottom: 0;
    }

    .filter-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-label i {
        color: var(--primary-cyan);
        font-size: 0.9rem;
    }

    .filter-select {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        padding: 0.7rem;
        color: var(--text-primary);
        font-size: 0.75rem;
        transition: all 0.3s ease;
        font-family: inherit;
        width: 100%;
    }

    .filter-select:focus {
        outline: none;
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.15);
        /* Era 0.1 */
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.4);
        /* Era 0.2 */
    }

    .filter-select option {
        background: var(--bg-secondary);
        color: var(--text-primary);
        padding: 0.5rem;
    }

    /* ===== SELECTOR DE NIVEL ===== */
    .level-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.5rem;
    }

    .level-btn {
        padding: 0.6rem 0.4rem;
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
    }

    .level-btn:hover {
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.1);
        transform: translateY(-2px);
    }

    .level-btn.active {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        color: #0a0e1a;
        border-color: transparent;
        box-shadow: 0 4px 20px rgba(0, 255, 255, 0.3);
    }

    .level-btn i {
        font-size: 0.9rem;
    }

    /* ===== CONTENEDOR DE DATOS (DERECHA) ===== */
    .data-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* ===== SECCIÓN DE DATOS ===== */
    .data-section {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 15px;
        padding: 2rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-card);
    }

    .data-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .data-section-title {
        font-size: 1rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .data-section-title i {
        color: var(--primary-cyan);
        font-size: 1.1rem;
    }

    .data-badge {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 20px;
        padding: 0.4rem 0.8rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ===== GRID DE ARCHIVOS ===== */
    .files-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    .file-card {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 12px;
        padding: 1.5rem;
        transition: all 0.3s ease;
        cursor: pointer;
        min-height: 200px;
    }

    .file-card:hover {
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.05);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(0, 255, 255, 0.2);
    }

    .file-card-header {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        margin-bottom: 0.8rem;
    }

    .file-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        display: flex;
        align-items: center;
        justify-content: center;
        color: #0a0e1a;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .file-info {
        flex: 1;
        overflow: hidden;
    }

    .file-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.2rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-meta {
        font-size: 0.7rem;
        color: var(--text-muted);
    }

    .file-card-body {
        margin-bottom: 0.8rem;
    }

    .file-preview {
        font-size: 0.7rem;
        color: var(--text-secondary);
        line-height: 1.4;
        margin-bottom: 0.5rem;
    }

    .file-stats {
        display: flex;
        gap: 1rem;
        margin-top: 0.8rem;
    }

    .file-stat {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.7rem;
        color: var(--text-muted);
    }

    .file-stat i {
        color: var(--primary-cyan);
        font-size: 0.8rem;
    }

    .file-card-footer {
        display: flex;
        gap: 0.5rem;
        padding-top: 0.8rem;
        border-top: 1px solid var(--border-secondary);
    }

    .pagination-container {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-secondary);
    }

    .pagination-info {
        font-size: 0.8rem;
        color: var(--text-secondary);
        font-weight: 600;
    }

    .pagination-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .btn-pagination {
        padding: 0.6rem 1rem;
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        color: var(--text-primary);
        font-weight: 600;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-pagination:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .btn-pagination:not(:disabled):hover {
        background: rgba(0, 255, 255, 0.1);
        border-color: var(--primary-cyan);
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 255, 255, 0.2);
    }

    .btn-pagination i {
        font-size: 0.9rem;
    }



    .btn-file {
        flex: 1;
        padding: 0.6rem;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
    }

    .btn-download {
        background: linear-gradient(135deg, var(--accent-green), var(--primary-cyan));
        color: #0a0e1a;
        box-shadow: 0 2px 10px rgba(0, 255, 136, 0.3);
    }

    .btn-download:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0, 255, 136, 0.4);
    }

    .btn-preview {
        background: var(--bg-secondary);
        color: var(--text-primary);
        border: 1px solid var(--border-secondary);
    }

    .btn-preview:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--primary-cyan);
        transform: translateY(-1px);
    }

    /* ===== EMPTY STATE ===== */
    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-icon {
        font-size: 3rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .empty-title {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }

    .empty-text {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .main-layout {
            grid-template-columns: 1fr;
        }

        .filters-panel {
            position: static;
        }

        .files-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .estaciones-container {
            padding: 0.8rem;
        }

        .level-buttons {
            grid-template-columns: 1fr;
        }

        .files-grid {
            grid-template-columns: 1fr;
        }

        .pagination-container {
            flex-direction: column;
            gap: 1rem;
        }

        .data-section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.8rem;
        }

        .file-card-footer {
            flex-direction: column;
        }
    }

    /* ===== SCROLLBAR PERSONALIZADO ===== */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--bg-secondary);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--primary-cyan);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--primary-blue);
    }

    /* ===== MODAL DE VISTA PREVIA ===== */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(10px);
        z-index: 9999;
        animation: fadeIn 0.3s ease;
    }

    .modal-overlay.active {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    .modal-container {
        background: var(--bg-primary);
        border: 2px solid var(--primary-cyan);
        border-radius: 15px;
        width: 95%;
        max-width: 1400px;
        height: 90vh;
        display: flex;
        flex-direction: column;
        box-shadow: 0 20px 60px rgba(0, 255, 255, 0.3);
        animation: slideUp 0.3s ease;
        overflow: hidden;
    }

    @keyframes slideUp {
        from {
            transform: translateY(50px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .modal-header {
        padding: 0.75rem 1rem;
        border-bottom: 1px solid var(--border-secondary);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-secondary);
        border-radius: 15px 15px 0 0;
        flex-shrink: 0;
    }

    .modal-title {
        font-size: 0.75rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
        line-height: 1.2;
    }

    .modal-title i {
        color: var(--primary-cyan);
        font-size: 0.75rem;
    }

    .modal-close {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        color: var(--text-primary);
        font-size: 1.2rem;
    }

    .modal-close:hover {
        background: rgba(255, 0, 0, 0.2);
        border-color: #ff073a;
        transform: rotate(90deg);
    }

    .modal-body {
        display: flex;
        flex-direction: column;
        flex: 1;
        overflow: hidden;
        padding: 0.75rem 1rem 1rem 1rem;
        /* Era 1.5rem 2rem en todos lados */
    }

    .modal-info {
        display: flex;
        gap: 2rem;
        margin-bottom: 0.75rem;
        padding: 0.7rem 1rem;
        background: var(--bg-card);
        border-radius: 10px;
        border: 1px solid var(--border-secondary);
        flex-shrink: 0;
    }

    .modal-info-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .modal-info-item i {
        color: var(--primary-cyan);
        font-size: 1rem;
    }

    .modal-info-item strong {
        color: var(--text-primary);
        margin-left: 0.3rem;
    }

    /* CONTENEDOR CON DOBLE SCROLL INDEPENDIENTE */
    .table-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        border: 1px solid var(--border-secondary);
        border-radius: 10px;
        background: var(--bg-secondary);
        overflow: hidden;
        position: relative;
    }

    .table-scroll-container {
        flex: 1;
        overflow-x: auto;
        overflow-y: auto;
        position: relative;
    }

    .preview-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.75rem;
        min-width: max-content;
    }

    .preview-table thead {
        position: sticky;
        top: 0;
        z-index: 10;
        background: var(--bg-primary);
    }

    .preview-table th {
        padding: 0.8rem 1rem;
        text-align: left;
        font-weight: 700;
        color: var(--primary-cyan);
        border-bottom: 2px solid var(--primary-cyan);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.7rem;
        white-space: nowrap;
        background: var(--bg-primary);
    }

    .preview-table td {
        padding: 0.7rem 1rem;
        border-bottom: 1px solid var(--border-secondary);
        color: var(--text-secondary);
        white-space: nowrap;
    }

    .preview-table tbody tr {
        transition: background 0.2s ease;
    }

    .preview-table tbody tr:hover {
        background: rgba(0, 255, 255, 0.05);
    }

    .preview-table tbody tr:nth-child(even) {
        background: rgba(255, 255, 255, 0.02);
    }

    .preview-table tbody tr:nth-child(even):hover {
        background: rgba(0, 255, 255, 0.08);
    }

    /* SCROLLBAR PERSONALIZADO PARA LA TABLA */
    .table-scroll-container::-webkit-scrollbar {
        width: 12px;
        height: 12px;
    }

    .table-scroll-container::-webkit-scrollbar-track {
        background: var(--bg-tertiary);
        border-radius: 6px;
    }

    .table-scroll-container::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        border-radius: 6px;
        border: 2px solid var(--bg-tertiary);
    }

    .table-scroll-container::-webkit-scrollbar-thumb:hover {
        background: var(--primary-cyan);
    }

    .table-scroll-container::-webkit-scrollbar-corner {
        background: var(--bg-tertiary);
    }

    .loading-spinner {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        gap: 1rem;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 4px solid var(--border-secondary);
        border-top-color: var(--primary-cyan);
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .loading-text {
        color: var(--text-secondary);
        font-size: 0.9rem;
    }

    .error-message {
        padding: 2rem;
        text-align: center;
        color: var(--accent-red);
    }

    .error-message i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
    }

    @media (max-width: 768px) {
        .modal-overlay {
            padding: 1rem;
        }

        .modal-container {
            width: 100%;
            height: 95vh;
            border-radius: 10px;
        }

        .modal-header {
            padding: 1rem 1.5rem;
        }

        .modal-body {
            padding: 1rem 1.5rem;
        }

        .modal-info {
            flex-direction: column;
            gap: 0.8rem;
        }

        .preview-table {
            font-size: 0.7rem;
        }

        .preview-table th,
        .preview-table td {
            padding: 0.5rem 0.7rem;
        }

        .table-scroll-container::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
    }

    .modal-form-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        backdrop-filter: blur(15px);
        z-index: 10000;
        animation: fadeIn 0.3s ease;
        overflow-y: auto;
        padding: 2rem;
    }

    .modal-form-overlay.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-form-container {
        background: var(--bg-primary);
        border: 2px solid var(--primary-cyan);
        border-radius: 20px;
        width: 100%;
        max-width: 900px;
        box-shadow: 0 20px 60px rgba(0, 255, 255, 0.4);
        animation: slideUp 0.3s ease;
        overflow: hidden;
    }

    .form-header {
        background: var(--bg-secondary);
        padding: 1.2rem 1.5rem;
        border-bottom: 1px solid var(--border-primary);
        position: relative;
    }

    .form-logo-section {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .form-logo {
        width: 100px;
        height: auto;
        border-radius: 10px;
        background: white;
        padding: 0.5rem;
    }

    .form-header-text h2 {
        font-size: 0.95rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0 0 0.3rem 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form-header-text p {
        font-size: 0.7rem;
        color: var(--text-secondary);
        margin: 0;
    }

    .form-close-btn {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        color: var(--text-primary);
        font-size: 1rem;
    }

    .form-close-btn:hover {
        background: rgba(255, 0, 0, 0.2);
        border-color: #ff073a;
        transform: rotate(90deg);
    }

    .form-body {
        padding: 1.2rem 1.5rem;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .form-label .required {
        color: var(--accent-red);
    }

    .form-input {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        padding: 0.6rem 0.8rem;
        color: var(--text-primary);
        font-size: 0.7rem;
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.1);
        box-shadow: 0 0 20px rgba(0, 255, 255, 0.3);
    }

    .form-input::placeholder {
        color: var(--text-muted);
    }

    .terms-section {
        background: var(--bg-card);
        border: 1px solid var(--primary-cyan);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .terms-title {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--primary-cyan);
        margin-bottom: 0.6rem;
    }

    .terms-title i {
        font-size: 0.85rem;
    }


    .terms-list {
        list-style: none;
        padding: 0;
        margin: 0 0 1rem 0;
    }

    .terms-list li {
        font-size: 0.65rem;
        color: var(--text-secondary);
        padding: 0.3rem 0;
        padding-left: 1.2rem;
        position: relative;
        line-height: 1.4;
    }

    .terms-list li::before {
        content: "•";
        position: absolute;
        left: 0.5rem;
        color: var(--primary-cyan);
        font-weight: bold;
    }

    .terms-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 0.6rem;
        cursor: pointer;
        padding: 0.7rem;
        background: rgba(0, 255, 255, 0.05);
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .terms-checkbox:hover {
        background: rgba(0, 255, 255, 0.1);
    }

    .terms-checkbox input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        margin-top: 1px;
        accent-color: var(--primary-cyan);
    }

    .terms-checkbox label {
        font-size: 0.7rem;
        color: var(--text-primary);
        cursor: pointer;
        line-height: 1.4;
    }

    .form-actions {
        display: flex;
        gap: 0.8rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-secondary);
    }

    .btn-form {
        flex: 1;
        padding: 0.7rem 1.2rem;
        border: none;
        border-radius: 8px;
        font-weight: 700;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-cancel {
        background: var(--bg-card);
        color: var(--text-primary);
        border: 1px solid var(--border-secondary);
    }

    .btn-cancel:hover {
        background: rgba(255, 0, 0, 0.2);
        border-color: var(--accent-red);
        transform: translateY(-2px);
    }

    .btn-download-form {
        background: linear-gradient(135deg, var(--accent-green), var(--primary-cyan));
        color: #0a0e1a;
        box-shadow: 0 4px 20px rgba(0, 255, 136, 0.4);
    }

    .btn-download-form:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 25px rgba(0, 255, 136, 0.5);
    }

    .btn-download-form:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    @media (max-width: 768px) {
        .modal-form-overlay {
            padding: 1rem;
        }

        .form-header {
            padding: 1.5rem;
        }

        .form-logo-section {
            flex-direction: column;
            text-align: center;
        }

        .form-body {
            padding: 1.5rem;
        }

        .form-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .form-actions {
            flex-direction: column;
        }

        .form-close-btn {
            top: 1rem;
            right: 1rem;
        }
    }

    .error-message {
        color: var(--accent-red);
        font-size: 0.65rem;
        margin-top: 0.3rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        animation: slideDown 0.3s ease;
    }

    .error-message::before {
        content: "⚠";
        font-size: 0.75rem;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .form-input.error {
        border-color: var(--accent-red) !important;
        background: rgba(255, 7, 58, 0.05);
    }

    @keyframes slideInRight {
        from {
            transform: translateX(400px);
            opacity: 0;
        }

        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0);
            opacity: 1;
        }

        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }

    /* AGREGAR AL FINAL DE LOS ESTILOS */

    .btn-delete {
        background: linear-gradient(135deg, var(--accent-red), #c70039);
        color: #ffffff;
        box-shadow: 0 2px 10px rgba(255, 7, 58, 0.3);
    }

    .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(255, 7, 58, 0.5);
    }

    /* Modal de confirmación */
    .modal-confirm-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        backdrop-filter: blur(15px);
        z-index: 10001;
        animation: fadeIn 0.3s ease;
    }

    .modal-confirm-overlay.active {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }

    .modal-confirm-container {
        background: var(--bg-primary);
        border: 2px solid var(--accent-red);
        border-radius: 15px;
        width: 100%;
        max-width: 500px;
        box-shadow: 0 20px 60px rgba(255, 7, 58, 0.4);
        animation: slideUp 0.3s ease;
        overflow: hidden;
    }

    .modal-confirm-header {
        background: rgba(255, 7, 58, 0.1);
        padding: 1.2rem 1.5rem;
        border-bottom: 1px solid var(--accent-red);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .modal-confirm-icon {
        width: 50px;
        height: 50px;
        background: var(--accent-red);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.1);
        }
    }

    .modal-confirm-title {
        flex: 1;
    }

    .modal-confirm-title h3 {
        font-size: 1rem;
        font-weight: 700;
        color: var(--accent-red);
        margin: 0 0 0.3rem 0;
        text-transform: uppercase;
    }

    .modal-confirm-title p {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin: 0;
    }

    .modal-confirm-body {
        padding: 1.5rem;
    }

    .confirm-file-info {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1rem;
    }

    .confirm-file-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--primary-cyan);
        margin-bottom: 0.5rem;
        word-break: break-all;
    }

    .confirm-file-meta {
        font-size: 0.7rem;
        color: var(--text-muted);
    }

    .confirm-warning {
        background: rgba(255, 149, 0, 0.1);
        border: 1px solid var(--accent-orange);
        border-radius: 8px;
        padding: 0.8rem;
        display: flex;
        gap: 0.8rem;
        align-items: flex-start;
    }

    .confirm-warning i {
        color: var(--accent-orange);
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .confirm-warning-text {
        font-size: 0.75rem;
        color: var(--text-secondary);
        line-height: 1.4;
    }

    .modal-confirm-actions {
        display: flex;
        gap: 0.8rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-secondary);
    }
</style>

<!-- Fondo fijo del Chimborazo -->
<div class="estaciones-fixed-background"></div>

<div class="estaciones-page-wrapper">
    <div class="estaciones-container">



        <!-- Layout principal -->
        <div class="main-layout">

            <!-- Panel de filtros (izquierda) -->
            <div class="filters-panel">
                <div class="filters-header">
                    <h2 class="filters-title">Filtros - Datos Meteorológicos</h2>
                </div>

                <!-- Filtro de Estación -->
                <div class="filter-section">
                    <label class="filter-label">
                        <i class="fas fa-map-marker-alt"></i>
                        Estación Meteorológica
                    </label>
                    <select class="filter-select" id="filterEstacion">
                        <option value="">Cargando estaciones...</option>
                    </select>
                </div>

                <!-- Filtro de Nivel -->
                <div class="filter-section">
                    <label class="filter-label">
                        <i class="fas fa-layer-group"></i>
                        Nivel de Datos
                    </label>
                    <div class="level-buttons" id="levelSelector">
                        <button type="button" class="level-btn active" data-nivel="L0">
                            <i class="fas fa-wind"></i>
                            <span>L0</span>
                        </button>
                        <button type="button" class="level-btn" data-nivel="L1">
                            <i class="fas fa-cloud-sun"></i>
                            <span>L1</span>
                        </button>
                        <button type="button" class="level-btn" data-nivel="L2">
                            <i class="fas fa-chart-line"></i>
                            <span>L2</span>
                        </button>
                    </div>
                </div>

                <!-- Filtro de Año -->
                <div class="filter-section">
                    <label class="filter-label">
                        <i class="fas fa-calendar-alt"></i>
                        Año
                    </label>
                    <select class="filter-select" id="filterAnio" disabled>
                        <option value="">Seleccione nivel primero</option>
                    </select>
                </div>
            </div>

            <!-- Contenedor de datos (derecha) -->
            <div class="data-content">

                <!-- Sección de Datos Limpios -->
                <div class="data-section">
                    <div class="data-section-header">
                        <h3 class="data-section-title">
                            <i class="fas fa-check-circle"></i>
                            Datos Limpios
                        </h3>
                        <span class="data-badge" id="badgeLimpios">0 archivos</span>
                    </div>

                    <!-- Grid de archivos -->
                    <div class="files-grid" id="cleanFilesGrid">
                        <div class="empty-state" style="grid-column: 1/-1;">
                            <div class="empty-icon">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <div class="empty-title">No hay datos limpios disponibles</div>
                            <div class="empty-text">Selecciona una estación y nivel para ver los archivos</div>
                        </div>
                    </div>

                    <!-- Paginación Limpios -->
                    <div class="pagination-container" id="paginationLimpios" style="display: none;">
                        <div class="pagination-buttons">
                            <button class="btn-pagination" id="btnPrevLimpios" onclick="cambiarPaginaLimpios(-1)">
                                <i class="fas fa-chevron-left"></i>
                                <span>Anterior</span>
                            </button>
                            <button class="btn-pagination" id="btnNextLimpios" onclick="cambiarPaginaLimpios(1)">
                                <span>Siguiente</span>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="pagination-info" id="infoLimpios">
                            Página 1 de 1
                        </div>
                    </div>
                </div>

                <!-- Sección de Datos Crudos -->
                <div class="data-section">
                    <div class="data-section-header">
                        <h3 class="data-section-title">
                            <i class="fas fa-file-csv"></i>
                            Datos Crudos
                        </h3>
                        <span class="data-badge" id="badgeCrudos">0 archivos</span>
                    </div>

                    <!-- Grid de archivos -->
                    <div class="files-grid" id="rawFilesGrid">
                        <div class="empty-state" style="grid-column: 1/-1;">
                            <div class="empty-icon">
                                <i class="fas fa-folder-open"></i>
                            </div>
                            <div class="empty-title">No hay datos crudos disponibles</div>
                            <div class="empty-text">Selecciona una estación y nivel para ver los archivos</div>
                        </div>
                    </div>

                    <!-- Paginación Crudos -->
                    <div class="pagination-container" id="paginationCrudos" style="display: none;">
                        <div class="pagination-buttons">
                            <button class="btn-pagination" id="btnPrevCrudos" onclick="cambiarPaginaCrudos(-1)">
                                <i class="fas fa-chevron-left"></i>
                                <span>Anterior</span>
                            </button>
                            <button class="btn-pagination" id="btnNextCrudos" onclick="cambiarPaginaCrudos(1)">
                                <span>Siguiente</span>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="pagination-info" id="infoCrudos">
                            Página 1 de 1
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- ============================================ -->
        <!-- SEPARADOR VISUAL ENTRE SECCIONES -->
        <!-- ============================================ -->
        <div style="height: 4rem; border-top: 2px solid rgba(0, 255, 255, 0.2); margin: 2rem 0;"></div>

        <!-- ============================================ -->
        <!-- SEGUNDO LAYOUT: TORRES (INDEPENDIENTE) -->
        <!-- ============================================ -->
        <div class="main-layout">

            <!-- Panel de filtros para Torres (izquierda) -->
            <div class="filters-panel">
                <div class="filters-header">
                    <h2 class="filters-title">Filtros - Torres</h2>
                </div>

                <!-- Filtro de Estación Torres -->
                <div class="filter-section">
                    <label class="filter-label">
                        <i class="fas fa-map-marker-alt"></i>
                        Estación
                    </label>
                    <select class="filter-select" id="filterEstacionTorres">
                        <option value="">Cargando estaciones...</option>
                    </select>
                </div>

                <!-- Filtro de Nivel Torres -->
                <div class="filter-section">
                    <label class="filter-label">
                        <i class="fas fa-layer-group"></i>
                        Nivel de Datos
                    </label>
                    <div class="level-buttons" id="levelSelectorTorres">
                        <button type="button" class="level-btn active" data-nivel="L0">
                            <i class="fas fa-wind"></i>
                            <span>L0</span>
                        </button>
                        <button type="button" class="level-btn" data-nivel="L1">
                            <i class="fas fa-cloud-sun"></i>
                            <span>L1</span>
                        </button>
                        <button type="button" class="level-btn" data-nivel="L2">
                            <i class="fas fa-chart-line"></i>
                            <span>L2</span>
                        </button>
                    </div>
                </div>

                <!-- ✅ AGREGAR: Filtro de Año Torres -->
                <div class="filter-section">
                    <label class="filter-label">
                        <i class="fas fa-calendar-alt"></i>
                        Año
                    </label>
                    <select class="filter-select" id="filterAnioTorres" disabled>
                        <option value="">Seleccione nivel primero</option>
                    </select>
                </div>
            </div>

            <!-- Contenedor de datos Torres (derecha) -->
            <div class="data-content">

                <!-- Sección de Torres -->
                <div class="data-section">
                    <div class="data-section-header">
                        <h3 class="data-section-title">
                            <i class="fas fa-tower-broadcast"></i>
                            Torres - Datos de Referencia
                        </h3>
                        <span class="data-badge" id="badgeTorres">0 archivos</span>
                    </div>

                    <!-- Grid de archivos Torres -->
                    <div class="files-grid" id="torresFilesGrid">
                        <div class="empty-state" style="grid-column: 1/-1;">
                            <div class="empty-icon">
                                <i class="fas fa-tower-broadcast"></i>
                            </div>
                            <div class="empty-title">No hay datos de torres disponibles</div>
                            <div class="empty-text">Selecciona una estación y nivel para ver los archivos</div>
                        </div>
                    </div>

                    <!-- Paginación Torres -->
                    <div class="pagination-container" id="paginationTorres" style="display: none;">
                        <div class="pagination-buttons">
                            <button class="btn-pagination" id="btnPrevTorres" onclick="cambiarPaginaTorres(-1)">
                                <i class="fas fa-chevron-left"></i>
                                <span>Anterior</span>
                            </button>
                            <button class="btn-pagination" id="btnNextTorres" onclick="cambiarPaginaTorres(1)">
                                <span>Siguiente</span>
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        <div class="pagination-info" id="infoTorres">
                            Página 1 de 1
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="modal-form-overlay" id="modalFormularioDescarga">
    <div class="modal-form-container">
        <div class="form-header">
            <div class="form-logo-section">
                <img src="../public/img/logo-geaa-espoch.jpg" alt="GEAA ESPOCH" class="form-logo">
                <div class="form-header-text">
                    <h2>Formulario de Descarga</h2>
                    <p>Complete la información para descargar los datos</p>
                </div>
            </div>
            <button class="form-close-btn" onclick="cerrarFormularioDescarga()">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div class="form-body">
            <form id="formDescarga" onsubmit="return false;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            NOMBRE COMPLETO <span class="required">*</span>
                        </label>
                        <input type="text" class="form-input" id="nombreCompleto" placeholder="Ej: Juan Pérez García"
                            required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            INSTITUCIÓN <span class="required">*</span>
                        </label>
                        <input type="text" class="form-input" id="institucion" placeholder="Ej: ESPOCH" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            CÉDULA/PASAPORTE <span class="required">*</span>
                        </label>
                        <input type="text" class="form-input" id="cedula" placeholder="Ej: 1234567890" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            MOTIVO DE DESCARGA <span class="required">*</span>
                        </label>
                        <input type="text" class="form-input" id="motivoDescarga"
                            placeholder="Ej: Proyecto de investigación" required>
                    </div>
                </div>

                <div class="terms-section">
                    <div class="terms-title">
                        <i class="fas fa-shield-alt"></i>
                        <span>Declaración de Términos</span>
                    </div>

                    <p style="font-size: 0.7rem; color: var(--text-secondary); margin-bottom: 0.6rem;">
                        Al descargar estos datos, me comprometo a:
                    </p>

                    <ul class="terms-list">
                        <li>Citar correctamente al GEAA (Grupo de Energías Alternativas y Ambiente) - ESPOCH</li>
                        <li>Referenciar el proyecto de investigación en cualquier publicación</li>
                        <li>Utilizar los datos únicamente con fines educativos y de investigación</li>
                        <li>No comercializar ni redistribuir los datos sin autorización previa</li>
                    </ul>

                    <div class="terms-checkbox">
                        <input type="checkbox" id="aceptoTerminos" required>
                        <label for="aceptoTerminos">
                            Acepto los términos y condiciones, y me comprometo a utilizar los datos de manera ética
                            y responsable.
                        </label>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-form btn-cancel" onclick="cerrarFormularioDescarga()">
                        <i class="fas fa-times"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="button" class="btn-form btn-download-form" id="btnDescargarForm"
                        onclick="procesarDescarga()">
                        <i class="fas fa-download"></i>
                        <span>Descargar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>

<script>
    // ========================================
    // VARIABLES GLOBALES
    // ========================================
    let currentEstacion = '';
    let currentNivel = 'L0';
    let currentAnio = '';
    let estaciones = [];
    let currentEstacionTorres = '';
    let currentNivelTorres = 'L0';
    let todosTorres = [];
    let paginaActualTorres = 1;
    let currentAnioTorres = '';

    // Datos y paginación
    let todosLosLimpios = [];
    let todosLosCrudos = [];
    let paginaActualLimpios = 1;
    let paginaActualCrudos = 1;
    const ARCHIVOS_POR_PAGINA = 10;

    // ========================================
    // INICIALIZACIÓN
    // ========================================
    document.addEventListener('DOMContentLoaded', function () {
        loadEstaciones();
        setupEventListeners();
    });

    document.addEventListener('DOMContentLoaded', function () {
        const inputs = ['nombreCompleto', 'institucion', 'cedula', 'motivoDescarga'];
        inputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', validarFormulario);
            }
        });

        const checkbox = document.getElementById('aceptoTerminos');
        if (checkbox) {
            checkbox.addEventListener('change', validarFormulario);
        }
    });

    // ========================================
    // GESTIÓN DE TORRES
    // ========================================

   async function loadArchivosTorres() {
    if (!currentEstacionTorres) {
        mostrarEstadoVacio('torresFilesGrid', 'Seleccione una estación');
        document.getElementById('badgeTorres').textContent = '0 archivos';
        document.getElementById('paginationTorres').style.display = 'none';
        return;
    }

    try {
        console.log(`🗼 Cargando torres: ${currentEstacionTorres} - ${currentNivelTorres} - Año: ${currentAnioTorres || 'Todos'}`);

        const formData = new FormData();
        formData.append('action', 'get_archivos_torres');
        formData.append('estacion', currentEstacionTorres);
        formData.append('nivel', currentNivelTorres);

        // ✅ AGREGAR filtro de año si existe
        if (currentAnioTorres) {
            formData.append('anio', currentAnioTorres);
        }

        const response = await fetch('../controller/CEstacion.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        console.log('📦 Torres recibidas:', data);

        if (data.success && data.data) {
            todosTorres = data.data.torres || [];
            paginaActualTorres = 1;
            renderPaginaTorres();
            document.getElementById('badgeTorres').textContent =
                `${todosTorres.length} archivo${todosTorres.length !== 1 ? 's' : ''}`;
        } else {
            showError(data.message || 'Error al cargar torres');
        }

    } catch (error) {
        console.error('❌ Error:', error);
        showError('Error de conexión');
    }
}

    function renderPaginaTorres() {
        const inicio = (paginaActualTorres - 1) * ARCHIVOS_POR_PAGINA;
        const fin = inicio + ARCHIVOS_POR_PAGINA;
        const archivosPagina = todosTorres.slice(inicio, fin);

        const grid = document.getElementById('torresFilesGrid');
        grid.innerHTML = '';

        if (!archivosPagina || archivosPagina.length === 0) {
            mostrarEstadoVacio('torresFilesGrid',
                `No hay datos de torres en ${currentEstacionTorres} - ${currentNivelTorres}`);
            document.getElementById('paginationTorres').style.display = 'none';
            return;
        }

        archivosPagina.forEach(archivo => {
            const card = crearTarjetaArchivo(archivo, true);
            grid.appendChild(card);
        });

        actualizarControlesPaginacion('Torres', todosTorres.length, paginaActualTorres);
    }

    function cambiarPaginaTorres(direccion) {
        const totalPaginas = Math.ceil(todosTorres.length / ARCHIVOS_POR_PAGINA);
        const nuevaPagina = paginaActualTorres + direccion;

        if (nuevaPagina >= 1 && nuevaPagina <= totalPaginas) {
            paginaActualTorres = nuevaPagina;
            renderPaginaTorres();
            document.getElementById('torresFilesGrid').scrollIntoView({
                behavior: 'smooth',
                block: 'nearest'
            });
        }
    }

    // ========================================
    // ELIMINACIÓN DE ARCHIVOS
    // ========================================

    function confirmarEliminacion(ruta, nombre, tipo) {
        const modal = document.createElement('div');
        modal.className = 'modal-confirm-overlay';
        modal.id = 'modalConfirmDelete';

        modal.innerHTML = `
        <div class="modal-confirm-container">
            <div class="modal-confirm-header">
                <div class="modal-confirm-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="modal-confirm-title">
                    <h3>Confirmar Eliminación</h3>
                    <p>Esta acción no se puede deshacer</p>
                </div>
            </div>
            <div class="modal-confirm-body">
                <div class="confirm-file-info">
                    <div class="confirm-file-name">
                        <i class="fas fa-file-csv"></i> ${nombre}
                    </div>
                    <div class="confirm-file-meta">
                        Tipo: ${tipo} | Ruta: ${ruta}
                    </div>
                </div>

                <div class="confirm-warning">
                    <i class="fas fa-info-circle"></i>
                    <div class="confirm-warning-text">
                        <strong>Advertencia:</strong> El archivo será eliminado permanentemente del servidor. 
                        Esta acción no se puede revertir. Asegúrese de que desea continuar.
                    </div>
                </div>

                <div class="modal-confirm-actions">
                    <button type="button" class="btn-form btn-cancel" onclick="cerrarModalConfirmacion()">
                        <i class="fas fa-times"></i>
                        <span>Cancelar</span>
                    </button>
                    <button type="button" class="btn-form btn-delete" onclick="eliminarArchivo('${ruta}', '${nombre}', '${tipo}')">
                        <i class="fas fa-trash-alt"></i>
                        <span>Eliminar Archivo</span>
                    </button>
                </div>
            </div>
        </div>
    `;

        document.body.appendChild(modal);
        setTimeout(() => modal.classList.add('active'), 10);

        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                cerrarModalConfirmacion();
            }
        });
    }

    function cerrarModalConfirmacion() {
        const modal = document.getElementById('modalConfirmDelete');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => modal.remove(), 300);
        }
    }

    async function eliminarArchivo(ruta, nombre, tipo) {
        const btnEliminar = event.target.closest('.btn-delete');
        const textoOriginal = btnEliminar.innerHTML;
        btnEliminar.disabled = true;
        btnEliminar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Eliminando...</span>';

        try {
            console.log('🗑️ Eliminando archivo:', ruta);

            const formData = new FormData();
            formData.append('action', 'eliminar_archivo');
            formData.append('ruta', ruta);

            const response = await fetch('../controller/CEstacion.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            console.log('📥 Respuesta eliminación:', data);

            if (data.success) {
                mostrarMensajeExito('Archivo eliminado correctamente');
                cerrarModalConfirmacion();

                // Recargar archivos según el tipo
                if (tipo === 'Torres') {
                    loadArchivosTorres();
                } else {
                    loadArchivos();
                }
            } else {
                throw new Error(data.message || 'Error al eliminar el archivo');
            }

        } catch (error) {
            console.error('❌ Error al eliminar:', error);
            alert('Error al eliminar el archivo: ' + error.message);
            btnEliminar.disabled = false;
            btnEliminar.innerHTML = textoOriginal;
        }
    }


    // ========================================
    // CARGA DE ESTACIONES
    // ========================================
    async function loadEstaciones() {
        try {
            console.log('🔍 Cargando estaciones...');

            const formData = new FormData();
            formData.append('action', 'get_estaciones');

            const response = await fetch('../controller/CEstacion.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('📦 Estaciones recibidas:', data);

            if (data.success && data.data && data.data.estaciones) {
                estaciones = data.data.estaciones;
                populateEstacionesSelect(estaciones);
                console.log('✅ Estaciones cargadas:', estaciones);
            } else {
                showError('No se encontraron estaciones disponibles');
            }

        } catch (error) {
            console.error('❌ Error al cargar estaciones:', error);
            showError('Error de conexión: ' + error.message);
        }
    }

    // ========================================
    // CARGA DE AÑOS DISPONIBLES
    // ========================================
    async function loadAniosDisponibles() {
        if (!currentEstacion || !currentNivel) {
            const selectAnio = document.getElementById('filterAnio');
            selectAnio.disabled = true;
            selectAnio.innerHTML = '<option value="">Seleccione estación y nivel primero</option>';
            return;
        }

        try {
            console.log(`📅 Cargando años disponibles: ${currentEstacion} - ${currentNivel}`);

            const formData = new FormData();
            formData.append('action', 'get_anios');
            formData.append('estacion', currentEstacion);
            formData.append('nivel', currentNivel);

            const response = await fetch('../controller/CEstacion.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            console.log('📦 Años recibidos:', data);

            if (data.success && data.data && data.data.anios) {
                populateAniosSelect(data.data.anios);
                console.log('✅ Años cargados:', data.data.anios);
            } else {
                showError('No se encontraron años disponibles');
                const selectAnio = document.getElementById('filterAnio');
                selectAnio.disabled = true;
                selectAnio.innerHTML = '<option value="">Sin años disponibles</option>';
            }

        } catch (error) {
            console.error('❌ Error al cargar años:', error);
            showError('Error de conexión: ' + error.message);
        }
    }
    // ========================================
// CARGA DE AÑOS DISPONIBLES PARA TORRES
// ========================================
async function loadAniosDisponiblesTorres() {
    if (!currentEstacionTorres || !currentNivelTorres) {
        const selectAnio = document.getElementById('filterAnioTorres');
        selectAnio.disabled = true;
        selectAnio.innerHTML = '<option value="">Seleccione estación y nivel primero</option>';
        return;
    }

    try {
        console.log(`📅 Cargando años Torres: ${currentEstacionTorres} - ${currentNivelTorres}`);

        const formData = new FormData();
        formData.append('action', 'get_anios_torres');
        formData.append('estacion', currentEstacionTorres);
        formData.append('nivel', currentNivelTorres);

        const response = await fetch('../controller/CEstacion.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        console.log('📦 Años Torres recibidos:', data);

        if (data.success && data.data && data.data.anios) {
            populateAniosSelectTorres(data.data.anios);
            console.log('✅ Años Torres cargados:', data.data.anios);
        } else {
            showError('No se encontraron años disponibles para Torres');
            const selectAnio = document.getElementById('filterAnioTorres');
            selectAnio.disabled = true;
            selectAnio.innerHTML = '<option value="">Sin años disponibles</option>';
        }

    } catch (error) {
        console.error('❌ Error al cargar años Torres:', error);
        showError('Error de conexión: ' + error.message);
    }
}

function populateAniosSelectTorres(aniosArray) {
    const select = document.getElementById('filterAnioTorres');

    // Limpiar opciones anteriores
    select.innerHTML = '';

    if (aniosArray.length === 0) {
        select.disabled = true;
        select.innerHTML = '<option value="">Sin años disponibles</option>';
        return;
    }

    // Habilitar select
    select.disabled = false;

    // Agregar opción "Todos los años"
    const optionTodos = document.createElement('option');
    optionTodos.value = '';
    optionTodos.textContent = 'Todos los años';
    select.appendChild(optionTodos);

    // Agregar años
    aniosArray.forEach(anio => {
        const option = document.createElement('option');
        option.value = anio;
        option.textContent = anio;
        select.appendChild(option);
    });

    // Resetear año actual
    currentAnioTorres = '';
}


    function populateAniosSelect(aniosArray) {
        const select = document.getElementById('filterAnio');

        // Limpiar opciones anteriores
        select.innerHTML = '';

        if (aniosArray.length === 0) {
            select.disabled = true;
            select.innerHTML = '<option value="">Sin años disponibles</option>';
            return;
        }

        // Habilitar select
        select.disabled = false;

        // Agregar opción "Todos los años"
        const optionTodos = document.createElement('option');
        optionTodos.value = '';
        optionTodos.textContent = 'Todos los años';
        select.appendChild(optionTodos);

        // Agregar años
        aniosArray.forEach(anio => {
            const option = document.createElement('option');
            option.value = anio;
            option.textContent = anio;
            select.appendChild(option);
        });

        // Resetear año actual
        currentAnio = '';
    }

    function populateEstacionesSelect(estacionesArray) {
        // Select principal
        const select = document.getElementById('filterEstacion');
        while (select.children.length > 1) {
            select.removeChild(select.lastChild);
        }
        select.children[0].textContent = 'Seleccione una estación';

        estacionesArray.forEach(estacion => {
            const option = document.createElement('option');
            option.value = estacion;
            option.textContent = estacion;
            select.appendChild(option);
        });

        // AGREGAR: Select de Torres
        const selectTorres = document.getElementById('filterEstacionTorres');
        while (selectTorres.children.length > 1) {
            selectTorres.removeChild(selectTorres.lastChild);
        }
        selectTorres.children[0].textContent = 'Seleccione una estación';

        estacionesArray.forEach(estacion => {
            const option = document.createElement('option');
            option.value = estacion;
            option.textContent = estacion;
            selectTorres.appendChild(option);
        });
    }

    // ========================================
    // CARGA DE ARCHIVOS
    // ========================================
    async function loadArchivos() {
        if (!currentEstacion) {
            mostrarEstadoVacio('cleanFilesGrid', 'Seleccione una estación');
            mostrarEstadoVacio('rawFilesGrid', 'Seleccione una estación');
            actualizarContadores(0, 0);
            ocultarPaginacion();
            return;
        }

        try {
            console.log(`📥 Cargando archivos: ${currentEstacion} - ${currentNivel} - Año: ${currentAnio || 'Todos'}`);

            const formData = new FormData();
            formData.append('action', 'get_archivos');
            formData.append('estacion', currentEstacion);
            formData.append('nivel', currentNivel);

            // ✅ AGREGAR filtro de año si existe
            if (currentAnio) {
                formData.append('anio', currentAnio);
            }

            const response = await fetch('../controller/CEstacion.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();
            console.log('📦 Archivos recibidos:', data);

            if (data.success && data.data) {
                // Guardar todos los archivos
                todosLosLimpios = data.data.limpios || [];
                todosLosCrudos = data.data.crudos || [];

                // Resetear páginas
                paginaActualLimpios = 1;
                paginaActualCrudos = 1;

                // Renderizar primera página
                renderPaginaLimpios();
                renderPaginaCrudos();

                actualizarContadores(todosLosLimpios.length, todosLosCrudos.length);
            } else {
                showError(data.message || 'Error al cargar archivos');
            }

        } catch (error) {
            console.error('❌ Error:', error);
            showError('Error de conexión');
        }
    }

    // ========================================
    // PAGINACIÓN - LIMPIOS
    // ========================================
    function renderPaginaLimpios() {
        const inicio = (paginaActualLimpios - 1) * ARCHIVOS_POR_PAGINA;
        const fin = inicio + ARCHIVOS_POR_PAGINA;
        const archivosPagina = todosLosLimpios.slice(inicio, fin);

        renderArchivos(archivosPagina, 'cleanFilesGrid');
        actualizarControlesPaginacion('Limpios', todosLosLimpios.length, paginaActualLimpios);
    }

    function cambiarPaginaLimpios(direccion) {
        const totalPaginas = Math.ceil(todosLosLimpios.length / ARCHIVOS_POR_PAGINA);
        const nuevaPagina = paginaActualLimpios + direccion;

        if (nuevaPagina >= 1 && nuevaPagina <= totalPaginas) {
            paginaActualLimpios = nuevaPagina;
            renderPaginaLimpios();

            // Scroll suave al inicio de la sección
            document.getElementById('cleanFilesGrid').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // ========================================
    // PAGINACIÓN - CRUDOS
    // ========================================
    function renderPaginaCrudos() {
        const inicio = (paginaActualCrudos - 1) * ARCHIVOS_POR_PAGINA;
        const fin = inicio + ARCHIVOS_POR_PAGINA;
        const archivosPagina = todosLosCrudos.slice(inicio, fin);

        renderArchivos(archivosPagina, 'rawFilesGrid');
        actualizarControlesPaginacion('Crudos', todosLosCrudos.length, paginaActualCrudos);
    }

    function cambiarPaginaCrudos(direccion) {
        const totalPaginas = Math.ceil(todosLosCrudos.length / ARCHIVOS_POR_PAGINA);
        const nuevaPagina = paginaActualCrudos + direccion;

        if (nuevaPagina >= 1 && nuevaPagina <= totalPaginas) {
            paginaActualCrudos = nuevaPagina;
            renderPaginaCrudos();

            // Scroll suave al inicio de la sección
            document.getElementById('rawFilesGrid').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    // ========================================
    // CONTROLES DE PAGINACIÓN
    // ========================================
    function actualizarControlesPaginacion(tipo, totalArchivos, paginaActual) {
        const totalPaginas = Math.ceil(totalArchivos / ARCHIVOS_POR_PAGINA);

        if (totalArchivos <= ARCHIVOS_POR_PAGINA) {
            // Ocultar paginación si hay 10 o menos archivos
            document.getElementById(`pagination${tipo}`).style.display = 'none';
            return;
        }

        // Mostrar paginación
        document.getElementById(`pagination${tipo}`).style.display = 'flex';

        // Actualizar botones
        const btnPrev = document.getElementById(`btnPrev${tipo}`);
        const btnNext = document.getElementById(`btnNext${tipo}`);

        btnPrev.disabled = (paginaActual === 1);
        btnNext.disabled = (paginaActual === totalPaginas);

        // Actualizar info
        document.getElementById(`info${tipo}`).textContent =
            `Página ${paginaActual} de ${totalPaginas} (${totalArchivos} archivos)`;
    }

    function ocultarPaginacion() {
        document.getElementById('paginationLimpios').style.display = 'none';
        document.getElementById('paginationCrudos').style.display = 'none';
    }

    // ========================================
    // RENDERIZADO
    // ========================================
    function renderArchivos(archivos, gridId) {
        const grid = document.getElementById(gridId);
        grid.innerHTML = '';

        if (!archivos || archivos.length === 0) {
            const tipo = gridId === 'cleanFilesGrid' ? 'limpios' : 'crudos';
            mostrarEstadoVacio(gridId, `No hay datos ${tipo} en ${currentEstacion} - ${currentNivel}`);
            return;
        }

        archivos.forEach(archivo => {
            const card = crearTarjetaArchivo(archivo);
            grid.appendChild(card);
        });
    }

    function crearTarjetaArchivo(archivo, esAdmin = true) {
        const card = document.createElement('div');
        card.className = 'file-card';

        card.innerHTML = `
        <div class="file-card-header">
            <div class="file-icon">
                <i class="fas fa-file-csv"></i>
            </div>
            <div class="file-info">
                <div class="file-name" title="${archivo.nombre}">${archivo.nombre}</div>
                <div class="file-meta">${archivo.fecha_formateada}</div>
            </div>
        </div>
        <div class="file-card-body">
            <div class="file-stat">
                <i class="fas fa-hdd"></i>
                <span>${archivo.tamanio_formateado}</span>
            </div>
        </div>
        <div class="file-card-footer">
            ${esAdmin ? `
                <button class="btn-file btn-delete" onclick="confirmarEliminacion('${archivo.ruta_relativa}', '${archivo.nombre}', '${archivo.tipo}')">
                    <i class="fas fa-trash-alt"></i>
                    <span>Eliminar</span>
                </button>
            ` : ''}
            <button class="btn-file btn-download" onclick="descargarArchivo('${archivo.ruta_relativa}', '${archivo.nombre}')">
                <i class="fas fa-download"></i>
                <span>Descargar</span>
            </button>
            <button class="btn-file btn-preview" onclick="previsualizarArchivo('${archivo.ruta_relativa}')">
                <i class="fas fa-eye"></i>
                <span>Vista previa</span>
            </button>
        </div>
    `;

        return card;
    }

    function mostrarEstadoVacio(gridId, mensaje) {
        const grid = document.getElementById(gridId);
        grid.innerHTML = `
            <div class="empty-state" style="grid-column: 1/-1;">
                <div class="empty-icon">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="empty-title">No hay archivos disponibles</div>
                <div class="empty-text">${mensaje}</div>
            </div>
        `;
    }

    function actualizarContadores(limpios, crudos) {
        document.getElementById('badgeLimpios').textContent = `${limpios} archivo${limpios !== 1 ? 's' : ''}`;
        document.getElementById('badgeCrudos').textContent = `${crudos} archivo${crudos !== 1 ? 's' : ''}`;
    }

    // ========================================
    // EVENT LISTENERS
    // ========================================
    function setupEventListeners() {
        // Listener para botones de nivel
        document.querySelectorAll('.level-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                setNivel(this.dataset.nivel);
            });
        });

        // Listener para cambio de estación
        document.getElementById('filterEstacion').addEventListener('change', function () {
            currentEstacion = this.value;
            console.log('📍 Estación seleccionada:', currentEstacion);

            // ✅ AGREGAR: Resetear año al cambiar estación
            currentAnio = '';
            document.getElementById('filterAnio').value = '';
            document.getElementById('filterAnio').disabled = true;
            document.getElementById('filterAnio').innerHTML = '<option value="">Seleccione nivel primero</option>';

            // Cargar años disponibles
            loadAniosDisponibles();

            loadArchivos();
        });

        // ✅ AGREGAR: Listener para cambio de año
        document.getElementById('filterAnio').addEventListener('change', function () {
            currentAnio = this.value;
            console.log('📅 Año seleccionado:', currentAnio || 'Todos');
            loadArchivos();
        });
        // Listeners para Torres
        document.getElementById('filterEstacionTorres').addEventListener('change', function () {
            currentEstacionTorres = this.value;
            console.log('🗼 Estación Torres seleccionada:', currentEstacionTorres);

            // Resetear año al cambiar estación
            currentAnioTorres = '';
            document.getElementById('filterAnioTorres').value = '';
            document.getElementById('filterAnioTorres').disabled = true;
            document.getElementById('filterAnioTorres').innerHTML = '<option value="">Seleccione nivel primero</option>';

            // Cargar años disponibles para Torres
            loadAniosDisponiblesTorres();

            loadArchivosTorres();
        });

        document.querySelectorAll('#levelSelectorTorres .level-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                currentNivelTorres = this.dataset.nivel;

                document.querySelectorAll('#levelSelectorTorres .level-btn').forEach(b => {
                    b.classList.remove('active');
                });
                this.classList.add('active');

                console.log('🎚️ Nivel Torres seleccionado:', currentNivelTorres);

                // Cargar años disponibles cuando cambia el nivel
                loadAniosDisponiblesTorres();

                // Resetear año al cambiar nivel
                currentAnioTorres = '';
                document.getElementById('filterAnioTorres').value = '';

                loadArchivosTorres();
            });
        });

        // ✅ AGREGAR: Listener para cambio de año en Torres
        document.getElementById('filterAnioTorres').addEventListener('change', function () {
            currentAnioTorres = this.value;
            console.log('📅 Año Torres seleccionado:', currentAnioTorres || 'Todos');
            loadArchivosTorres();
        });

        // Cerrar modal de confirmación con ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                cerrarModalConfirmacion();
            }
        });
    }
    function setNivel(nivel) {
        currentNivel = nivel;

        document.querySelectorAll('.level-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-nivel="${nivel}"]`).classList.add('active');

        console.log('🎚️ Nivel seleccionado:', currentNivel);

        // ✅ AGREGAR: Cargar años disponibles cuando cambia el nivel
        loadAniosDisponibles();

        // ✅ MODIFICAR: Resetear año al cambiar nivel
        currentAnio = '';
        document.getElementById('filterAnio').value = '';

        loadArchivos();
    }

    // ========================================
    // ACCIONES DE ARCHIVO
    // ========================================
    let archivoParaDescargar = null;
    function descargarArchivo(ruta, nombre) {
        archivoParaDescargar = { ruta, nombre };

        document.getElementById('formDescarga').reset();
        limpiarErrores();
        validarFormulario();

        const modal = document.getElementById('modalFormularioDescarga');
        modal.classList.add('active');

        console.log('📋 Formulario de descarga abierto para:', nombre);
    }

    function validarFormulario() {
        const nombreCompleto = document.getElementById('nombreCompleto').value.trim();
        const institucion = document.getElementById('institucion').value.trim();
        const cedula = document.getElementById('cedula').value.trim();
        const motivoDescarga = document.getElementById('motivoDescarga').value.trim();
        const aceptoTerminos = document.getElementById('aceptoTerminos').checked;

        let esValido = true;

        const errorNombre = validarNombreCompleto(nombreCompleto);
        if (errorNombre) {
            if (nombreCompleto) mostrarError('nombreCompleto', errorNombre);
            esValido = false;
        } else {
            limpiarError('nombreCompleto');
        }

        const errorInstitucion = validarInstitucion(institucion);
        if (errorInstitucion) {
            if (institucion) mostrarError('institucion', errorInstitucion);
            esValido = false;
        } else {
            limpiarError('institucion');
        }

        const errorCedula = validarCedulaEcuador(cedula);
        if (errorCedula) {
            if (cedula) mostrarError('cedula', errorCedula);
            esValido = false;
        } else {
            limpiarError('cedula');
        }

        const errorMotivo = validarMotivo(motivoDescarga);
        if (errorMotivo) {
            if (motivoDescarga) mostrarError('motivoDescarga', errorMotivo);
            esValido = false;
        } else {
            limpiarError('motivoDescarga');
        }

        const formularioValido = esValido && aceptoTerminos;
        document.getElementById('btnDescargarForm').disabled = !formularioValido;

        return formularioValido;
    }


    function cerrarFormularioDescarga() {
        const modal = document.getElementById('modalFormularioDescarga');
        modal.classList.remove('active');
        archivoParaDescargar = null;
        limpiarErrores();
    }
    function limpiarErrores() {
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.style.borderColor = '';
            const errorMsg = input.parentElement.querySelector('.error-message');
            if (errorMsg) errorMsg.remove();
        });
    }
    async function procesarDescarga() {
        if (!validarFormulario()) {
            alert('Por favor corrija los errores en el formulario antes de continuar');
            return;
        }

        if (!archivoParaDescargar) {
            alert('Error: No hay archivo seleccionado');
            return;
        }

        // Deshabilitar botón mientras procesa
        const btnDescargar = document.getElementById('btnDescargarForm');
        const textoOriginal = btnDescargar.innerHTML;
        btnDescargar.disabled = true;

        // ✅ CAMBIAR ESTE TEXTO
        btnDescargar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Descargando...</span>';
        // ❌ ANTES ERA: '<i class="fas fa-spinner fa-spin"></i> <span>Registrando...</span>';

        try {
            // 1. REGISTRAR EN BASE DE DATOS
            const formData = new FormData();
            formData.append('action', 'registrar_descarga');
            formData.append('nombre_solicitante', document.getElementById('nombreCompleto').value.trim());
            formData.append('institucion', document.getElementById('institucion').value.trim());
            formData.append('cedula_pasaporte', document.getElementById('cedula').value.trim());
            formData.append('motivo', document.getElementById('motivoDescarga').value.trim());
            formData.append('estacion', currentEstacion);
            formData.append('tipo_nivel', currentNivel);

            console.log('📤 Enviando registro de descarga...');

            const response = await fetch('../controller/CEstacion.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const data = await response.json();
            console.log('📥 Respuesta del servidor:', data);

            if (!data.success) {
                throw new Error(data.message || 'Error al registrar la descarga');
            }

            // 2. SI EL REGISTRO FUE EXITOSO, INICIAR DESCARGA
            console.log('✅ Descarga registrada con ID:', data.data.id_registro);

            const link = document.createElement('a');
            link.href = '../' + archivoParaDescargar.ruta;
            link.download = archivoParaDescargar.nombre;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            console.log('✅ Archivo descargado:', archivoParaDescargar.nombre);

            // ✅ CAMBIAR ESTE MENSAJE
            mostrarMensajeExito('Descarga completada exitosamente');
            // ❌ ANTES ERA: 'Descarga registrada y archivo descargado correctamente'

            // Cerrar modal
            setTimeout(() => {
                cerrarFormularioDescarga();
            }, 1500);

        } catch (error) {
            console.error('❌ Error en procesarDescarga:', error);
            alert('Error al procesar la descarga: ' + error.message);

            // ✅ RESTAURAR BOTÓN CON TEXTO ORIGINAL
            btnDescargar.disabled = false;
            btnDescargar.innerHTML = textoOriginal;
        }
    }
    function mostrarMensajeExito(mensaje) {
        // Crear toast de éxito
        const toast = document.createElement('div');
        toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, var(--accent-green), var(--primary-cyan));
        color: #0a0e1a;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 4px 20px rgba(0, 255, 136, 0.4);
        font-weight: 600;
        font-size: 0.85rem;
        z-index: 99999;
        display: flex;
        align-items: center;
        gap: 0.8rem;
        animation: slideInRight 0.3s ease;
    `;

        toast.innerHTML = `
        <i class="fas fa-check-circle" style="font-size: 1.2rem;"></i>
        <span>${mensaje}</span>
    `;

        document.body.appendChild(toast);

        // Eliminar después de 3 segundos
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    document.getElementById('aceptoTerminos')?.addEventListener('change', function () {
        document.getElementById('btnDescargarForm').disabled = !this.checked;
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            cerrarFormularioDescarga();
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const inputs = ['nombreCompleto', 'institucion', 'cedula', 'motivoDescarga'];
        inputs.forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener('input', validarFormulario);
                input.addEventListener('blur', validarFormulario);
            }
        });

        const checkbox = document.getElementById('aceptoTerminos');
        if (checkbox) {
            checkbox.addEventListener('change', validarFormulario);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            cerrarFormularioDescarga();
        }
    });

    function previsualizarArchivo(ruta) {
        console.log('👁️ Vista previa:', ruta);
        alert('Función de previsualización en desarrollo');
    }

    function showError(message) {
        console.error('⚠️', message);
    }
    // ========================================
    // VISTA PREVIA DE ARCHIVOS
    // ========================================
    async function previsualizarArchivo(ruta) {
        console.log('👁️ Abriendo vista previa:', ruta);

        // Crear modal
        const modal = crearModalPreview();
        document.body.appendChild(modal);

        // Mostrar modal con loading
        setTimeout(() => modal.classList.add('active'), 10);

        try {
            // Cargar datos del CSV
            const response = await fetch('../' + ruta);

            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }

            const csvText = await response.text();

            // Parsear CSV
            const datos = parsearCSV(csvText);

            // Renderizar tabla
            renderizarTablaPreview(modal, datos, ruta);

        } catch (error) {
            console.error('❌ Error al cargar CSV:', error);
            mostrarErrorPreview(modal, error.message);
        }
    }

    function crearModalPreview() {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.id = 'modalPreview';

        modal.innerHTML = `
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fas fa-table"></i>
                    <span>Cargando vista previa...</span>
                </h3>
                <button class="modal-close" onclick="cerrarModalPreview()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="loading-spinner">
                    <div class="spinner"></div>
                    <div class="loading-text">Cargando datos del archivo...</div>
                </div>
            </div>
        </div>
    `;

        // Cerrar con ESC
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                cerrarModalPreview();
            }
        });

        return modal;
    }

    function parsearCSV(texto) {
        const lineas = texto.trim().split('\n');

        if (lineas.length === 0) {
            throw new Error('El archivo CSV está vacío');
        }

        // Detectar separador (coma o punto y coma)
        const primeraLinea = lineas[0];
        const separador = primeraLinea.includes(';') ? ';' : ',';

        // Parsear headers
        const headers = primeraLinea.split(separador).map(h => h.trim().replace(/^"|"$/g, ''));

        // Parsear filas (limitar a 1000 filas para performance)
        const filas = [];
        const maxFilas = Math.min(lineas.length - 1, 1000);

        for (let i = 1; i <= maxFilas; i++) {
            if (lineas[i].trim()) {
                const valores = lineas[i].split(separador).map(v => v.trim().replace(/^"|"$/g, ''));
                filas.push(valores);
            }
        }

        return {
            headers: headers,
            filas: filas,
            totalFilas: lineas.length - 1,
            filasMostradas: filas.length
        };
    }

    function renderizarTablaPreview(modal, datos, ruta) {
        const modalTitle = modal.querySelector('.modal-title span');
        const modalBody = modal.querySelector('.modal-body');

        const nombreArchivo = ruta.split('/').pop();
        modalTitle.textContent = `Vista Previa: ${nombreArchivo}`;

        let html = `
        <div class="modal-info">
            <div class="modal-info-item">
                <i class="fas fa-columns"></i>
                <span>Columnas: <strong>${datos.headers.length}</strong></span>
            </div>
            <div class="modal-info-item">
                <i class="fas fa-list"></i>
                <span>Filas: <strong>${datos.totalFilas.toLocaleString()}</strong></span>
            </div>
            ${datos.filasMostradas < datos.totalFilas ? `
                <div class="modal-info-item">
                    <i class="fas fa-info-circle"></i>
                    <span>Mostrando: <strong>${datos.filasMostradas}</strong> de <strong>${datos.totalFilas.toLocaleString()}</strong></span>
                </div>
            ` : ''}
        </div>
        
        <div class="table-wrapper">
            <div class="table-scroll-container">
                <table class="preview-table">
                    <thead>
                        <tr>
    `;

        // Headers
        datos.headers.forEach(header => {
            html += `<th>${header}</th>`;
        });

        html += `
                        </tr>
                    </thead>
                    <tbody>
    `;

        // Filas
        datos.filas.forEach(fila => {
            html += '<tr>';
            fila.forEach(valor => {
                html += `<td>${valor || '-'}</td>`;
            });
            html += '</tr>';
        });

        html += `
                    </tbody>
                </table>
            </div>
        </div>
    `;

        modalBody.innerHTML = html;
    }

    function mostrarErrorPreview(modal, mensaje) {
        const modalBody = modal.querySelector('.modal-body');
        modalBody.innerHTML = `
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <div><strong>Error al cargar el archivo</strong></div>
            <div>${mensaje}</div>
        </div>
    `;
    }
    function mostrarError(inputId, mensaje) {
        const input = document.getElementById(inputId);
        input.style.borderColor = 'var(--accent-red)';

        let errorDiv = input.parentElement.querySelector('.error-message');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.style.cssText = 'color: var(--accent-red); font-size: 0.65rem; margin-top: 0.3rem;';
            input.parentElement.appendChild(errorDiv);
        }
        errorDiv.textContent = mensaje;
    }
    function limpiarError(inputId) {
        const input = document.getElementById(inputId);
        input.style.borderColor = '';
        const errorMsg = input.parentElement.querySelector('.error-message');
        if (errorMsg) errorMsg.remove();
    }
    function validarNombreCompleto(nombre) {
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
        if (!nombre || nombre.length < 5) {
            return 'El nombre debe tener al menos 5 caracteres';
        }
        if (!regex.test(nombre)) {
            return 'El nombre solo puede contener letras';
        }
        const palabras = nombre.trim().split(/\s+/);
        if (palabras.length < 2) {
            return 'Ingrese su nombre completo (nombre y apellido)';
        }
        return null;
    }

    function validarInstitucion(institucion) {
        const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s\-\.]+$/;
        if (!institucion || institucion.length < 3) {
            return 'La institución debe tener al menos 3 caracteres';
        }
        if (!regex.test(institucion)) {
            return 'La institución solo puede contener letras, espacios, guiones y puntos';
        }
        return null;
    }

    function validarCedulaEcuador(cedula) {
        cedula = cedula.replace(/\s/g, '');

        if (!/^\d+$/.test(cedula)) {
            return 'La cédula solo debe contener números';
        }

        if (cedula.length === 10) {
            const provincia = parseInt(cedula.substring(0, 2));
            if (provincia < 1 || provincia > 24) {
                return 'Cédula inválida: código de provincia incorrecto';
            }

            const tercerDigito = parseInt(cedula.charAt(2));
            if (tercerDigito > 5) {
                return 'Cédula inválida: tercer dígito incorrecto';
            }

            const coeficientes = [2, 1, 2, 1, 2, 1, 2, 1, 2];
            let suma = 0;

            for (let i = 0; i < 9; i++) {
                let valor = parseInt(cedula.charAt(i)) * coeficientes[i];
                if (valor > 9) valor -= 9;
                suma += valor;
            }

            const digitoVerificador = parseInt(cedula.charAt(9));
            const resultado = suma % 10 === 0 ? 0 : 10 - (suma % 10);

            if (resultado !== digitoVerificador) {
                return 'Cédula ecuatoriana inválida (dígito verificador incorrecto)';
            }

            return null;
        }

        if (cedula.length >= 6 && cedula.length <= 20) {
            return null;
        }

        return 'Ingrese una cédula válida (10 dígitos) o pasaporte (6-20 caracteres)';
    }

    function validarMotivo(motivo) {
        if (!motivo || motivo.length < 25) {
            return 'El motivo debe tener al menos 25 caracteres';
        }
        if (motivo.length > 500) {
            return 'El motivo no puede exceder 500 caracteres';
        }
        return null;
    }


    function cerrarModalPreview() {
        const modal = document.getElementById('modalPreview');
        if (modal) {
            modal.classList.remove('active');
            setTimeout(() => modal.remove(), 300);
        }
    }

    // Cerrar modal con tecla ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            cerrarFormularioDescarga();
        }
    });
</script>

<?php include('footerAdmin.php'); ?>