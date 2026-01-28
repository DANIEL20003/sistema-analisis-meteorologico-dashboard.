<?php
$titulo_pagina = 'Reportes - Sistema de Análisis Meteorológico - ESPOCH';
$pagina_activa = 'reportes';

include('../views/Administrador/headerAdmin.php');
?>


<style>
    /* ===== VARIABLES DE COLORES ===== */
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

    /* ===== LAYOUT PRINCIPAL ===== */
    .reportes-page {
        min-height: 100vh;
        position: relative;
        padding-bottom: 0;
    }

    main {
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }

    .reportes-background {
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

    .reportes-background::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(10, 14, 26, 0.92) 0%, rgba(18, 24, 43, 0.88) 100%);
        z-index: 1;
    }

    .reportes-container {
        position: relative;
        z-index: 2;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0.5rem 2rem 0 2rem;
    }

    /* ===== FILTROS DE ESTACIONES ===== */
    .station-filters {
        background: var(--bg-secondary);
        border: 1px solid var(--border-secondary);
        border-radius: 15px;
        padding: 1.2rem 1.5rem;
        margin: 1rem auto;
        max-width: 700px;
        box-shadow: var(--shadow-card);
    }

    .filters-title {
        color: var(--primary-cyan);
        font-size: 0.85rem;
        font-weight: 700;
        margin: 0 0 0.8rem 0;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .station-select-wrapper {
        position: relative;
    }

    .station-select {
        width: 100%;
        background: var(--bg-card);
        border: 2px solid var(--border-secondary);
        border-radius: 12px;
        padding: 0.75rem 3rem 0.75rem 1.2rem;
        color: var(--text-primary);
        font-size: 0.8rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        outline: none;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .station-select:hover {
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.05);
        box-shadow: 0 0 20px rgba(0, 255, 255, 0.1);
    }

    .station-select:focus {
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.08);
        box-shadow: 0 0 30px rgba(0, 255, 255, 0.2);
    }

    .station-select option {
        background: var(--bg-secondary);
        color: var(--text-primary);
        padding: 0.6rem;
        font-size: 0.75rem;
    }

    .select-icon {
        position: absolute;
        right: 1.5rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--primary-cyan);
        font-size: 1rem;
        pointer-events: none;
        transition: all 0.3s ease;
    }

    .station-select:focus~.select-icon {
        transform: translateY(-50%) rotate(180deg);
    }

    /* ===== CONTENIDO PRINCIPAL DE ESTACIÓN ===== */
    .station-content {
        background: var(--bg-secondary);
        border: 1px solid var(--border-secondary);
        border-radius: 15px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-card);
    }

    .loading-content {
        text-align: center;
        padding: 3rem;
        color: var(--text-muted);
    }

    .loading-content .loading-spinner {
        width: 32px;
        height: 32px;
        border: 3px solid var(--border-primary);
        border-radius: 50%;
        border-top-color: var(--primary-cyan);
        animation: spin 1s ease-in-out infinite;
        margin: 0 auto 1rem;
    }

    .reportes-subtitle {
        font-size: 1rem;
        color: var(--text-secondary);
        margin: 0 0 1rem 0;
        line-height: 1.5;
        text-align: center;
    }

    /* ===== NUEVO DISEÑO: IMAGEN - INFO - IMAGEN ===== */
    .station-header {
        display: grid;
        grid-template-columns: 1fr 380px 1fr;
        gap: 2rem;
        margin-bottom: 3rem;
        align-items: center;
    }

    /* Imágenes más grandes */
    .station-image-large {
        width: 100%;
        height: 400px;
        background: linear-gradient(135deg, var(--bg-card) 0%, rgba(26, 34, 54, 0.5) 100%);
        border: 1px solid var(--border-primary);
        border-radius: 15px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        box-shadow: 0 0 30px rgba(0, 255, 255, 0.25),
            inset 0 0 30px rgba(0, 255, 255, 0.08);
        padding: 15px;
    }

    .station-image-large::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0, 255, 255, 0.1) 0%, transparent 50%);
        z-index: 1;
        pointer-events: none;
    }

    .station-image-large img {
        max-width: 95%;
        max-height: 95%;
        width: auto;
        height: auto;
        object-fit: contain;
        object-position: center;
        border-radius: 10px;
        filter: brightness(1.08) contrast(1.15) saturate(1.25);
        transition: all 0.4s ease;
        position: relative;
        z-index: 2;
    }

    .station-image-large:hover img {
        filter: brightness(1.12) contrast(1.2) saturate(1.3);
        transform: scale(1.03);
    }

    .image-label {
        position: absolute;
        top: 10px;
        left: 10px;
        background: rgba(0, 0, 0, 0.85);
        color: var(--text-primary);
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        z-index: 3;
        border: 1px solid var(--border-primary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Información compacta en el centro */
    .station-info-center {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .info-item-compact {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        padding: 0.6rem 0.8rem;
        border-left: 3px solid var(--primary-cyan);
        transition: all 0.2s ease;
        position: relative;
    }

    .info-item-compact:hover {
        background: var(--bg-tertiary);
        border-left-color: var(--accent-green);
        transform: translateX(3px);
    }

    .info-label-compact {
        color: var(--text-muted);
        font-size: 0.65rem;
        font-weight: 600;
        margin: 0 0 0.2rem 0;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .info-value-compact {
        color: var(--text-primary);
        font-size: 0.8rem;
        font-weight: 600;
        margin: 0;
        line-height: 1.3;
    }

    .status-active {
        color: var(--accent-green);
    }

    .status-offline {
        color: var(--accent-red);
    }

    /* ===== BOTONES DE EDICIÓN Y ELIMINACIÓN DE ESTACIÓN ===== */
    .station-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        display: flex;
        gap: 0.5rem;
        z-index: 10;
    }

    .btn-edit-station,
    .btn-delete-station {
        background: rgba(0, 0, 0, 0.8);
        border: 1px solid var(--border-primary);
        color: var(--text-primary);
        padding: 0.5rem;
        border-radius: 8px;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
    }

    .btn-edit-station:hover {
        background: var(--primary-cyan);
        color: #0a0e1a;
        border-color: var(--primary-cyan);
        transform: scale(1.1);
    }

    .btn-delete-station:hover {
        background: var(--accent-red);
        color: white;
        border-color: var(--accent-red);
        transform: scale(1.1);
    }

    /* ===== SECCIONES DE COMPONENTES Y SENSORES MEJORADAS ===== */
    .components-sensors-section {
        margin-top: 2.5rem;
    }

    .section-title {
        color: var(--primary-cyan);
        font-size: 1.1rem;
        font-weight: 700;
        margin: 0 0 1.5rem 0;
        padding: 0.8rem 1rem;
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-left: 4px solid var(--primary-cyan);
        border-radius: 10px;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .section-title i {
        font-size: 1.3rem;
    }

    .section-actions {
        margin-left: auto;
        display: flex;
        gap: 0.5rem;
    }

    .btn-add {
        background: var(--accent-green);
        color: #0a0e1a;
        border: none;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .btn-add:hover {
        background: #00cc6a;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 255, 136, 0.3);
    }

    .detailed-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .detailed-card {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 12px;
        padding: 1.3rem;
        border-left: 4px solid var(--accent-green);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .detailed-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0, 255, 255, 0.03) 0%, transparent 100%);
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .detailed-card:hover::before {
        opacity: 1;
    }

    .detailed-card:hover {
        border-left-color: var(--primary-cyan);
        transform: translateY(-3px);
        background: var(--bg-tertiary);
        box-shadow: 0 6px 20px rgba(0, 255, 255, 0.15);
    }

    .card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .card-title {
        color: var(--text-primary);
        font-size: 0.95rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .card-icon {
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        color: #0a0e1a;
        font-weight: 700;
    }

    .card-body {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
    }

    .card-row {
        display: grid;
        grid-template-columns: 110px 1fr;
        gap: 0.8rem;
        font-size: 0.75rem;
        align-items: start;
    }

    .card-label {
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .card-value {
        color: var(--text-secondary);
        font-weight: 500;
        word-break: break-word;
    }

    .card-footer {
        margin-top: 0.8rem;
        padding-top: 0.8rem;
        border-top: 1px solid var(--border-secondary);
    }

    .card-notes {
        font-size: 0.7rem;
        color: var(--text-muted);
        line-height: 1.5;
        font-style: italic;
    }

    .card-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 0.8rem;
        padding-top: 0.8rem;
        border-top: 1px solid var(--border-secondary);
    }

    .btn-edit,
    .btn-delete {
        background: transparent;
        border: 1px solid var(--border-secondary);
        color: var(--text-secondary);
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.7rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.3rem;
    }

    .btn-edit:hover {
        border-color: var(--primary-cyan);
        color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.1);
    }

    .btn-delete:hover {
        border-color: var(--accent-red);
        color: var(--accent-red);
        background: rgba(255, 7, 58, 0.1);
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        background: var(--bg-card);
        border: 1px dashed var(--border-secondary);
        border-radius: 12px;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 3rem;
        color: var(--border-primary);
        margin-bottom: 1rem;
    }

    /* ===== MODALES ===== */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.8);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal.show {
        display: flex;
    }

    .modal-content {
        background: var(--bg-secondary);
        border: 1px solid var(--border-primary);
        border-radius: 15px;
        padding: 2rem;
        max-width: 800px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 0 50px rgba(0, 255, 255, 0.3);
    }

    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .modal-title {
        color: var(--primary-cyan);
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0;
    }

    .modal-close {
        background: none;
        border: none;
        color: var(--text-muted);
        font-size: 1.5rem;
        cursor: pointer;
        padding: 0.5rem;
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .modal-close:hover {
        color: var(--text-primary);
        background: var(--bg-card);
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        color: var(--text-secondary);
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .form-input,
    .form-select,
    .form-textarea {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        padding: 0.8rem;
        color: var(--text-primary);
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .form-select {
        background: var(--bg-tertiary);
        color: var(--text-primary);
        cursor: pointer;
    }

    .form-select[readonly],
    .form-input[readonly] {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-primary);
        opacity: 0.7;
        cursor: not-allowed;
        pointer-events: none;
    }

    .form-select[readonly] option {
        background: var(--bg-secondary);
        color: var(--text-primary);
    }

    /* Mejorar visualización de selects no editables */
    .form-select[style*="pointer-events: none"] {
        background: rgba(255, 255, 255, 0.05) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-secondary) !important;
    }

    .form-select option {
        background: var(--bg-secondary);
        color: var(--text-primary);
        padding: 0.5rem;
    }

    /* Estilos para input de fecha */
    .form-input[type="date"] {
        color-scheme: dark;
    }

    .form-input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: var(--primary-cyan);
        box-shadow: 0 0 0 2px rgba(0, 255, 255, 0.2);
    }

    .form-textarea {
        resize: vertical;
        min-height: 80px;
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-secondary);
    }

    .btn-save,
    .btn-cancel {
        flex: 1;
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .btn-save {
        background: var(--accent-green);
        color: #0a0e1a;
        border: none;
    }

    .btn-save:hover {
        background: #00cc6a;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 255, 136, 0.3);
    }

    .btn-delete-confirm {
        flex: 1;
        padding: 0.8rem 1.5rem;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        background: var(--accent-red);
        color: white;
        border: none;
    }

    .btn-delete-confirm:hover {
        background: #cc0000;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 7, 58, 0.4);
    }

    .btn-cancel {
        background: transparent;
        color: var(--text-secondary);
        border: 1px solid var(--border-secondary);
    }

    .btn-cancel:hover {
        color: var(--text-primary);
        border-color: var(--text-muted);
    }

    /* Estilos específicos para modal de editar estación */
    .modalEditarEstacion .modal-content {
        max-width: 1000px;
    }

    .form-help-text {
        font-size: 0.7rem;
        color: var(--text-muted);
        font-style: italic;
    }

    .image-preview {
        max-width: 200px;
        max-height: 150px;
        border-radius: 8px;
        border: 1px solid var(--border-secondary);
        margin-top: 0.5rem;
    }

    /* ===== NOTIFICACIONES ===== */
    /* ===== NOTIFICACIONES MEJORADAS ===== */
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 1.2rem 1.8rem;
        border-radius: 10px;
        color: white;
        font-weight: 600;
        font-size: 0.95rem;
        z-index: 2000;
        transform: translateX(120%);
        transition: transform 0.3s ease;
        max-width: 450px;
        word-wrap: break-word;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .notification.show {
        transform: translateX(0);
    }

    .notification.success {
        background: linear-gradient(135deg, var(--accent-green), #00cc6a);
    }

    .notification.error {
        background: linear-gradient(135deg, var(--accent-red), #cc0000);
        cursor: pointer;
        animation: shake 0.5s;
    }

    .notification.error::after {
        content: ' (Haz clic para cerrar)';
        font-size: 0.75rem;
        opacity: 0.8;
        font-style: italic;
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-10px);
        }

        75% {
            transform: translateX(10px);
        }
    }

    /* ===== ANIMACIONES ===== */
    .fade-in {
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1200px) {
        .station-header {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }

        .station-image-large {
            height: 280px;
        }

        .station-info-center {
            grid-template-columns: repeat(2, 1fr);
            display: grid;
            gap: 0.8rem;
        }
    }

    @media (max-width: 1024px) {
        .reportes-container {
            padding: 0 1.5rem 0 1.5rem;
        }

        .station-buttons {
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        }

        .detailed-grid {
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }

        .form-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .reportes-container {
            padding: 0 1rem 0 1rem;
        }

        .station-filters {
            padding: 1rem;
        }

        .station-buttons {
            grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
        }

        .station-button {
            font-size: 0.6rem;
            padding: 0.6rem 0.8rem;
        }

        .station-content {
            padding: 1.5rem;
        }

        .station-image-large {
            height: 220px;
        }

        .station-info-center {
            grid-template-columns: 1fr;
        }

        .detailed-grid {
            grid-template-columns: 1fr;
        }

        .card-row {
            grid-template-columns: 90px 1fr;
        }

        .modal-content {
            padding: 1.5rem;
            width: 95%;
        }

        .form-actions {
            flex-direction: column;
        }
    }

    .tipo-card {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 10px;
        padding: 1.2rem;
        margin-bottom: 1rem;
        border-left: 4px solid var(--primary-cyan);
        transition: all 0.3s ease;
    }

    .tipo-card:hover {
        transform: translateX(5px);
        background: var(--bg-tertiary);
        border-left-color: var(--accent-green);
    }

    .tipo-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.8rem;
    }

    .tipo-card-title {
        color: var(--text-primary);
        font-size: 1rem;
        font-weight: 700;
        margin: 0;
    }

    .tipo-card-desc {
        color: var(--text-muted);
        font-size: 0.85rem;
        margin: 0;
        font-style: italic;
    }

    .tipo-card-actions {
        display: flex;
        gap: 0.5rem;
    }

    .btn-icon {
        background: transparent;
        border: 1px solid var(--border-secondary);
        color: var(--text-secondary);
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-size: 0.7rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .btn-icon:hover {
        border-color: var(--primary-cyan);
        color: var(--primary-cyan);
    }

    .btn-icon.delete:hover {
        border-color: var(--accent-red);
        color: var(--accent-red);
    }
</style>

<div class="reportes-page">
    <div class="reportes-background"></div>

    <div class="reportes-container">
        <!-- FILTROS DE ESTACIONES -->
        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
            <button class="btn-add" onclick="abrirModalGestionSensores()"
                style="background: var(--primary-blue); flex: 1;">
                <i class="fas fa-sliders-h"></i>
                Gestionar Tipos de Sensores
            </button>
            <button class="btn-add" onclick="abrirModalGestionComponentes()"
                style="background: var(--accent-purple); flex: 1;">
                <i class="fas fa-cog"></i>
                Gestionar Tipos de Componentes
            </button>
            <button class="btn-add" onclick="abrirModalGestionVariables()"
                style="background: var(--accent-yellow); flex: 1; color: #0a0e1a;">
                <i class="fas fa-database"></i>
                Gestionar Variables L2 TR
            </button>
        </div>

        <div class="station-filters fade-in" id="stationFilters">
            <h3 class="filters-title">
                <i class="fas fa-filter"></i>
                Seleccionar Estación
            </h3>
            <div style="display: flex; gap: 1rem; align-items: center;">
                <div class="station-select-wrapper" style="flex: 1;">
                    <select id="stationSelect" class="station-select">
                        <option value="">Cargando estaciones...</option>
                    </select>
                    <i class="fas fa-chevron-down select-icon"></i>
                </div>
                <button class="btn-add" onclick="abrirModalAgregarEstacion()"
                    style="background: var(--accent-green); white-space: nowrap;">
                    <i class="fas fa-plus"></i>
                    Agregar
                </button>
            </div>
        </div>

        <div class="reportes-subtitle fade-in">
            Panel de Administración - Gestión de componentes y sensores por estación
        </div>

        <!-- CONTENIDO DE LA ESTACIÓN -->
        <div class="station-content fade-in" id="stationContent">
            <div class="loading-content">

                <p>Agregue una estacion</p>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="modalGestionSensores">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-sliders-h"></i>
                Gestión de Tipos de Sensores
            </h3>
            <button class="modal-close" onclick="cerrarModal('modalGestionSensores')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <button class="btn-add" onclick="abrirModalAgregarTipoSensor()">
                <i class="fas fa-plus"></i>
                Agregar Nuevo Tipo
            </button>
        </div>

        <div id="listaSensores" style="max-height: 500px; overflow-y: auto;">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p>Cargando tipos de sensores...</p>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="modalGestionComponentes">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-cog"></i>
                Gestión de Tipos de Componentes
            </h3>
            <button class="modal-close" onclick="cerrarModal('modalGestionComponentes')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <button class="btn-add" onclick="abrirModalAgregarTipoComponente()">
                <i class="fas fa-plus"></i>
                Agregar Nuevo Tipo
            </button>
        </div>

        <div id="listaComponentes" style="max-height: 500px; overflow-y: auto;">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p>Cargando tipos de componentes...</p>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="modalGestionVariables">
    <div class="modal-content" style="max-width: 1000px;">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-database"></i>
                Gestión de Variables Meteorológicas L2 Tiempo Real
            </h3>
            <button class="modal-close" onclick="cerrarModal('modalGestionVariables')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div style="margin-bottom: 1.5rem;">
            <button class="btn-add" onclick="abrirModalAgregarVariable()">
                <i class="fas fa-plus"></i>
                Agregar Nueva Variable
            </button>
        </div>

        <div id="listaVariables" style="max-height: 500px; overflow-y: auto;">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p>Cargando variables...</p>
            </div>
        </div>
    </div>
</div>
<div class="modal" id="modalFormVariable">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3 class="modal-title" id="tituloFormVariable">
                <i class="fas fa-plus"></i>
                Agregar Variable
            </h3>
            <button class="modal-close" onclick="cerrarModal('modalFormVariable')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formVariable">
            <input type="hidden" id="varAccion" value="">
            <input type="hidden" id="varId" value="">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="varCodigo">Código de Columna *</label>
                    <input type="text" class="form-input" id="varCodigo" name="codigo_columna"
                        placeholder="Ej: 1_29341m" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="varNombre">Nombre Corto *</label>
                    <input type="text" class="form-input" id="varNombre" name="nombre_corto" placeholder="Ej: temp_avg"
                        required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="varUnidad">Unidad *</label>
                    <select class="form-select" id="varUnidad" name="unidad" required>
                        <option value="">Seleccionar...</option>
                        <option value="°C">°C (Temperatura)</option>
                        <option value="%">% (Porcentaje/Humedad)</option>
                        <option value="hPa">hPa (Presión)</option>
                        <option value="mm">mm (Precipitación)</option>
                        <option value="W/m²">W/m² (Radiación)</option>
                        <option value="°">° (Dirección)</option>
                        <option value="m/s">m/s (Velocidad)</option>
                        <option value="m">m (Distancia)</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="varDescripcion">Descripción *</label>
                    <textarea class="form-textarea" id="varDescripcion" name="descripcion"
                        placeholder="Descripción de la variable..." rows="3" required></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modalFormVariable')">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i>
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="modalFormTipo">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3 class="modal-title" id="tituloFormTipo">
                <i class="fas fa-plus"></i>
                Agregar Tipo
            </h3>
            <button class="modal-close" onclick="cerrarModal('modalFormTipo')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formTipo">
            <input type="hidden" id="tipoAccion" value="">
            <input type="hidden" id="tipoCategoria" value="">
            <input type="hidden" id="tipoId" value="">

            <div class="form-group">
                <label class="form-label" for="tipoNombre">Nombre *</label>
                <input type="text" class="form-input" id="tipoNombre" name="nombre" placeholder="Nombre del tipo"
                    required>
            </div>

            <div class="form-group">
                <label class="form-label" for="tipoDescripcion">Descripción</label>
                <textarea class="form-textarea" id="tipoDescripcion" name="descripcion"
                    placeholder="Descripción opcional..." rows="3"></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modalFormTipo')">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i>
                    Guardar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DE CONFIRMACIÓN DE ELIMINACIÓN -->
<div class="modal" id="modalConfirmDelete">
    <div class="modal-content" style="max-width: 400px;">
        <div class="modal-header">
            <h3 class="modal-title" style="color: var(--accent-red);">
                <i class="fas fa-exclamation-triangle"></i>
                Confirmar Eliminación
            </h3>
        </div>
        <div style="padding: 1.5rem 0; color: var(--text-secondary); text-align: center;">
            <p id="confirmDeleteMessage" style="font-size: 1rem; margin: 0;">
                ¿Está seguro de que desea eliminar este elemento?
            </p>
        </div>
        <div class="form-actions" style="margin-top: 1rem;">
            <button type="button" class="btn-cancel" onclick="cerrarModal('modalConfirmDelete')">
                <i class="fas fa-times"></i>
                Cancelar
            </button>
            <button type="button" class="btn-delete-confirm" onclick="confirmarEliminacion()">
                <i class="fas fa-trash"></i>
                Eliminar
            </button>
        </div>
    </div>
</div>

<!-- MODAL PARA EDITAR ESTACIÓN -->
<div class="modal modalEditarEstacion" id="modalEditarEstacion">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-edit"></i>
                Editar Estación Meteorológica
            </h3>
            <button class="modal-close" onclick="cerrarModal('modalEditarEstacion')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formEditarEstacion" enctype="multipart/form-data">
            <input type="hidden" id="editEstacionId" name="id_estacion">

            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label" for="editEstacionNombre">Nombre de la Estación *</label>
                    <input type="text" class="form-input" id="editEstacionNombre" name="nombre"
                        placeholder="Ej: Estación Meteorológica Quito" required>
                    <small class="form-help-text">Puede modificar el nombre de la estación</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstacionCodigo">Código de la Estación *</label>
                    <input type="text" class="form-input" id="editEstacionCodigo" name="codigo"
                        placeholder="Ej: EM-QUITO-01" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstacionTag">TAG Código INER *</label>
                    <input type="text" class="form-input" id="editEstacionTag" name="tag_codigo_iner"
                        placeholder="Ej: INER-QUI-001" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstacionProvincia">Provincia *</label>
                    <select class="form-select" id="editEstacionProvincia" name="provincia" required>
                        <option value="">Seleccionar provincia...</option>
                        <option value="Azuay">Azuay</option>
                        <option value="Bolívar">Bolívar</option>
                        <option value="Cañar">Cañar</option>
                        <option value="Chimborazo">Chimborazo</option>
                        <option value="Cotopaxi">Cotopaxi</option>
                        <option value="El Oro">El Oro</option>
                        <option value="Esmeraldas">Esmeraldas</option>
                        <option value="Galápagos">Galápagos</option>
                        <option value="Guayas">Guayas</option>
                        <option value="Imbabura">Imbabura</option>
                        <option value="Loja">Loja</option>
                        <option value="Los Ríos">Los Ríos</option>
                        <option value="Manabí">Manabí</option>
                        <option value="Morona Santiago">Morona Santiago</option>
                        <option value="Napo">Napo</option>
                        <option value="Pastaza">Pastaza</option>
                        <option value="Pichincha">Pichincha</option>
                        <option value="Tungurahua">Tungurahua</option>
                        <option value="Zamora Chinchipe">Zamora Chinchipe</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstacionCanton">Cantón *</label>
                    <input type="text" class="form-input" id="editEstacionCanton" name="canton"
                        placeholder="Nombre del cantón" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstacionParroquia">Parroquia</label>
                    <input type="text" class="form-input" id="editEstacionParroquia" name="parroquia"
                        placeholder="Nombre de la parroquia">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstacionComunidad">Comunidad</label>
                    <input type="text" class="form-input" id="editEstacionComunidad" name="comunidad"
                        placeholder="Nombre de la comunidad">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstacionLatitud">Latitud * (formato: -1.65432100)</label>
                    <input type="number" class="form-input" id="editEstacionLatitud" name="latitud" step="0.00000001"
                        min="-90" max="90" placeholder="-1.65432100" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstacionLongitud">Longitud * (formato: -78.98765432)</label>
                    <input type="number" class="form-input" id="editEstacionLongitud" name="longitud" step="0.00000001"
                        min="-180" max="180" placeholder="-78.98765432" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstacionAltura">Altura del Terreno (m)</label>
                    <input type="number" class="form-input" id="editEstacionAltura" name="altura_terreno"
                        placeholder="2500" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstacionFechaInstalacion">Fecha de Instalación</label>
                    <input type="date" class="form-input" id="editEstacionFechaInstalacion" name="fecha_instalacion">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstacionFotografia">Fotografía de la Estación (JPG, PNG)</label>
                    <input type="file" class="form-input" id="editEstacionFotografia" name="fotografia"
                        accept=".jpg,.jpeg,.png">
                    <div id="fotoPreview" style="margin-top: 0.5rem;"></div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editEstacionMapa">Mapa de Ubicación (JPG, PNG)</label>
                    <input type="file" class="form-input" id="editEstacionMapa" name="mapa" accept=".jpg,.jpeg,.png">
                    <div id="mapaPreview" style="margin-top: 0.5rem;"></div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modalEditarEstacion')">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i>
                    Actualizar Estación
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PARA EDITAR SENSOR -->
<div class="modal" id="modalEditarSensor">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-sensor-on"></i>
                Editar Sensor
            </h3>
            <button class="modal-close" onclick="cerrarModal('modalEditarSensor')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formEditarSensor">
            <input type="hidden" id="editSensorId" name="id_sensor">
            <input type="hidden" id="editSensorEstacion" name="id_estacion">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="editSensorTipo">Tipo de Sensor (No editable)</label>
                    <select class="form-select" name="sensor_tipo[]" required>
                        <option value="">Cargando...</option>
                    </select>
                    <small class="form-help-text">El tipo de sensor no puede modificarse</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editSensorEstado">Estado</label>
                    <select class="form-select" id="editSensorEstado" name="estado">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                        <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                        <option value="FUERA_SERVICIO">FUERA_SERVICIO</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editSensorMarca">Marca</label>
                    <input type="text" class="form-input" id="editSensorMarca" name="marca"
                        placeholder="Marca del sensor">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editSensorModelo">Modelo</label>
                    <input type="text" class="form-input" id="editSensorModelo" name="modelo"
                        placeholder="Modelo del sensor">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editSensorSerie">Serie</label>
                    <input type="text" class="form-input" id="editSensorSerie" name="serie"
                        placeholder="Número de serie">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editSensorMantenimiento">Fecha Último Mantenimiento</label>
                    <input type="date" class="form-input" id="editSensorMantenimiento" name="fecha_mantenimiento">
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="editSensorObservaciones">Observaciones</label>
                    <textarea class="form-textarea" id="editSensorObservaciones" name="observaciones"
                        placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modalEditarSensor')">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i>
                    Actualizar Sensor
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PARA EDITAR COMPONENTE -->
<div class="modal" id="modalEditarComponente">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-microchip"></i>
                Editar Componente
            </h3>
            <button class="modal-close" onclick="cerrarModal('modalEditarComponente')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formEditarComponente">
            <input type="hidden" id="editComponenteId" name="id_componente">
            <input type="hidden" id="editComponenteEstacion" name="id_estacion">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="editComponenteTipo">Tipo de Componente (No editable)</label>
                    <select class="form-select" id="editComponenteTipo" name="tipo_componente" disabled
                        style="background-color: #e9ecef; cursor: not-allowed;">
                        <option value="">Seleccionar tipo...</option>
                        <option value="Datalogger">Datalogger</option>
                        <option value="Módulo">Módulo</option>
                        <option value="Regulador">Regulador</option>
                        <option value="Modem">Modem</option>
                        <option value="Panel Solar">Panel Solar</option>
                        <option value="Batería">Batería</option>
                        <option value="Caja">Caja</option>
                        <option value="Torre">Torre</option>
                        <option value="Anillo de sombra">Anillo de sombra</option>
                        <option value="GPS">GPS</option>
                        <option value="Antena">Antena</option>
                    </select>
                    <small class="form-help-text">El tipo de componente no puede modificarse</small>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editComponenteEstado">Estado</label>
                    <select class="form-select" id="editComponenteEstado" name="estado">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                        <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                        <option value="FUERA_SERVICIO">FUERA_SERVICIO</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="editComponenteMarca">Marca</label>
                    <input type="text" class="form-input" id="editComponenteMarca" name="marca"
                        placeholder="Marca del componente">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editComponenteModelo">Modelo</label>
                    <input type="text" class="form-input" id="editComponenteModelo" name="modelo"
                        placeholder="Modelo del componente">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editComponenteSerie">Serie</label>
                    <input type="text" class="form-input" id="editComponenteSerie" name="serie"
                        placeholder="Número de serie">
                </div>

                <div class="form-group">
                    <label class="form-label" for="editComponenteMantenimiento">Fecha Último Mantenimiento</label>
                    <input type="date" class="form-input" id="editComponenteMantenimiento" name="fecha_mantenimiento">
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="editComponenteEspecificaciones">Especificaciones</label>
                    <textarea class="form-textarea" id="editComponenteEspecificaciones" name="especificaciones"
                        placeholder="Especificaciones técnicas..."></textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="editComponenteObservaciones">Observaciones</label>
                    <textarea class="form-textarea" id="editComponenteObservaciones" name="observaciones"
                        placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modalEditarComponente')">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i>
                    Actualizar Componente
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PARA AGREGAR SENSOR -->
<div class="modal" id="modalAgregarSensor">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-plus"></i>
                Agregar Nuevo Sensor
            </h3>
            <button class="modal-close" onclick="cerrarModal('modalAgregarSensor')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formAgregarSensor">
            <input type="hidden" id="addSensorEstacion" name="id_estacion">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="addSensorTipo">Tipo de Sensor *</label>
                    <select class="form-select" id="addSensorTipo" name="tipo_sensor" required>
                        <option value="">Seleccionar tipo...</option>
                        <option value="Sensor de Presión Barométrica">Sensor de Presión Barométrica</option>
                        <option value="Sensor de Viento (Anenómetro ultrasónico)">Sensor de Viento (Anenómetro
                            ultrasónico)</option>
                        <option value="Sensor de radiación solar 1 (Piranometro)">Sensor de radiación solar 1
                            (Piranometro)</option>
                        <option value="Sensor de radiación solar 2 (Piranometro)">Sensor de radiación solar 2
                            (Piranometro)</option>
                        <option value="Sensor de temperatura/Humedad">Sensor de temperatura/Humedad</option>
                        <option value="Sensor de temperatura del suelo">Sensor de temperatura del suelo</option>
                        <option value="Sensor de lluvia">Sensor de lluvia</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="addSensorEstado">Estado</label>
                    <select class="form-select" id="addSensorEstado" name="estado">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                        <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                        <option value="FUERA_SERVICIO">FUERA_SERVICIO</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="addSensorMarca">Marca</label>
                    <input type="text" class="form-input" id="addSensorMarca" name="marca"
                        placeholder="Marca del sensor">
                </div>

                <div class="form-group">
                    <label class="form-label" for="addSensorModelo">Modelo</label>
                    <input type="text" class="form-input" id="addSensorModelo" name="modelo"
                        placeholder="Modelo del sensor">
                </div>

                <div class="form-group">
                    <label class="form-label" for="addSensorSerie">Serie</label>
                    <input type="text" class="form-input" id="addSensorSerie" name="serie"
                        placeholder="Número de serie">
                </div>

                <div class="form-group">
                    <label class="form-label" for="addSensorMantenimiento">Fecha Último Mantenimiento</label>
                    <input type="date" class="form-input" id="addSensorMantenimiento" name="fecha_mantenimiento">
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="addSensorObservaciones">Observaciones</label>
                    <textarea class="form-textarea" id="addSensorObservaciones" name="observaciones"
                        placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modalAgregarSensor')">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-plus"></i>
                    Agregar Sensor
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PARA AGREGAR COMPONENTE -->
<div class="modal" id="modalAgregarComponente">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-plus"></i>
                Agregar Nuevo Componente
            </h3>
            <button class="modal-close" onclick="cerrarModal('modalAgregarComponente')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="formAgregarComponente">
            <input type="hidden" id="addComponenteEstacion" name="id_estacion">

            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="addComponenteTipo">Tipo de Componente *</label>
                    <select class="form-select" id="addComponenteTipo" name="tipo_componente" required>
                        <option value="">Seleccionar tipo...</option>
                        <option value="Datalogger">Datalogger</option>
                        <option value="Módulo">Módulo</option>
                        <option value="Regulador">Regulador</option>
                        <option value="Modem">Modem</option>
                        <option value="Panel Solar">Panel Solar</option>
                        <option value="Batería">Batería</option>
                        <option value="Caja">Caja</option>
                        <option value="Torre">Torre</option>
                        <option value="Anillo de sombra">Anillo de sombra</option>
                        <option value="GPS">GPS</option>
                        <option value="Antena">Antena</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="addComponenteEstado">Estado</label>
                    <select class="form-select" id="addComponenteEstado" name="estado">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                        <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                        <option value="FUERA_SERVICIO">FUERA_SERVICIO</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="addComponenteMarca">Marca</label>
                    <input type="text" class="form-input" id="addComponenteMarca" name="marca"
                        placeholder="Marca del componente">
                </div>

                <div class="form-group">
                    <label class="form-label" for="addComponenteModelo">Modelo</label>
                    <input type="text" class="form-input" id="addComponenteModelo" name="modelo"
                        placeholder="Modelo del componente">
                </div>

                <div class="form-group">
                    <label class="form-label" for="addComponenteSerie">Serie</label>
                    <input type="text" class="form-input" id="addComponenteSerie" name="serie"
                        placeholder="Número de serie">
                </div>

                <div class="form-group">
                    <label class="form-label" for="addComponenteMantenimiento">Fecha Último Mantenimiento</label>
                    <input type="date" class="form-input" id="addComponenteMantenimiento" name="fecha_mantenimiento">
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="addComponenteEspecificaciones">Especificaciones</label>
                    <textarea class="form-textarea" id="addComponenteEspecificaciones" name="especificaciones"
                        placeholder="Especificaciones técnicas..."></textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label" for="addComponenteObservaciones">Observaciones</label>
                    <textarea class="form-textarea" id="addComponenteObservaciones" name="observaciones"
                        placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modalAgregarComponente')">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-plus"></i>
                    Agregar Componente
                </button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL PARA AGREGAR ESTACIÓN COMPLETA -->
<div class="modal" id="modalAgregarEstacion">
    <div class="modal-content" style="max-width: 1000px;">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-map-marker-alt"></i>
                Agregar Nueva Estación Meteorológica
            </h3>
            <button class="modal-close" onclick="cerrarModal('modalAgregarEstacion')">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form id="formAgregarEstacion" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="form-group full-width">
                    <label class="form-label" for="estacionNombre">Nombre de la Estación *</label>
                    <input type="text" class="form-input" id="estacionNombre" name="nombre"
                        placeholder="Ej: Estación Meteorológica Quito" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="estacionCodigo">Código de la Estación *</label>
                    <input type="text" class="form-input" id="estacionCodigo" name="codigo"
                        placeholder="Ej: EM-QUITO-01" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="estacionTag">TAG Código INER *</label>
                    <input type="text" class="form-input" id="estacionTag" name="tag_codigo_iner"
                        placeholder="Ej: INER-QUI-001" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="estacionProvincia">Provincia *</label>
                    <select class="form-select" id="estacionProvincia" name="provincia" required>
                        <option value="">Seleccionar provincia...</option>
                        <option value="Azuay">Azuay</option>
                        <option value="Bolívar">Bolívar</option>
                        <option value="Cañar">Cañar</option>
                        <option value="Chimborazo">Chimborazo</option>
                        <option value="Cotopaxi">Cotopaxi</option>
                        <option value="El Oro">El Oro</option>
                        <option value="Esmeraldas">Esmeraldas</option>
                        <option value="Galápagos">Galápagos</option>
                        <option value="Guayas">Guayas</option>
                        <option value="Imbabura">Imbabura</option>
                        <option value="Loja">Loja</option>
                        <option value="Los Ríos">Los Ríos</option>
                        <option value="Manabí">Manabí</option>
                        <option value="Morona Santiago">Morona Santiago</option>
                        <option value="Napo">Napo</option>
                        <option value="Pastaza">Pastaza</option>
                        <option value="Pichincha">Pichincha</option>
                        <option value="Tungurahua">Tungurahua</option>
                        <option value="Zamora Chinchipe">Zamora Chinchipe</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="estacionCanton">Cantón *</label>
                    <input type="text" class="form-input" id="estacionCanton" name="canton"
                        placeholder="Nombre del cantón" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="estacionParroquia">Parroquia</label>
                    <input type="text" class="form-input" id="estacionParroquia" name="parroquia"
                        placeholder="Nombre de la parroquia">
                </div>

                <div class="form-group">
                    <label class="form-label" for="estacionComunidad">Comunidad</label>
                    <input type="text" class="form-input" id="estacionComunidad" name="comunidad"
                        placeholder="Nombre de la comunidad">
                </div>

                <div class="form-group">
                    <label class="form-label" for="estacionLatitud">Latitud * (formato: -1.65432100)</label>
                    <input type="number" class="form-input" id="estacionLatitud" name="latitud" step="0.00000001"
                        min="-90" max="90" placeholder="-1.65432100" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="estacionLongitud">Longitud * (formato: -78.98765432)</label>
                    <input type="number" class="form-input" id="estacionLongitud" name="longitud" step="0.00000001"
                        min="-180" max="180" placeholder="-78.98765432" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="estacionAltura">Altura del Terreno (m)</label>
                    <input type="number" class="form-input" id="estacionAltura" name="altura_terreno" placeholder="2500"
                        min="0">
                </div>

                <div class="form-group">
                    <label class="form-label" for="estacionFechaInstalacion">Fecha de Instalación</label>
                    <input type="date" class="form-input" id="estacionFechaInstalacion" name="fecha_instalacion">
                </div>

                <div class="form-group">
                    <label class="form-label" for="estacionFotografia">Fotografía de la Estación * (JPG, PNG)</label>
                    <input type="file" class="form-input" id="estacionFotografia" name="fotografia"
                        accept=".jpg,.jpeg,.png" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="estacionMapa">Mapa de Ubicación * (JPG, PNG)</label>
                    <input type="file" class="form-input" id="estacionMapa" name="mapa" accept=".jpg,.jpeg,.png"
                        required>
                </div>
            </div>

            <!-- SECCIÓN DE SENSORES MÍNIMOS -->
            <div class="form-group full-width" style="margin-top: 2rem;">
                <label class="form-label" style="color: var(--primary-cyan); font-size: 1rem;">
                    <i class="fas fa-sensor-on"></i>
                    Sensores (Mínimo 1 requerido) *
                </label>

                <div id="contenedorSensores">
                    <!-- Los sensores se agregarán dinámicamente aquí -->
                </div>

                <button type="button" class="btn-add" onclick="agregarSensorEstacion()" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    Agregar Sensor
                </button>
            </div>

            <!-- SECCIÓN DE COMPONENTES MÍNIMOS -->
            <div class="form-group full-width" style="margin-top: 2rem;">
                <label class="form-label" style="color: var(--primary-cyan); font-size: 1rem;">
                    <i class="fas fa-microchip"></i>
                    Componentes (Mínimo 3 requeridos) *
                </label>

                <div id="contenedorComponentes">
                    <!-- Los componentes se agregarán dinámicamente aquí -->
                </div>

                <button type="button" class="btn-add" onclick="agregarComponenteEstacion()" style="margin-top: 1rem;">
                    <i class="fas fa-plus"></i>
                    Agregar Componente
                </button>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-cancel" onclick="cerrarModal('modalAgregarEstacion')">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i>
                    Crear Estación
                </button>
            </div>
        </form>
    </div>
</div>

<!-- TEMPLATES PARA SENSORES Y COMPONENTES -->
<template id="template-sensor-estacion">
    <div class="detailed-card" style="margin-bottom: 1rem; position: relative;">
        <button type="button" onclick="eliminarElementoEstacion(this)"
            style="position: absolute; top: 10px; right: 10px; background: var(--accent-red); color: white; border: none; padding: 0.3rem 0.6rem; border-radius: 50%; cursor: pointer;">
            <i class="fas fa-times"></i>
        </button>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tipo de Sensor *</label>
                    <select class="form-select" name="sensor_tipo[]" required>
                        <option value="">Cargando...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Marca</label>
                    <input type="text" class="form-input" name="sensor_marca[]" placeholder="Marca del sensor">
                </div>

                <div class="form-group">
                    <label class="form-label">Modelo</label>
                    <input type="text" class="form-input" name="sensor_modelo[]" placeholder="Modelo del sensor">
                </div>

                <div class="form-group">
                    <label class="form-label">Serie</label>
                    <input type="text" class="form-input" name="sensor_serie[]" placeholder="Número de serie">
                </div>

                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select class="form-select" name="sensor_estado[]">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                        <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                        <option value="FUERA_SERVICIO">FUERA_SERVICIO</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha Último Mantenimiento</label>
                    <input type="date" class="form-input" name="sensor_mantenimiento[]">
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-textarea" name="sensor_observaciones[]"
                        placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>
        </div>
    </div>
</template>

<template id="template-componente-estacion">
    <div class="detailed-card" style="margin-bottom: 1rem; position: relative;">
        <button type="button" onclick="eliminarElementoEstacion(this)"
            style="position: absolute; top: 10px; right: 10px; background: var(--accent-red); color: white; border: none; padding: 0.3rem 0.6rem; border-radius: 50%; cursor: pointer;">
            <i class="fas fa-times"></i>
        </button>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tipo de Componente *</label>
                    <select class="form-select" name="componente_tipo[]" required>
                        <option value="">Cargando...</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Marca</label>
                    <input type="text" class="form-input" name="componente_marca[]" placeholder="Marca del componente">
                </div>

                <div class="form-group">
                    <label class="form-label">Modelo</label>
                    <input type="text" class="form-input" name="componente_modelo[]"
                        placeholder="Modelo del componente">
                </div>

                <div class="form-group">
                    <label class="form-label">Serie</label>
                    <input type="text" class="form-input" name="componente_serie[]" placeholder="Número de serie">
                </div>

                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select class="form-select" name="componente_estado[]">
                        <option value="ACTIVO">ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                        <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                        <option value="FUERA_SERVICIO">FUERA_SERVICIO</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Fecha Último Mantenimiento</label>
                    <input type="date" class="form-input" name="componente_mantenimiento[]">
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Especificaciones</label>
                    <textarea class="form-textarea" name="componente_especificaciones[]"
                        placeholder="Especificaciones técnicas..."></textarea>
                </div>

                <div class="form-group full-width">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-textarea" name="componente_observaciones[]"
                        placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
<!-- MODAL DE CARGA PARA ELIMINACIÓN -->
<div class="modal-loading" id="modalLoadingDelete" style="display: none;">
    <div class="modal-loading-content">
        <div class="loading-animation">
            <div class="loading-spinner-large"></div>
        </div>
        <h3 class="loading-title">Eliminando Estación</h3>
        <p class="loading-message">Por favor espere mientras se eliminan todos los datos asociados...</p>
        <div class="loading-progress">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <p class="progress-text" id="progressText">Iniciando eliminación...</p>
        </div>
    </div>
</div>

<style>
    .modal-loading {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.92);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(8px);
    }

    .modal-loading-content {
        background: var(--bg-secondary);
        border: 2px solid var(--primary-cyan);
        border-radius: 20px;
        padding: 3rem 2.5rem;
        max-width: 500px;
        width: 90%;
        text-align: center;
        box-shadow: 0 0 80px rgba(0, 255, 255, 0.4),
            inset 0 0 60px rgba(0, 255, 255, 0.1);
        animation: modalLoadingAppear 0.3s ease-out;
    }

    @keyframes modalLoadingAppear {
        from {
            opacity: 0;
            transform: scale(0.9);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .loading-animation {
        margin-bottom: 2rem;
    }

    .loading-spinner-large {
        width: 80px;
        height: 80px;
        border: 6px solid var(--border-primary);
        border-radius: 50%;
        border-top-color: var(--primary-cyan);
        border-right-color: var(--accent-green);
        animation: spinLarge 1s cubic-bezier(0.68, -0.55, 0.27, 1.55) infinite;
        margin: 0 auto;
    }

    @keyframes spinLarge {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .loading-title {
        color: var(--primary-cyan);
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0 0 0.8rem 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .loading-message {
        color: var(--text-secondary);
        font-size: 0.95rem;
        margin: 0 0 2rem 0;
        line-height: 1.6;
    }

    .loading-progress {
        margin-top: 2rem;
    }

    .progress-bar {
        width: 100%;
        height: 8px;
        background: var(--bg-card);
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--border-secondary);
        margin-bottom: 1rem;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary-cyan), var(--accent-green));
        border-radius: 10px;
        width: 0%;
        transition: width 0.3s ease;
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.6);
    }

    .progress-text {
        color: var(--text-muted);
        font-size: 0.85rem;
        margin: 0;
        font-style: italic;
    }
</style>
<!-- NOTIFICACIÓN -->
<div class="notification" id="notification"></div>

<!-- Font Awesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
    // Variables globales
    let estacionesData = []; // Se cargará dinámicamente desde la base de datos
    let selectedStationId = null;
    let isLoading = false;
    let currentData = null; // Para almacenar los datos actuales
    let deleteType = null; // 'sensor', 'component', o 'station'
    let deleteId = null;

    console.log('📊 Reportes Admin cargado - Modo dinámico activado');

    // ===== CARGAR ESTACIONES DESDE LA BASE DE DATOS =====
    async function loadStationsFromDatabase() {
        try {
            console.log('🔄 Cargando estaciones desde la base de datos...');

            const response = await fetch('../controller/CInfoSensor.php?action=get_stations_for_frontend');

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }

            const data = await response.json();

            if (data.success) {
                estacionesData = data.data;
                window.estacionesData = data.data; // Hacer accesible globalmente
                console.log('✅ Estaciones cargadas:', estacionesData.length);
                console.log('Estaciones:', estacionesData);

                // Generar botones y seleccionar la primera estación
                generateStationButtons();

                // Si hay estaciones, seleccionar la primera
                if (estacionesData.length > 0) {
                    // Buscar estación por defecto (ESPOCH) o usar la primera
                    let defaultStation = estacionesData.find(station => station.isDefault);
                    if (!defaultStation) {
                        defaultStation = estacionesData[0];
                    }

                    console.log('🎯 Seleccionando estación por defecto:', defaultStation.nombre);
                    selectStation(defaultStation.id);
                } else {
                    console.warn('⚠️ No se encontraron estaciones en la base de datos');
                    window.estacionesData = [];
                    showNoStationsMessage();
                }

            } else {
                throw new Error(data.error || 'Error desconocido al cargar estaciones');
            }

        } catch (error) {
            console.error('❌ Error al cargar estaciones:', error);
            showErrorLoadingStations(error.message);
        }
    }

    // ===== GENERAR BOTONES DE ESTACIONES DINÁMICAMENTE =====
    // ===== GENERAR COMBO BOX DE ESTACIONES DINÁMICAMENTE =====
    function generateStationButtons() {
        const selectElement = document.getElementById('stationSelect');
        selectElement.innerHTML = '';

        if (estacionesData.length === 0) {
            selectElement.innerHTML = '<option value="">No hay estaciones disponibles</option>';
            selectElement.disabled = true;
            return;
        }

        // Agregar todas las estaciones (SIN opción por defecto vacía)
        estacionesData.forEach((estacion) => {
            const option = document.createElement('option');
            option.value = estacion.id;
            option.textContent = estacion.nombre;
            if (estacion.isDefault) {
                option.selected = true;
            }
            selectElement.appendChild(option);
        });

        // Evento change
        selectElement.addEventListener('change', function () {
            if (this.value) {
                selectStation(parseInt(this.value));
            }
        });

        selectElement.disabled = false;
        console.log('🎨 Combo box de estaciones generado:', estacionesData.length);
    }

    // ===== SELECCIONAR ESTACIÓN =====
    // ===== SELECCIONAR ESTACIÓN =====
    function selectStation(stationId) {
        if (isLoading || selectedStationId === stationId) return;

        selectedStationId = stationId;
        isLoading = true;

        console.log('📍 Estación seleccionada:', stationId);

        // Cargar contenido
        loadStationContent(stationId);
    }

    // ===== CARGAR CONTENIDO DE LA ESTACIÓN =====
    // ===== CARGAR CONTENIDO DE LA ESTACIÓN =====
    function loadStationContent(stationId) {
        if (!stationId) {
            console.error('No se proporcionó ID de estación para cargar');
            showNoStationsMessage();
            return;
        }

        const contentContainer = document.getElementById('stationContent');

        // Mostrar loading
        contentContainer.innerHTML = `
        <div class="loading-content">
            <div class="loading-spinner"></div>
            <p>Cargando información de la estación...</p>
        </div>
    `;

        // AGREGAR TIMESTAMP PARA FORZAR RECARGA DE IMÁGENES
        const timestamp = new Date().getTime();

        // Realizar petición AJAX
        fetch(`../controller/CInfoSensor.php?id_estacion=${stationId}&_t=${timestamp}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status} - ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                isLoading = false;


                if (data.success) {
                    currentData = data.data; // Guardar datos actuales
                    renderStationContent(data.data);
                    console.log('✅ Contenido de estación cargado exitosamente');
                } else {
                    throw new Error(data.error || 'Error desconocido');
                }
            })
            .catch(error => {
                isLoading = false;
                console.error('Error:', error);

                // Mostrar error mejorado
                let errorMessage = 'Error desconocido al cargar la estación';
                if (error.message.includes('404') || error.message.includes('Not Found')) {
                    errorMessage = 'La estación no fue encontrada. Recargando lista de estaciones...';
                    setTimeout(() => {
                        loadStationsFromDatabase();
                    }, 2000);
                } else if (error.message.includes('500') || error.message.includes('Internal Server Error')) {
                    errorMessage = 'Error del servidor. Intente nuevamente.';
                } else if (error.message.includes('network') || error.message.includes('fetch')) {
                    errorMessage = 'Error de conexión. Verifique su conexión a internet.';
                } else {
                    errorMessage = `Error: ${error.message}`;
                }

                contentContainer.innerHTML = `
                <div class="loading-content">
                    <i class="fas fa-exclamation-triangle" style="color: var(--accent-red); font-size: 2rem; margin-bottom: 1rem;"></i>
                    <p style="color: var(--accent-red);">${errorMessage}</p>
                    <button onclick="loadStationsFromDatabase()" class="btn-secondary" style="margin-top: 1rem; padding: 0.5rem 1rem; border: 1px solid var(--accent-red); background: none; color: var(--accent-red); border-radius: 4px; cursor: pointer;">
                        <i class="fas fa-refresh"></i> Recargar Estaciones
                    </button>
                </div>
            `;
            });
    }

    // ===== RENDERIZAR CONTENIDO DE LA ESTACIÓN =====
    // ===== RENDERIZAR CONTENIDO DE LA ESTACIÓN =====
    function renderStationContent(data) {
        const contentContainer = document.getElementById('stationContent');
        const estacion = data.estacion;
        const componentes = data.componentes || [];
        const sensores = data.sensores || [];
        const imagenes = data.imagenes || {};
        const ultimaLectura = data.ultima_lectura;

        // AGREGAR TIMESTAMP PARA EVITAR CACHÉ DE IMÁGENES
        const timestamp = new Date().getTime();

        // Formatear coordenadas
        const coords = estacion.coordenadas;
        const coordsStr = `${Math.abs(coords.lat).toFixed(4)}°S, ${Math.abs(coords.lng).toFixed(4)}°W`;

        contentContainer.innerHTML = `
        <!-- HEADER: IMAGEN - INFORMACIÓN - IMAGEN -->
        <div class="station-header">
            <!-- IMAGEN 1: MAPA CON TIMESTAMP -->
            <div class="station-image-large">
                <div class="image-label">🗺️ Mapa de Ubicación</div>
                <img src="../public/img/${imagenes.mapa}?t=${timestamp}" alt="Mapa de la estación" 
                     onerror="this.src='../public/img/default.png'">
            </div>
            
            <!-- INFORMACIÓN CENTRAL -->
            <div class="station-info-center" style="position: relative;">
                <!-- Botones de acción para estación -->
                <div class="station-actions">
                    <button class="btn-edit-station" onclick="editarEstacion(${estacion.id})" title="Editar Estación">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn-delete-station" onclick="eliminarEstacion(${estacion.id}, '${estacion.nombre}')" title="Eliminar Estación">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
                
                <div class="info-item-compact">
                    <p class="info-label-compact">📍 Ubicación</p>
                    <p class="info-value-compact">${estacion.ubicacion}</p>
                </div>
                <div class="info-item-compact">
                    <p class="info-label-compact">🏔️ Elevación</p>
                    <p class="info-value-compact">${estacion.elevacion}</p>
                </div>
                <div class="info-item-compact">
                    <p class="info-label-compact">🏷️ Código</p>
                    <p class="info-value-compact">${estacion.codigo}</p>
                </div>
                <div class="info-item-compact">
                    <p class="info-label-compact">⚡ Estado</p>
                    <p class="info-value-compact ${estacion.status === 'active' ? 'status-active' : 'status-offline'}">
                        ${estacion.estado === 'ACTIVA' ? '🟢 Activa' : '🔴 Inactiva'}
                    </p>
                </div>
                <div class="info-item-compact">
                    <p class="info-label-compact">🌐 Coordenadas</p>
                    <p class="info-value-compact">${coordsStr}</p>
                </div>
                
                <div class="info-item-compact">
                    <p class="info-label-compact">🏷️ TAG INER</p>
                    <p class="info-value-compact">${estacion.tag || 'N/A'}</p>
                </div>
            </div>
            
            <!-- IMAGEN 2: FOTOGRAFÍA CON TIMESTAMP -->
            <div class="station-image-large">
                <div class="image-label">📸 Fotografía de la Estación</div>
                <img src="../public/img/${imagenes.fotografia}?t=${timestamp}" alt="Fotografía de la estación" 
                     onerror="this.src='../public/img/default.png'">
            </div>
        </div>

        <!-- COMPONENTES -->
        <div class="components-sensors-section">
            <h4 class="section-title">
                <i class="fas fa-microchip"></i>
                Componentes de la Estación
                <span style="margin-left: auto; font-size: 0.85rem; opacity: 0.8;">(${componentes.length})</span>
                <div class="section-actions">
                    <button class="btn-add" onclick="abrirModalAgregarComponente()">
                        <i class="fas fa-plus"></i>
                        Agregar
                    </button>
                </div>
            </h4>
            ${componentes.length > 0 ? `
                <div class="detailed-grid">
                    ${componentes.map((comp, index) => `
                        <div class="detailed-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <div class="card-icon">${index + 1}</div>
                                    ${comp.tipo}
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card-row">
                                    <span class="card-label">Marca:</span>
                                    <span class="card-value">${comp.marca || 'N/A'}</span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Modelo:</span>
                                    <span class="card-value">${comp.modelo || 'N/A'}</span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Serie:</span>
                                    <span class="card-value">${comp.serie || 'N/A'}</span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Estado:</span>
                                    <span class="card-value">${comp.estado || 'N/A'}</span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Mantenimiento:</span>
                                    <span class="card-value">${comp.fecha_mantenimiento || 'Pendiente'}</span>
                                </div>
                            </div>
                            ${comp.especificaciones ? `
                                <div class="card-footer">
                                    <div class="card-notes">
                                        <strong>Especificaciones:</strong> ${comp.especificaciones}
                                    </div>
                                </div>
                            ` : ''}
                            ${comp.observaciones ? `
                                <div class="card-footer">
                                    <div class="card-notes">
                                        <strong>Observaciones:</strong> ${comp.observaciones}
                                    </div>
                                </div>
                            ` : ''}
                            <div class="card-actions">
                                <button class="btn-edit" onclick="editarComponente(${comp.id})">
                                    <i class="fas fa-edit"></i>
                                    Editar
                                </button>
                                <button class="btn-delete" onclick="eliminarComponente(${comp.id})">
                                    <i class="fas fa-trash"></i>
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            ` : `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No hay componentes registrados para esta estación</p>
                    <button class="btn-add" onclick="abrirModalAgregarComponente()" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i>
                        Agregar Primer Componente
                    </button>
                </div>
            `}
        </div>

        <!-- SENSORES -->
        <div class="components-sensors-section">
            <h4 class="section-title">
                <i class="fas fa-sensor-on"></i>
                Sensores de la Estación
                <span style="margin-left: auto; font-size: 0.85rem; opacity: 0.8;">(${sensores.length})</span>
                <div class="section-actions">
                    <button class="btn-add" onclick="abrirModalAgregarSensor()">
                        <i class="fas fa-plus"></i>
                        Agregar
                    </button>
                </div>
            </h4>
            ${sensores.length > 0 ? `
                <div class="detailed-grid">
                    ${sensores.map((sensor, index) => `
                        <div class="detailed-card">
                            <div class="card-header">
                                <div class="card-title">
                                    <div class="card-icon">${index + 1}</div>
                                    ${sensor.tipo}
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card-row">
                                    <span class="card-label">Marca:</span>
                                    <span class="card-value">${sensor.marca || 'N/A'}</span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Modelo:</span>
                                    <span class="card-value">${sensor.modelo || 'N/A'}</span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Serie:</span>
                                    <span class="card-value">${sensor.serie || 'N/A'}</span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Estado:</span>
                                    <span class="card-value">${sensor.estado || 'N/A'}</span>
                                </div>
                                <div class="card-row">
                                    <span class="card-label">Mantenimiento:</span>
                                    <span class="card-value">${sensor.fecha_mantenimiento || 'Pendiente'}</span>
                                </div>
                            </div>
                            ${sensor.observaciones ? `
                                <div class="card-footer">
                                    <div class="card-notes">
                                        <strong>Observaciones:</strong> ${sensor.observaciones}
                                    </div>
                                </div>
                            ` : ''}
                            <div class="card-actions">
                                <button class="btn-edit" onclick="editarSensor(${sensor.id})">
                                    <i class="fas fa-edit"></i>
                                    Editar
                                </button>
                                <button class="btn-delete" onclick="eliminarSensor(${sensor.id})">
                                    <i class="fas fa-trash"></i>
                                    Eliminar
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            ` : `
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No hay sensores registrados para esta estación</p>
                    <button class="btn-add" onclick="abrirModalAgregarSensor()" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i>
                        Agregar Primer Sensor
                    </button>
                </div>
            `}
        </div>
    `;
    }

    // ===== FUNCIONES PARA MANEJO DE MODALES =====
    function abrirModal(modalId) {
        document.getElementById(modalId).classList.add('show');
    }

    function cerrarModal(modalId) {
        document.getElementById(modalId).classList.remove('show');
    }

    // Cerrar modal al hacer click fuera del contenido
    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('show');
        }
    });

    // ===== FUNCIONES PARA EDICIÓN DE ESTACIÓN =====
    function editarEstacion(estacionId) {
        // Cargar datos de la estación actual
        if (!currentData || !currentData.estacion) {
            mostrarNotificacion('No hay datos de estación disponibles', 'error');
            return;
        }

        const estacion = currentData.estacion;

        // Llenar el formulario con los datos actuales
        document.getElementById('editEstacionId').value = estacion.id;

        // ✅ CAMBIO CRÍTICO: Usar el campo correcto
        document.getElementById('editEstacionNombre').value = estacion.nombre;

        document.getElementById('editEstacionCodigo').value = estacion.codigo || '';
        document.getElementById('editEstacionTag').value = estacion.tag || '';
        document.getElementById('editEstacionLatitud').value = estacion.coordenadas.lat || '';
        document.getElementById('editEstacionLongitud').value = estacion.coordenadas.lng || '';
        document.getElementById('editEstacionAltura').value = estacion.elevacion ? estacion.elevacion.replace('m', '') : '';
        document.getElementById('editEstacionFechaInstalacion').value = estacion.instalacion || '';

        // Dividir ubicación en provincia, cantón, parroquia, comunidad
        const ubicacion = estacion.ubicacion || '';
        const partes = ubicacion.split(', ').map(p => p.trim());

        if (partes.length >= 1) document.getElementById('editEstacionComunidad').value = partes[0] || '';
        if (partes.length >= 2) document.getElementById('editEstacionCanton').value = partes[1] || '';
        if (partes.length >= 3) {
            // La provincia debe ser una de las opciones del select
            const provincia = partes[2];
            const selectProvincia = document.getElementById('editEstacionProvincia');
            selectProvincia.value = provincia;
        }

        // Limpiar previews de imágenes
        document.getElementById('fotoPreview').innerHTML = '';
        document.getElementById('mapaPreview').innerHTML = '';

        // Limpiar inputs de archivo
        document.getElementById('editEstacionFotografia').value = '';
        document.getElementById('editEstacionMapa').value = '';

        abrirModal('modalEditarEstacion');
    }

    function eliminarEstacion(estacionId, nombreEstacion) {
        deleteType = 'station';
        deleteId = estacionId;
        document.getElementById('confirmDeleteMessage').textContent =
            `¿Está seguro de que desea eliminar la estación "${nombreEstacion}"? Esta acción eliminará también todos sus sensores, componentes y datos asociados.`;
        abrirModal('modalConfirmDelete');
    }

    // ===== FUNCIONES PARA EDICIÓN DE SENSORES =====
    async function editarSensor(sensorId) {
        try {
            const response = await fetch(`../controller/CInfoSensor.php?action=get_sensor&id_sensor=${sensorId}`);
            const data = await response.json();

            if (data.success) {
                const sensor = data.data;

                await cargarTiposSensores('editSensorTipo');

                document.getElementById('editSensorId').value = sensor.id;
                document.getElementById('editSensorEstacion').value = sensor.id_estacion;
                document.getElementById('editSensorTipo').value = sensor.id_catalogo_sensor;
                document.getElementById('editSensorMarca').value = sensor.marca || '';
                document.getElementById('editSensorModelo').value = sensor.modelo || '';
                document.getElementById('editSensorSerie').value = sensor.serie || '';
                document.getElementById('editSensorEstado').value = sensor.estado;
                document.getElementById('editSensorMantenimiento').value = sensor.fecha_mantenimiento || '';
                document.getElementById('editSensorObservaciones').value = sensor.observaciones || '';

                // CAMBIO AQUÍ: Usar pointer-events: none en lugar de disabled
                const tipoSelect = document.getElementById('editSensorTipo');
                tipoSelect.style.pointerEvents = 'none';
                tipoSelect.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
                tipoSelect.style.color = 'var(--text-primary)';
                tipoSelect.style.opacity = '0.7';

                abrirModal('modalEditarSensor');
            } else {
                mostrarNotificacion('Error al cargar datos del sensor', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('Error al cargar datos del sensor', 'error');
        }
    }

    async function eliminarSensor(sensorId) {
        deleteType = 'sensor';
        deleteId = sensorId;
        document.getElementById('confirmDeleteMessage').textContent = '¿Está seguro de que desea eliminar este sensor?';
        abrirModal('modalConfirmDelete');
    }

    // ===== FUNCIONES PARA EDICIÓN DE COMPONENTES =====
    async function editarComponente(componenteId) {
        try {
            const response = await fetch(`../controller/CInfoSensor.php?action=get_component&id_componente=${componenteId}`);
            const data = await response.json();

            if (data.success) {
                const componente = data.data;

                await cargarTiposComponentes('editComponenteTipo');

                document.getElementById('editComponenteId').value = componente.id;
                document.getElementById('editComponenteEstacion').value = componente.id_estacion;
                document.getElementById('editComponenteTipo').value = componente.id_catalogo_componente;
                document.getElementById('editComponenteMarca').value = componente.marca || '';
                document.getElementById('editComponenteModelo').value = componente.modelo || '';
                document.getElementById('editComponenteSerie').value = componente.serie || '';
                document.getElementById('editComponenteEstado').value = componente.estado;
                document.getElementById('editComponenteMantenimiento').value = componente.fecha_mantenimiento || '';
                document.getElementById('editComponenteEspecificaciones').value = componente.especificaciones || '';
                document.getElementById('editComponenteObservaciones').value = componente.observaciones || '';

                // CAMBIO AQUÍ: Usar pointer-events: none en lugar de disabled
                const tipoSelect = document.getElementById('editComponenteTipo');
                tipoSelect.style.pointerEvents = 'none';
                tipoSelect.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
                tipoSelect.style.color = 'var(--text-primary)';
                tipoSelect.style.opacity = '0.7';

                abrirModal('modalEditarComponente');
            } else {
                mostrarNotificacion('Error al cargar datos del componente', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('Error al cargar datos del componente', 'error');
        }
    }

    async function eliminarComponente(componenteId) {
        deleteType = 'component';
        deleteId = componenteId;
        document.getElementById('confirmDeleteMessage').textContent = '¿Está seguro de que desea eliminar este componente?';
        abrirModal('modalConfirmDelete');
    }

    async function confirmarEliminacion() {
        if (!deleteType || !deleteId) return;

        try {
            let action, idField;

            if (deleteType === 'station') {
                action = 'delete_station';
                idField = 'id_estacion';
            } else if (deleteType === 'sensor') {
                action = 'delete_sensor';
                idField = 'id_sensor';
            } else if (deleteType === 'component') {
                action = 'delete_component';
                idField = 'id_componente';
            } else if (deleteType === 'tipo_sensor') {
                action = 'delete_tipo_sensor';
                idField = 'id';
            } else if (deleteType === 'tipo_componente') {
                action = 'delete_tipo_componente';
                idField = 'id';
            } else if (deleteType === 'variable') {
                action = 'delete_variable';
                idField = 'id';
            }

            // ✅ Cerrar modal de confirmación
            cerrarModal('modalConfirmDelete');

            // ✅ MOSTRAR MODAL DE CARGA SOLO SI ES ESTACIÓN
            if (deleteType === 'station') {
                mostrarModalCargaEliminacion();
            }

            const response = await fetch('../controller/CInfoSensor.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action,
                    [idField]: deleteId
                })
            });

            const data = await response.json();

            if (data.success) {
                let mensaje = '';

                switch (deleteType) {
                    case 'station':
                        mensaje = '✅ Estación eliminada correctamente';
                        break;
                    case 'sensor':
                        mensaje = '✅ Sensor eliminado correctamente';
                        break;
                    case 'component':
                        mensaje = '✅ Componente eliminado correctamente';
                        break;
                    case 'tipo_sensor':
                        mensaje = '✅ Tipo de sensor eliminado correctamente';
                        break;
                    case 'tipo_componente':
                        mensaje = '✅ Tipo de componente eliminado correctamente';
                        break;
                    case 'variable':
                        mensaje = '✅ Variable eliminada correctamente';
                        break;
                }

                mostrarNotificacion(mensaje, 'success');

                // ✅ RECARGAR PÁGINA DESPUÉS DE 1 SEGUNDO PARA TODOS LOS CASOS
                setTimeout(() => {
                    window.location.reload();
                }, 1000);

            } else {
                // ✅ Cerrar modal de carga si hay error
                if (deleteType === 'station') {
                    ocultarModalCargaEliminacion();
                }
                mostrarNotificacion('❌ ' + (data.message || 'Error al eliminar'), 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            // ✅ Cerrar modal de carga si hay error
            if (deleteType === 'station') {
                ocultarModalCargaEliminacion();
            }
            mostrarNotificacion('❌ Error al eliminar el elemento', 'error');
        } finally {
            deleteType = null;
            deleteId = null;
        }
    }

    // ✅ NUEVA FUNCIÓN: Mostrar modal de carga con animación de progreso
    function mostrarModalCargaEliminacion() {
        const modal = document.getElementById('modalLoadingDelete');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');

        modal.style.display = 'flex';

        // Simular progreso realista
        let progress = 0;
        const messages = [
            'Iniciando eliminación...',
            'Eliminando sensores asociados...',
            'Eliminando componentes asociados...',
            'Eliminando datos de la estación...',
            'Limpiando archivos...',
            'Finalizando proceso...'
        ];

        const interval = setInterval(() => {
            if (progress < 95) {
                // Progreso más lento al inicio, más rápido al final
                const increment = progress < 30 ? 2 : progress < 70 ? 3 : 5;
                progress += increment;

                progressFill.style.width = progress + '%';

                // Cambiar mensaje según el progreso
                const messageIndex = Math.floor((progress / 100) * messages.length);
                if (messageIndex < messages.length) {
                    progressText.textContent = messages[messageIndex];
                }
            }
        }, 200);

        // Guardar el interval para poder limpiarlo después
        modal.dataset.intervalId = interval;
    }

    // ✅ NUEVA FUNCIÓN: Ocultar modal de carga
    function ocultarModalCargaEliminacion() {
        const modal = document.getElementById('modalLoadingDelete');
        const intervalId = modal.dataset.intervalId;

        if (intervalId) {
            clearInterval(parseInt(intervalId));
        }

        // Completar progreso antes de cerrar
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');

        progressFill.style.width = '100%';
        progressText.textContent = 'Completado';

        setTimeout(() => {
            modal.style.display = 'none';
            progressFill.style.width = '0%';
            progressText.textContent = 'Iniciando eliminación...';
        }, 500);
    }
    // ===== FUNCIONES PARA AGREGAR NUEVOS REGISTROS =====
    async function abrirModalAgregarSensor() {
        document.getElementById('addSensorEstacion').value = selectedStationId;
        await cargarTiposSensores('addSensorTipo');
        abrirModal('modalAgregarSensor');
    }

    async function abrirModalAgregarComponente() {
        document.getElementById('addComponenteEstacion').value = selectedStationId;
        await cargarTiposComponentes('addComponenteTipo');
        abrirModal('modalAgregarComponente');
    }

    // ===== FUNCIONES PARA CARGAR OPCIONES =====
    async function cargarTiposSensores(selectId) {
        try {
            const response = await fetch('../controller/CInfoSensor.php?action=get_sensor_types');
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById(selectId);
                const currentValue = select.value;

                select.innerHTML = '<option value="">Seleccionar tipo...</option>';

                data.data.forEach(tipo => {
                    const option = document.createElement('option');
                    option.value = tipo.id;
                    option.textContent = tipo.nombre;
                    if (tipo.descripcion) {
                        option.title = tipo.descripcion;
                    }
                    select.appendChild(option);
                });

                if (currentValue) {
                    select.value = currentValue;
                }

                console.log('Tipos de sensores cargados:', data.data.length);
            }
        } catch (error) {
            console.error('Error al cargar tipos de sensores:', error);
            mostrarNotificacion('Error al cargar tipos de sensores', 'error');
        }
    }

    async function cargarTiposComponentes(selectId) {
        try {
            const response = await fetch('../controller/CInfoSensor.php?action=get_component_types');
            const data = await response.json();

            if (data.success) {
                const select = document.getElementById(selectId);
                const currentValue = select.value;

                select.innerHTML = '<option value="">Seleccionar tipo...</option>';

                data.data.forEach(tipo => {
                    const option = document.createElement('option');
                    option.value = tipo.id;
                    option.textContent = tipo.nombre;
                    if (tipo.descripcion) {
                        option.title = tipo.descripcion;
                    }
                    select.appendChild(option);
                });

                if (currentValue) {
                    select.value = currentValue;
                }

                console.log('Tipos de componentes cargados:', data.data.length);
            }
        } catch (error) {
            console.error('Error al cargar tipos de componentes:', error);
            mostrarNotificacion('Error al cargar tipos de componentes', 'error');
        }
    }

    // ===== FUNCIONES PARA MANEJO DE FORMULARIOS =====

    document.getElementById('formEditarEstacion').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        try {
            const response = await fetch('../controller/CInfoSensor.php?action=update_station', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                cerrarModal('modalEditarEstacion');

                // ✅ RECARGAR PÁGINA DESPUÉS DE 1 SEGUNDO
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                mostrarNotificacion('❌ Error al actualizar estación: ' + result.error, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('❌ Error al actualizar estación', 'error');
        }
    });

    // Previsualización de imágenes en el modal de editar estación
    document.getElementById('editEstacionFotografia').addEventListener('change', function (e) {
        const preview = document.getElementById('fotoPreview');
        const file = e.target.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = `
                    <div style="margin-top: 0.5rem;">
                        <strong>Nueva fotografía:</strong><br>
                        <img src="${e.target.result}" class="image-preview" alt="Preview">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.3rem;">${file.name}</p>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    });

    document.getElementById('editEstacionMapa').addEventListener('change', function (e) {
        const preview = document.getElementById('mapaPreview');
        const file = e.target.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = `
                    <div style="margin-top: 0.5rem;">
                        <strong>Nuevo mapa:</strong><br>
                        <img src="${e.target.result}" class="image-preview" alt="Preview">
                        <p style="font-size: 0.7rem; color: var(--text-muted); margin-top: 0.3rem;">${file.name}</p>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    });

    // Formulario editar sensor
    document.getElementById('formEditarSensor').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = {
            action: 'update_sensor',
            id_sensor: formData.get('id_sensor'),
            marca: formData.get('marca'),
            modelo: formData.get('modelo'),
            serie: formData.get('serie'),
            fecha_mantenimiento: formData.get('fecha_mantenimiento'),
            estado: formData.get('estado'),
            observaciones: formData.get('observaciones')
        };

        try {
            const response = await fetch('../controller/CInfoSensor.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                mostrarNotificacion('✅ Sensor actualizado correctamente', 'success');
                cerrarModal('modalEditarSensor');

                // ✅ RECARGAR PÁGINA DESPUÉS DE 1 SEGUNDO
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                mostrarNotificacion('❌ Error al actualizar sensor', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('❌ Error al actualizar sensor', 'error');
        }
    });

    // Formulario editar componente
    document.getElementById('formEditarComponente').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = {
            action: 'update_component',
            id_componente: formData.get('id_componente'),
            marca: formData.get('marca'),
            modelo: formData.get('modelo'),
            serie: formData.get('serie'),
            fecha_mantenimiento: formData.get('fecha_mantenimiento'),
            estado: formData.get('estado'),
            especificaciones: formData.get('especificaciones'),
            observaciones: formData.get('observaciones')
        };

        try {
            const response = await fetch('../controller/CInfoSensor.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                mostrarNotificacion('✅ Componente actualizado correctamente', 'success');
                cerrarModal('modalEditarComponente');

                // ✅ RECARGAR PÁGINA DESPUÉS DE 1 SEGUNDO
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                mostrarNotificacion('❌ Error al actualizar componente', 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('❌ Error al actualizar componente', 'error');
        }
    });

    // Formulario agregar sensor
    document.getElementById('formAgregarSensor').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = {
            action: 'add_sensor',
            id_estacion: formData.get('id_estacion'),
            id_catalogo_sensor: formData.get('tipo_sensor'),
            marca: formData.get('marca'),
            modelo: formData.get('modelo'),
            serie: formData.get('serie'),
            fecha_mantenimiento: formData.get('fecha_mantenimiento'),
            estado: formData.get('estado'),
            observaciones: formData.get('observaciones')
        };

        try {
            const response = await fetch('../controller/CInfoSensor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                mostrarNotificacion('✅ Sensor agregado correctamente', 'success');
                cerrarModal('modalAgregarSensor');

                // ✅ RECARGAR PÁGINA DESPUÉS DE 1 SEGUNDO
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                const errorMsg = result.error || 'Error al agregar sensor';
                console.error('Error del servidor:', errorMsg);
                mostrarNotificacion('❌ Error al agregar sensor: ' + errorMsg, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('❌ Error al agregar sensor', 'error');
        }
    });

    // Formulario agregar componente
    document.getElementById('formAgregarComponente').addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const data = {
            action: 'add_component',
            id_estacion: formData.get('id_estacion'),
            id_catalogo_componente: formData.get('tipo_componente'),
            marca: formData.get('marca'),
            modelo: formData.get('modelo'),
            serie: formData.get('serie'),
            fecha_mantenimiento: formData.get('fecha_mantenimiento'),
            estado: formData.get('estado'),
            especificaciones: formData.get('especificaciones'),
            observaciones: formData.get('observaciones')
        };

        try {
            const response = await fetch('../controller/CInfoSensor.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                mostrarNotificacion('✅ Componente agregado correctamente', 'success');
                cerrarModal('modalAgregarComponente');

                // ✅ RECARGAR PÁGINA DESPUÉS DE 1 SEGUNDO
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                const errorMsg = result.error || 'Error al agregar componente';
                console.error('Error del servidor:', errorMsg);
                mostrarNotificacion('❌ Error al agregar componente: ' + errorMsg, 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('❌ Error al agregar componente', 'error');
        }
    });

    // ===== FUNCIONES DE UTILIDAD =====
    function mostrarNotificacion(mensaje, tipo) {
        const notification = document.getElementById('notification');
        notification.textContent = mensaje;
        notification.className = `notification ${tipo}`;
        notification.classList.add('show');

        // ✅ CAMBIO: Errores permanecen hasta hacer clic
        if (tipo === 'error') {
            notification.style.cursor = 'pointer';
            notification.title = 'Haz clic para cerrar';

            // Cerrar solo al hacer clic
            notification.onclick = function () {
                notification.classList.remove('show');
                notification.onclick = null;
            };
        } else {
            // Éxitos desaparecen automáticamente en 5 segundos
            setTimeout(() => {
                notification.classList.remove('show');
            }, 5000);
        }
    }

    function showNoStationsMessage() {
        // Limpiar variables globales
        selectedStationId = null;
        currentData = null;
        estacionesData = [];
        window.estacionesData = [];

        const container = document.getElementById('stationButtons');
        container.innerHTML = `
            <div class="loading-content" style="padding: 2rem; color: var(--text-muted);">
                <i class="fas fa-info-circle" style="color: var(--accent-orange); font-size: 2rem; margin-bottom: 1rem;"></i>
                <p>No hay estaciones registradas en la base de datos</p>
                <p style="font-size: 0.8rem; margin-top: 0.5rem;">Contacte al administrador para agregar estaciones</p>
            </div>
        `;

        const contentContainer = document.getElementById('stationContent');
        contentContainer.innerHTML = `
            <div class="loading-content">
                <i class="fas fa-inbox" style="color: var(--text-muted); font-size: 2rem; margin-bottom: 1rem;"></i>
                <p style="color: var(--text-muted);">No hay estaciones disponibles para mostrar</p>
            </div>
        `;
    }

    function showErrorLoadingStations(errorMessage) {
        const container = document.getElementById('stationButtons');
        container.innerHTML = `
            <div class="loading-content" style="padding: 2rem; color: var(--text-muted);">
                <i class="fas fa-exclamation-triangle" style="color: var(--accent-red); font-size: 2rem; margin-bottom: 1rem;"></i>
                <p style="color: var(--accent-red);">Error al cargar estaciones</p>
                <p style="font-size: 0.8rem; margin-top: 0.5rem; color: var(--text-muted);">${errorMessage}</p>
                <button onclick="loadStationsFromDatabase()" style="margin-top: 1rem; background: var(--primary-cyan); color: #0a0e1a; border: none; padding: 0.5rem 1rem; border-radius: 6px; cursor: pointer;">
                    <i class="fas fa-refresh"></i> Reintentar
                </button>
            </div>
        `;
    }

    // ===== FUNCIONES PARA AGREGAR ESTACIÓN =====
    async function abrirModalAgregarEstacion() {
        console.log('🔍 DEBUG: Intentando abrir modal agregar estación');

        // Resetear el formulario
        const form = document.getElementById('formAgregarEstacion');
        console.log('🔍 DEBUG: Formulario encontrado:', form !== null);
        form.reset();

        const contenedorSensores = document.getElementById('contenedorSensores');
        const contenedorComponentes = document.getElementById('contenedorComponentes');

        console.log('🔍 DEBUG: Contenedores encontrados:', {
            sensores: contenedorSensores !== null,
            componentes: contenedorComponentes !== null
        });

        contenedorSensores.innerHTML = '';
        contenedorComponentes.innerHTML = '';

        // ✅ CRÍTICO: Esperar a que se agreguen y carguen los elementos
        console.log('🔍 DEBUG: Agregando elementos por defecto...');

        // Agregar sensor y esperar a que cargue
        await agregarSensorEstacion();

        // Agregar componentes y esperar a que carguen
        await agregarComponenteEstacion();
        await agregarComponenteEstacion();
        await agregarComponenteEstacion();

        console.log('🔍 DEBUG: Elementos agregados y cargados, abriendo modal...');

        abrirModal('modalAgregarEstacion');
        console.log('🔍 DEBUG: Modal abierto');
    }

    async function agregarSensorEstacion() {
        console.log('🔧 Agregando nuevo sensor...');

        const contenedor = document.getElementById('contenedorSensores');

        // Crear el HTML directamente (sin template)
        const sensorCard = document.createElement('div');
        sensorCard.className = 'detailed-card';
        sensorCard.style.marginBottom = '1rem';
        sensorCard.style.position = 'relative';

        sensorCard.innerHTML = `
        <button type="button" onclick="eliminarElementoEstacion(this)" style="position: absolute; top: 10px; right: 10px; background: var(--accent-red); color: white; border: none; padding: 0.3rem 0.6rem; border-radius: 50%; cursor: pointer;">
            <i class="fas fa-times"></i>
        </button>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tipo de Sensor *</label>
                    <select class="form-select" name="sensor_tipo[]" required disabled>
                        <option value="">⏳ Cargando tipos de sensores...</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Marca</label>
                    <input type="text" class="form-input" name="sensor_marca[]" placeholder="Marca del sensor">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Modelo</label>
                    <input type="text" class="form-input" name="sensor_modelo[]" placeholder="Modelo del sensor">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Serie</label>
                    <input type="text" class="form-input" name="sensor_serie[]" placeholder="Número de serie">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select class="form-select" name="sensor_estado[]">
                        <option value="ACTIVO" selected>ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                        <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                        <option value="FUERA_SERVICIO">FUERA_SERVICIO</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Fecha Último Mantenimiento</label>
                    <input type="date" class="form-input" name="sensor_mantenimiento[]">
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-textarea" name="sensor_observaciones[]" placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>
        </div>
    `;

        // Agregar al contenedor
        contenedor.appendChild(sensorCard);

        // Ahora buscar el select que acabamos de agregar
        const selects = contenedor.querySelectorAll('select[name="sensor_tipo[]"]');
        const nuevoSelect = selects[selects.length - 1];

        console.log('🔍 Select agregado al DOM:', nuevoSelect);
        console.log('🔍 Contenido inicial del select:', nuevoSelect.innerHTML);

        // Cargar los tipos
        try {
            const response = await fetch('../controller/CInfoSensor.php?action=get_sensor_types');
            const data = await response.json();

            console.log('📥 Respuesta del servidor:', data);

            if (data.success && data.data.length > 0) {
                // Limpiar el select
                nuevoSelect.innerHTML = '';

                // Agregar opción por defecto
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Seleccionar tipo...';
                nuevoSelect.appendChild(defaultOption);

                // Agregar las opciones reales
                data.data.forEach(tipo => {
                    const option = document.createElement('option');
                    option.value = String(tipo.id);
                    option.textContent = tipo.nombre;
                    nuevoSelect.appendChild(option);
                    console.log(`✅ Opción agregada: ${tipo.id} - ${tipo.nombre}`);
                });

                // Habilitar el select
                nuevoSelect.disabled = false;

                console.log('✅ Select cargado. Contenido final:', nuevoSelect.innerHTML);
                console.log('✅ Total opciones:', nuevoSelect.options.length);

            } else {
                nuevoSelect.innerHTML = '<option value="">❌ No hay tipos disponibles</option>';
                console.error('❌ No hay datos:', data);
            }
        } catch (error) {
            console.error('❌ Error al cargar tipos:', error);
            nuevoSelect.innerHTML = '<option value="">❌ Error de conexión</option>';
        }
    }

    async function agregarComponenteEstacion() {
        console.log('🔧 Agregando nuevo componente...');

        const contenedor = document.getElementById('contenedorComponentes');

        // Crear el HTML directamente (sin template)
        const componenteCard = document.createElement('div');
        componenteCard.className = 'detailed-card';
        componenteCard.style.marginBottom = '1rem';
        componenteCard.style.position = 'relative';

        componenteCard.innerHTML = `
        <button type="button" onclick="eliminarElementoEstacion(this)" style="position: absolute; top: 10px; right: 10px; background: var(--accent-red); color: white; border: none; padding: 0.3rem 0.6rem; border-radius: 50%; cursor: pointer;">
            <i class="fas fa-times"></i>
        </button>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Tipo de Componente *</label>
                    <select class="form-select" name="componente_tipo[]" required disabled>
                        <option value="">⏳ Cargando tipos de componentes...</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Marca</label>
                    <input type="text" class="form-input" name="componente_marca[]" placeholder="Marca del componente">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Modelo</label>
                    <input type="text" class="form-input" name="componente_modelo[]" placeholder="Modelo del componente">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Serie</label>
                    <input type="text" class="form-input" name="componente_serie[]" placeholder="Número de serie">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Estado</label>
                    <select class="form-select" name="componente_estado[]">
                        <option value="ACTIVO" selected>ACTIVO</option>
                        <option value="INACTIVO">INACTIVO</option>
                        <option value="MANTENIMIENTO">MANTENIMIENTO</option>
                        <option value="FUERA_SERVICIO">FUERA_SERVICIO</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Fecha Último Mantenimiento</label>
                    <input type="date" class="form-input" name="componente_mantenimiento[]">
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">Especificaciones</label>
                    <textarea class="form-textarea" name="componente_especificaciones[]" placeholder="Especificaciones técnicas..."></textarea>
                </div>
                
                <div class="form-group full-width">
                    <label class="form-label">Observaciones</label>
                    <textarea class="form-textarea" name="componente_observaciones[]" placeholder="Observaciones adicionales..."></textarea>
                </div>
            </div>
        </div>
    `;

        // Agregar al contenedor
        contenedor.appendChild(componenteCard);

        // Ahora buscar el select que acabamos de agregar
        const selects = contenedor.querySelectorAll('select[name="componente_tipo[]"]');
        const nuevoSelect = selects[selects.length - 1];

        console.log('🔍 Select de componente agregado al DOM:', nuevoSelect);

        // Cargar los tipos
        try {
            const response = await fetch('../controller/CInfoSensor.php?action=get_component_types');
            const data = await response.json();

            console.log('📥 Respuesta del servidor (componentes):', data);

            if (data.success && data.data.length > 0) {
                // Limpiar el select
                nuevoSelect.innerHTML = '';

                // Agregar opción por defecto
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'Seleccionar tipo...';
                nuevoSelect.appendChild(defaultOption);

                // Agregar las opciones reales
                data.data.forEach(tipo => {
                    const option = document.createElement('option');
                    option.value = String(tipo.id);
                    option.textContent = tipo.nombre;
                    nuevoSelect.appendChild(option);
                    console.log(`✅ Opción de componente agregada: ${tipo.id} - ${tipo.nombre}`);
                });

                // Habilitar el select
                nuevoSelect.disabled = false;

                console.log('✅ Select de componente cargado. Total opciones:', nuevoSelect.options.length);

            } else {
                nuevoSelect.innerHTML = '<option value="">❌ No hay tipos disponibles</option>';
                console.error('❌ No hay datos de componentes:', data);
            }
        } catch (error) {
            console.error('❌ Error al cargar tipos de componentes:', error);
            nuevoSelect.innerHTML = '<option value="">❌ Error de conexión</option>';
        }
    }

    function eliminarElementoEstacion(boton) {
        const tarjeta = boton.closest('.detailed-card');
        tarjeta.remove();
    }

    // ===== CORRECCIÓN PRINCIPAL: Formulario agregar estación =====
    document.getElementById('formAgregarEstacion').addEventListener('submit', async function (e) {
        e.preventDefault();

        console.log('📝 Validando formulario de nueva estación...');

        // 1️⃣ VALIDAR CAMPOS BÁSICOS
        const nombre = document.getElementById('estacionNombre').value.trim();
        const codigo = document.getElementById('estacionCodigo').value.trim();
        const tag = document.getElementById('estacionTag').value.trim();
        const provincia = document.getElementById('estacionProvincia').value;
        const canton = document.getElementById('estacionCanton').value.trim();
        const latitud = document.getElementById('estacionLatitud').value;
        const longitud = document.getElementById('estacionLongitud').value;

        if (!nombre) {
            mostrarNotificacion('❌ ERROR: El campo "Nombre de la Estación" es obligatorio', 'error');
            document.getElementById('estacionNombre').focus();
            return;
        }

        if (!codigo) {
            mostrarNotificacion('❌ ERROR: El campo "Código de la Estación" es obligatorio', 'error');
            document.getElementById('estacionCodigo').focus();
            return;
        }

        if (!tag) {
            mostrarNotificacion('❌ ERROR: El campo "TAG Código INER" es obligatorio', 'error');
            document.getElementById('estacionTag').focus();
            return;
        }

        if (!provincia) {
            mostrarNotificacion('❌ ERROR: Debe seleccionar una "Provincia"', 'error');
            document.getElementById('estacionProvincia').focus();
            return;
        }

        if (!canton) {
            mostrarNotificacion('❌ ERROR: El campo "Cantón" es obligatorio', 'error');
            document.getElementById('estacionCanton').focus();
            return;
        }

        if (!latitud || !longitud) {
            mostrarNotificacion('❌ ERROR: Las coordenadas (Latitud y Longitud) son obligatorias', 'error');
            if (!latitud) document.getElementById('estacionLatitud').focus();
            else document.getElementById('estacionLongitud').focus();
            return;
        }

        // 2️⃣ VALIDAR ARCHIVOS
        const fotografia = document.getElementById('estacionFotografia').files[0];
        const mapa = document.getElementById('estacionMapa').files[0];

        if (!fotografia) {
            mostrarNotificacion('❌ ERROR: Debe cargar la "Fotografía de la Estación"', 'error');
            document.getElementById('estacionFotografia').focus();
            return;
        }

        if (!mapa) {
            mostrarNotificacion('❌ ERROR: Debe cargar el "Mapa de Ubicación"', 'error');
            document.getElementById('estacionMapa').focus();
            return;
        }

        // 3️⃣ VALIDAR SENSORES
        const sensores = document.querySelectorAll('#contenedorSensores .detailed-card');
        if (sensores.length === 0) {
            mostrarNotificacion('❌ ERROR: Debe agregar al menos 1 SENSOR a la estación', 'error');
            document.querySelector('#contenedorSensores').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }


        // Validar que cada sensor tenga tipo seleccionado
        const sensorTipos = document.querySelectorAll('#contenedorSensores select[name="sensor_tipo[]"]');
        let sensorSinTipo = false;
        let numeroSensor = 0;

        for (let i = 0; i < sensorTipos.length; i++) {
            const selectElement = sensorTipos[i];
            const valorSeleccionado = selectElement.value;

            // ✅ DEBUG: Mostrar qué tiene cada select
            console.log(`Sensor #${i + 1}:`, {
                disabled: selectElement.disabled,
                value: valorSeleccionado,
                innerHTML: selectElement.innerHTML.substring(0, 100)
            });

            // Verificar si está deshabilitado (aún cargando)
            if (selectElement.disabled) {
                mostrarNotificacion(`❌ ERROR: El SENSOR #${i + 1} aún está cargando. Por favor espere unos segundos.`, 'error');
                return;
            }

            // Verificar si tiene valor seleccionado
            if (!valorSeleccionado || valorSeleccionado === '' || valorSeleccionado === 'null') {
                sensorSinTipo = true;
                numeroSensor = i + 1;
                mostrarNotificacion(`❌ ERROR: El SENSOR #${numeroSensor} no tiene tipo seleccionado. Por favor seleccione un tipo de sensor.`, 'error');
                selectElement.focus();
                selectElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        }

        console.log('✅ Sensores validados:', sensorTipos.length);

        // 4️⃣ VALIDAR COMPONENTES
        // 4️⃣ VALIDAR COMPONENTES
        const componentes = document.querySelectorAll('#contenedorComponentes .detailed-card');
        if (componentes.length < 3) {
            mostrarNotificacion(`❌ ERROR: Debe agregar al menos 3 COMPONENTES (actualmente: ${componentes.length})`, 'error');
            document.querySelector('#contenedorComponentes').scrollIntoView({ behavior: 'smooth', block: 'center' });
            return;
        }

        // Validar que cada componente tenga tipo seleccionado
        const componenteTipos = document.querySelectorAll('#contenedorComponentes select[name="componente_tipo[]"]');
        let componenteSinTipo = false;
        let numeroComponente = 0;

        for (let i = 0; i < componenteTipos.length; i++) {
            const selectElement = componenteTipos[i];
            const valorSeleccionado = selectElement.value;

            // ✅ DEBUG: Mostrar qué tiene cada select
            console.log(`Componente #${i + 1}:`, {
                disabled: selectElement.disabled,
                value: valorSeleccionado,
                innerHTML: selectElement.innerHTML.substring(0, 100)
            });

            // Verificar si está deshabilitado (aún cargando)
            if (selectElement.disabled) {
                mostrarNotificacion(`❌ ERROR: El COMPONENTE #${i + 1} aún está cargando. Por favor espere unos segundos.`, 'error');
                return;
            }

            // Verificar si tiene valor seleccionado
            if (!valorSeleccionado || valorSeleccionado === '' || valorSeleccionado === 'null') {
                componenteSinTipo = true;
                numeroComponente = i + 1;
                mostrarNotificacion(`❌ ERROR: El COMPONENTE #${numeroComponente} no tiene tipo seleccionado. Por favor seleccione un tipo de componente.`, 'error');
                selectElement.focus();
                selectElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        }

        console.log('✅ Componentes validados:', componenteTipos.length);

        // 5️⃣ ENVIAR FORMULARIO
        try {
            mostrarNotificacion('⏳ Creando estación, por favor espere...', 'success');

            const formData = new FormData(this);

            // Log para debug
            console.log('📤 Enviando FormData con:');
            for (let pair of formData.entries()) {
                console.log(`  - ${pair[0]}:`, pair[1]);
            }

            const response = await fetch('../controller/CInfoSensor.php?action=add_station', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            console.log('📥 Respuesta del servidor:', result);

            if (result.success) {
                cerrarModal('modalAgregarEstacion');
                loadStationsFromDatabase();
            } else {
                mostrarNotificacion('❌ ERROR DEL SERVIDOR: ' + (result.error || 'Error desconocido'), 'error');
            }
        } catch (error) {
            console.error('💥 Error fatal:', error);
            mostrarNotificacion('❌ ERROR DE CONEXIÓN: No se pudo conectar con el servidor. Verifique su conexión.', 'error');
        }
    });

    // ===== INICIALIZACIÓN =====
    document.addEventListener('DOMContentLoaded', function () {
        console.log('🚀 Iniciando reportes de estaciones admin...');

        // Cargar estaciones desde la base de datos
        loadStationsFromDatabase();

        console.log('✅ Reportes Admin listo - Modo dinámico activado');
    });
    async function abrirModalGestionSensores() {
        abrirModal('modalGestionSensores');
        await cargarCatalogoSensores();
    }

    async function abrirModalGestionComponentes() {
        abrirModal('modalGestionComponentes');
        await cargarCatalogoComponentes();
    }

    async function cargarCatalogoSensores() {
        const container = document.getElementById('listaSensores');

        try {
            container.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p>Cargando tipos de sensores...</p>
            </div>
        `;

            const response = await fetch('../controller/CInfoSensor.php?action=get_catalogo_sensores');
            const data = await response.json();

            if (data.success) {
                if (data.data.length === 0) {
                    container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No hay tipos de sensores registrados</p>
                    </div>
                `;
                    return;
                }

                container.innerHTML = data.data.map(tipo => `
                <div class="tipo-card">
                    <div class="tipo-card-header">
                        <h4 class="tipo-card-title">${tipo.nombre}</h4>
                        <div class="tipo-card-actions">
                            <button class="btn-icon" onclick="editarTipoSensor(${tipo.id_catalogo_sensor}, '${tipo.nombre.replace(/'/g, "\\'")}', '${(tipo.descripcion || '').replace(/'/g, "\\'")}')">
                                <i class="fas fa-edit"></i>
                                Editar
                            </button>
                            <button class="btn-icon delete" onclick="eliminarTipoSensor(${tipo.id_catalogo_sensor}, '${tipo.nombre.replace(/'/g, "\\'")}')">
                                <i class="fas fa-trash"></i>
                                Eliminar
                            </button>
                        </div>
                    </div>
                    ${tipo.descripcion ? `<p class="tipo-card-desc">${tipo.descripcion}</p>` : ''}
                </div>
            `).join('');
            } else {
                throw new Error(data.error || 'Error al cargar tipos de sensores');
            }
        } catch (error) {
            console.error('Error:', error);
            container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle" style="color: var(--accent-red);"></i>
                <p style="color: var(--accent-red);">Error al cargar tipos de sensores</p>
            </div>
        `;
        }
    }

    async function cargarCatalogoComponentes() {
        const container = document.getElementById('listaComponentes');

        try {
            container.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p>Cargando tipos de componentes...</p>
            </div>
        `;

            const response = await fetch('../controller/CInfoSensor.php?action=get_catalogo_componentes');
            const data = await response.json();

            if (data.success) {
                if (data.data.length === 0) {
                    container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No hay tipos de componentes registrados</p>
                    </div>
                `;
                    return;
                }

                container.innerHTML = data.data.map(tipo => `
                <div class="tipo-card">
                    <div class="tipo-card-header">
                        <h4 class="tipo-card-title">${tipo.nombre}</h4>
                        <div class="tipo-card-actions">
                            <button class="btn-icon" onclick="editarTipoComponente(${tipo.id_catalogo_componente}, '${tipo.nombre.replace(/'/g, "\\'")}', '${(tipo.descripcion || '').replace(/'/g, "\\'")}')">
                                <i class="fas fa-edit"></i>
                                Editar
                            </button>
                            <button class="btn-icon delete" onclick="eliminarTipoComponente(${tipo.id_catalogo_componente}, '${tipo.nombre.replace(/'/g, "\\'")}')">
                                <i class="fas fa-trash"></i>
                                Eliminar
                            </button>
                        </div>
                    </div>
                    ${tipo.descripcion ? `<p class="tipo-card-desc">${tipo.descripcion}</p>` : ''}
                </div>
            `).join('');
            } else {
                throw new Error(data.error || 'Error al cargar tipos de componentes');
            }
        } catch (error) {
            console.error('Error:', error);
            container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle" style="color: var(--accent-red);"></i>
                <p style="color: var(--accent-red);">Error al cargar tipos de componentes</p>
            </div>
        `;
        }
    }

    function abrirModalAgregarTipoSensor() {
        document.getElementById('tituloFormTipo').innerHTML = '<i class="fas fa-plus"></i> Agregar Tipo de Sensor';
        document.getElementById('tipoAccion').value = 'add';
        document.getElementById('tipoCategoria').value = 'sensor';
        document.getElementById('tipoId').value = '';
        document.getElementById('tipoNombre').value = '';
        document.getElementById('tipoDescripcion').value = '';
        abrirModal('modalFormTipo');
    }

    function abrirModalAgregarTipoComponente() {
        document.getElementById('tituloFormTipo').innerHTML = '<i class="fas fa-plus"></i> Agregar Tipo de Componente';
        document.getElementById('tipoAccion').value = 'add';
        document.getElementById('tipoCategoria').value = 'componente';
        document.getElementById('tipoId').value = '';
        document.getElementById('tipoNombre').value = '';
        document.getElementById('tipoDescripcion').value = '';
        abrirModal('modalFormTipo');
    }

    function editarTipoSensor(id, nombre, descripcion) {
        document.getElementById('tituloFormTipo').innerHTML = '<i class="fas fa-edit"></i> Editar Tipo de Sensor';
        document.getElementById('tipoAccion').value = 'update';
        document.getElementById('tipoCategoria').value = 'sensor';
        document.getElementById('tipoId').value = id;
        document.getElementById('tipoNombre').value = nombre;
        document.getElementById('tipoDescripcion').value = descripcion;
        abrirModal('modalFormTipo');
    }

    function editarTipoComponente(id, nombre, descripcion) {
        document.getElementById('tituloFormTipo').innerHTML = '<i class="fas fa-edit"></i> Editar Tipo de Componente';
        document.getElementById('tipoAccion').value = 'update';
        document.getElementById('tipoCategoria').value = 'componente';
        document.getElementById('tipoId').value = id;
        document.getElementById('tipoNombre').value = nombre;
        document.getElementById('tipoDescripcion').value = descripcion;
        abrirModal('modalFormTipo');
    }

    async function eliminarTipoSensor(id, nombre) {
        // ❌ ELIMINAR ESTO:
        // if (!confirm(`¿Está seguro de eliminar el tipo de sensor "${nombre}"?\n\nNOTA: Solo se puede eliminar si no está siendo usado por ningún sensor.`)) {
        //     return;
        // }

        // ✅ AGREGAR ESTO:
        deleteType = 'tipo_sensor';
        deleteId = id;
        document.getElementById('confirmDeleteMessage').innerHTML = `
        ¿Está seguro de eliminar el tipo de sensor <strong>"${nombre}"</strong>?
        <br><br>
        <span style="color: var(--accent-orange); font-size: 0.85rem;">
            ⚠️ NOTA: Solo se puede eliminar si no está siendo usado por ningún sensor.
        </span>
    `;
        abrirModal('modalConfirmDelete');
    }

    async function eliminarTipoComponente(id, nombre) {
        // ❌ ELIMINAR ESTO:
        // if (!confirm(`¿Está seguro de eliminar el tipo de componente "${nombre}"?\n\nNOTA: Solo se puede eliminar si no está siendo usado por ningún componente.`)) {
        //     return;
        // }

        // ✅ AGREGAR ESTO:
        deleteType = 'tipo_componente';
        deleteId = id;
        document.getElementById('confirmDeleteMessage').innerHTML = `
        ¿Está seguro de eliminar el tipo de componente <strong>"${nombre}"</strong>?
        <br><br>
        <span style="color: var(--accent-orange); font-size: 0.85rem;">
            ⚠️ NOTA: Solo se puede eliminar si no está siendo usado por ningún componente.
        </span>
    `;
        abrirModal('modalConfirmDelete');
    }

    document.getElementById('formTipo').addEventListener('submit', async function (e) {
        e.preventDefault();

        const accion = document.getElementById('tipoAccion').value;
        const categoria = document.getElementById('tipoCategoria').value;
        const id = document.getElementById('tipoId').value;
        const nombre = document.getElementById('tipoNombre').value.trim();
        const descripcion = document.getElementById('tipoDescripcion').value.trim();

        if (!nombre) {
            mostrarNotificacion('❌ El nombre es requerido', 'error');
            return;
        }

        try {
            let action, method;

            if (accion === 'add') {
                action = categoria === 'sensor' ? 'add_tipo_sensor' : 'add_tipo_componente';
                method = 'POST';
            } else {
                action = categoria === 'sensor' ? 'update_tipo_sensor' : 'update_tipo_componente';
                method = 'PUT';
            }

            const body = { action, nombre, descripcion };
            if (accion === 'update') body.id = parseInt(id);

            const response = await fetch('../controller/CInfoSensor.php', {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });

            const result = await response.json();

            if (result.success) {
                const mensaje = accion === 'add' ? 'agregado' : 'actualizado';
                mostrarNotificacion(`✅ Tipo ${mensaje} correctamente`, 'success');
                cerrarModal('modalFormTipo');

                // ✅ RECARGAR PÁGINA DESPUÉS DE 1 SEGUNDO
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                mostrarNotificacion('❌ ' + (result.message || 'Error al guardar'), 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('❌ Error al procesar la solicitud', 'error');
        }
    });
    async function abrirModalGestionVariables() {
        abrirModal('modalGestionVariables');
        await cargarCatalogoVariables();
    }

    async function cargarCatalogoVariables() {
        const container = document.getElementById('listaVariables');

        try {
            container.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p>Cargando variables...</p>
            </div>
        `;

            const response = await fetch('../controller/CInfoSensor.php?action=get_catalogo_variables');
            const data = await response.json();

            if (data.success) {
                if (data.data.length === 0) {
                    container.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No hay variables registradas</p>
                    </div>
                `;
                    return;
                }

                container.innerHTML = data.data.map(variable => `
                <div class="tipo-card">
                    <div class="tipo-card-header">
                        <h4 class="tipo-card-title">
                            ${variable.codigo_columna} 
                            <span style="color: var(--accent-green); font-size: 0.85rem; margin-left: 0.5rem;">(${variable.nombre_corto})</span>
                        </h4>
                        <div class="tipo-card-actions">
                            <button class="btn-icon" onclick='editarVariable(${variable.id_metadato}, "${variable.codigo_columna}", "${variable.nombre_corto}", "${(variable.descripcion || '').replace(/'/g, "\\'")}}", "${variable.unidad}")'>
                                <i class="fas fa-edit"></i>
                                Editar
                            </button>
                            <button class="btn-icon delete" onclick='eliminarVariable(${variable.id_metadato}, "${variable.codigo_columna}")'>
                                <i class="fas fa-trash"></i>
                                Eliminar
                            </button>
                        </div>
                    </div>
                    <p class="tipo-card-desc">
                        <strong>Descripción:</strong> ${variable.descripcion || 'Sin descripción'}<br>
                        <strong>Unidad:</strong> <span style="color: var(--primary-cyan);">${variable.unidad || 'N/A'}</span>
                    </p>
                </div>
            `).join('');
            } else {
                throw new Error(data.error || 'Error al cargar variables');
            }
        } catch (error) {
            console.error('Error:', error);
            container.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-triangle" style="color: var(--accent-red);"></i>
                <p style="color: var(--accent-red);">Error al cargar variables</p>
            </div>
        `;
        }
    }

    function abrirModalAgregarVariable() {
        document.getElementById('tituloFormVariable').innerHTML = '<i class="fas fa-plus"></i> Agregar Variable';
        document.getElementById('varAccion').value = 'add';
        document.getElementById('varId').value = '';
        document.getElementById('varCodigo').value = '';
        document.getElementById('varNombre').value = '';
        document.getElementById('varDescripcion').value = '';
        document.getElementById('varUnidad').value = '';
        abrirModal('modalFormVariable');
    }

    function editarVariable(id, codigo, nombre, descripcion, unidad) {
        document.getElementById('tituloFormVariable').innerHTML = '<i class="fas fa-edit"></i> Editar Variable';
        document.getElementById('varAccion').value = 'update';
        document.getElementById('varId').value = id;
        document.getElementById('varCodigo').value = codigo;
        document.getElementById('varNombre').value = nombre;
        document.getElementById('varDescripcion').value = descripcion;
        document.getElementById('varUnidad').value = unidad;
        abrirModal('modalFormVariable');
    }

    function eliminarVariable(id, codigo) {
        deleteType = 'variable';
        deleteId = id;
        document.getElementById('confirmDeleteMessage').innerHTML = `
        ¿Está seguro de eliminar la variable <strong>"${codigo}"</strong>?
        <br><br>
        <span style="color: var(--accent-orange); font-size: 0.85rem;">
            ⚠️ NOTA: Solo se puede eliminar si no está siendo usada en datos L2.
        </span>
    `;
        abrirModal('modalConfirmDelete');
    }

    document.getElementById('formVariable').addEventListener('submit', async function (e) {
        e.preventDefault();

        const accion = document.getElementById('varAccion').value;
        const id = document.getElementById('varId').value;
        const codigo = document.getElementById('varCodigo').value.trim();
        const nombre = document.getElementById('varNombre').value.trim();
        const descripcion = document.getElementById('varDescripcion').value.trim();
        const unidad = document.getElementById('varUnidad').value;

        if (!codigo || !nombre || !descripcion || !unidad) {
            mostrarNotificacion('❌ Todos los campos son obligatorios', 'error');
            return;
        }

        try {
            const action = accion === 'add' ? 'add_variable' : 'update_variable';
            const method = accion === 'add' ? 'POST' : 'PUT';

            const body = { action, codigo_columna: codigo, nombre_corto: nombre, descripcion, unidad };
            if (accion === 'update') body.id = parseInt(id);

            const response = await fetch('../controller/CInfoSensor.php', {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(body)
            });

            const result = await response.json();

            if (result.success) {
                const mensaje = accion === 'add' ? 'agregada' : 'actualizada';
                mostrarNotificacion(`✅ Variable ${mensaje} correctamente`, 'success');
                cerrarModal('modalFormVariable');

                // ✅ RECARGAR PÁGINA DESPUÉS DE 1 SEGUNDO
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                mostrarNotificacion('❌ ' + (result.message || 'Error al guardar'), 'error');
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarNotificacion('❌ Error al procesar la solicitud', 'error');
        }
    });
</script>

<?php include('../views/Administrador/footerAdmin.php'); ?>