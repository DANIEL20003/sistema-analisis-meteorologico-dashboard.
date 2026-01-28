<?php
$titulo_pagina = 'Dashboard - Sistema de Análisis Meteorológico - ESPOCH';
$pagina_activa = 'dashboard';

include('../views/Administrador/headerAdmin.php');
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdn.plot.ly/plotly-2.27.0.min.js" charset="utf-8"></script>
<!-- Módulo exclusivo para gráficas L2 -->
<!-- Módulo exclusivo para gráficas L2 (ELIMINADO POR CONFLICTO) -->
<style>
    /* ===== MODAL DE DESCARGA ===== */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(8px);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.3s ease;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-card {
        background: linear-gradient(135deg, rgba(10, 14, 26, 0.98), rgba(18, 24, 43, 0.95));
        border: 2px solid var(--primary-cyan);
        border-radius: 20px;
        padding: 1.5rem;
        /* ✅ REDUCIDO: de 2.5rem a 1.5rem */
        max-width: 650px;
        /* ✅ REDUCIDO: de 750px a 650px */
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 255, 255, 0.3);
        position: relative;
        animation: slideUp 0.4s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ===== HEADER DEL MODAL ===== */
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.8rem;
        /* ✅ REDUCIDO: de 2rem a 0.8rem */
        padding-bottom: 0.8rem;
        /* ✅ REDUCIDO: de 1.5rem a 0.8rem */
        border-bottom: 2px solid var(--border-primary);
    }

    .modal-title-section {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex: 1;
    }

    .modal-logo {
        width: 160px;
        /* ✅ REDUCIDO: de 180px a 160px */
        height: 70px;
        /* ✅ REDUCIDO: de 80px a 70px */
        background: #ffffff;
        border-radius: 8px;
        padding: 0.3rem;
        /* ✅ REDUCIDO: de 0.5rem a 0.3rem */
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .modal-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .modal-title-text h3 {
        font-size: 0.75rem;
        /* ✅ REDUCIDO: de 0.85rem a 0.75rem */
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0 0 0.15rem 0;
        /* ✅ REDUCIDO: de 0.3rem a 0.15rem */
        text-transform: uppercase;
        letter-spacing: 0.3px;
        line-height: 1.1;
    }

    .modal-title-text p {
        font-size: 0.65rem;
        /* ✅ REDUCIDO: de 0.75rem a 0.65rem */
        color: var(--text-secondary);
        margin: 0;
        line-height: 1.2;
    }

    .modal-close {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 50%;
        width: 35px;
        /* ✅ REDUCIDO: de 40px a 35px */
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        color: var(--text-primary);
        font-size: 1.1rem;
        /* ✅ REDUCIDO: de 1.2rem a 1.1rem */
        flex-shrink: 0;
    }

    .modal-close:hover {
        background: rgba(255, 7, 58, 0.8);
        border-color: #ff073a;
        transform: rotate(90deg);
    }

    /* ===== BODY DEL MODAL ===== */
    .modal-body {
        padding: 0.8rem 1.5rem;
        /* ✅ REDUCIDO: de 2rem a 0.8rem/1.5rem */
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.8rem;
        /* ✅ REDUCIDO: de 1.5rem a 0.8rem */
        margin-bottom: 0.8rem;
        /* ✅ REDUCIDO: de 2rem a 0.8rem */
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 0.3rem;
        /* ✅ REDUCIDO: de 0.5rem a 0.3rem */
    }

    .form-label {
        font-size: 0.68rem;
        /* ✅ REDUCIDO: de 0.85rem a 0.68rem */
        font-weight: 600;
        color: var(--text-primary);
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .required {
        color: var(--accent-red);
        margin-left: 0.15rem;
        /* ✅ REDUCIDO: de 0.2rem a 0.15rem */
    }


    .form-input {
        width: 100%;
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        padding: 0.55rem;
        /* ✅ REDUCIDO: de 0.8rem a 0.55rem */
        color: var(--text-primary);
        font-size: 0.8rem;
        /* ✅ REDUCIDO: de 0.9rem a 0.8rem */
        transition: all 0.3s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.05);
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
    }

    .form-input.error {
        border-color: var(--accent-red);
        background: rgba(255, 7, 58, 0.05);
    }

    .form-error {
        font-size: 0.65rem;
        /* ✅ REDUCIDO: de 0.75rem a 0.65rem */
        color: var(--accent-red);
        display: none;
        margin-top: -0.2rem;
        /* ✅ REDUCIDO */
    }

    .form-input.error~.form-error {
        display: block;
    }

    /* ===== SECCIÓN DE TÉRMINOS ===== */
    .terms-section {
        background: rgba(0, 255, 255, 0.05);
        border: 1px solid var(--primary-cyan);
        border-radius: 12px;
        padding: 0.8rem;
        /* ✅ REDUCIDO: de 1.5rem a 0.8rem */
        margin-top: 0.6rem;
        /* ✅ REDUCIDO: de 1rem a 0.6rem */
    }

    .terms-title {
        display: flex;
        align-items: center;
        gap: 0.3rem;
        /* ✅ REDUCIDO: de 0.5rem a 0.3rem */
        font-size: 0.75rem;
        /* ✅ REDUCIDO: de 1rem a 0.75rem */
        font-weight: 700;
        color: var(--primary-cyan);
        margin-bottom: 0.6rem;
        /* ✅ REDUCIDO: de 1rem a 0.6rem */
    }

    .terms-title i {
        font-size: 0.9rem;
        /* ✅ REDUCIDO: de 1.2rem a 0.9rem */
    }

    .terms-content {
        font-size: 0.68rem;
        /* ✅ REDUCIDO: de 0.85rem a 0.68rem */
        color: var(--text-secondary);
        line-height: 1.4;
        /* ✅ REDUCIDO: de 1.6 a 1.4 */
        margin-bottom: 0.6rem;
        /* ✅ REDUCIDO: de 1rem a 0.6rem */
    }

    .terms-content ul {
        margin: 0.3rem 0 0 1rem;
        /* ✅ REDUCIDO márgenes */
        padding: 0;
    }

    .terms-content ul li {
        margin-bottom: 0.3rem;
        /* ✅ REDUCIDO: de 0.5rem a 0.3rem */
    }

    .terms-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        /* ✅ REDUCIDO: de 0.8rem a 0.5rem */
        margin-top: 0.6rem;
        /* ✅ REDUCIDO: de 1rem a 0.6rem */
    }

    .terms-checkbox input[type="checkbox"] {
        width: 16px;
        /* ✅ REDUCIDO: de 20px a 16px */
        height: 16px;
        margin-top: 0.1rem;
        /* ✅ REDUCIDO: de 0.2rem a 0.1rem */
        cursor: pointer;
        accent-color: var(--primary-cyan);
        flex-shrink: 0;
    }

    .terms-checkbox label {
        font-size: 0.68rem;
        /* ✅ REDUCIDO: de 0.85rem a 0.68rem */
        color: var(--text-primary);
        cursor: pointer;
        line-height: 1.3;
    }

    /* ===== FOOTER DEL MODAL ===== */
    .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.8rem;
        /* ✅ REDUCIDO: de 1rem a 0.8rem */
        padding: 0.8rem 1.5rem;
        /* ✅ REDUCIDO: de 1.5rem a 0.8rem */
        border-top: 1px solid var(--border-primary);
    }

    .btn-modal {
        padding: 0.55rem 1rem;
        /* ✅ REDUCIDO: de 0.8rem/1.5rem a 0.55rem/1rem */
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.75rem;
        /* ✅ REDUCIDO: de 0.9rem a 0.75rem */
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        /* ✅ REDUCIDO: de 0.5rem a 0.4rem */
        text-transform: uppercase;
        letter-spacing: 0.3px;
        /* ✅ REDUCIDO: de 0.5px a 0.3px */
    }

    .btn-modal-cancel {
        background: var(--bg-card);
        color: var(--text-secondary);
        border: 1px solid var(--border-secondary);
    }

    .btn-modal-cancel:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--primary-cyan);
        transform: translateY(-1px);
    }

    .btn-modal-submit {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        color: #0a0e1a;
        box-shadow: 0 4px 15px rgba(0, 255, 255, 0.3);
    }

    .btn-modal-submit:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 255, 255, 0.4);
    }

    .btn-modal-submit:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
            gap: 0.6rem;
        }

        .modal-card {
            width: 95%;
            max-height: 95vh;
            padding: 1rem;
        }

        .modal-header {
            padding: 0.6rem 1rem;
        }

        .modal-logo {
            width: 120px;
            height: 60px;
        }

        .modal-title-text h3 {
            font-size: 0.7rem;
        }

        .modal-title-text p {
            font-size: 0.6rem;
        }

        .modal-body {
            padding: 0.8rem 1rem;
        }

        .modal-footer {
            flex-direction: column;
            padding: 0.6rem 1rem;
        }

        .btn-modal {
            width: 100%;
            justify-content: center;
            font-size: 0.7rem;
        }
    }

    /* ===== SCROLLBAR DEL MODAL ===== */
    .modal-card::-webkit-scrollbar {
        width: 8px;
    }

    .modal-card::-webkit-scrollbar-track {
        background: var(--bg-secondary);
    }

    .modal-card::-webkit-scrollbar-thumb {
        background: var(--primary-cyan);
        border-radius: 4px;
    }

    .modal-card::-webkit-scrollbar-thumb:hover {
        background: var(--primary-blue);
    }

    /* ===== NOTIFICACIÓN PERSONALIZADA DE DESCARGA ===== */
    .custom-download-notification {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: linear-gradient(135deg, rgba(10, 14, 26, 0.98), rgba(18, 24, 43, 0.98));
        border: 2px solid var(--primary-cyan);
        border-radius: 15px;
        padding: 2rem 3rem;
        box-shadow: 0 20px 60px rgba(0, 255, 255, 0.3);
        z-index: 10000;
        animation: downloadPulse 0.5s ease-out;
        backdrop-filter: blur(10px);
    }

    .custom-download-notification-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
    }

    .custom-download-notification-icon {
        font-size: 3rem;
        color: var(--primary-cyan);
        animation: downloadSpin 1s ease-in-out;
    }

    .custom-download-notification-text {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--text-primary);
        text-align: center;
    }

    .custom-download-notification-subtext {
        font-size: 0.85rem;
        color: var(--text-secondary);
        text-align: center;
    }

    .custom-download-notification-success {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(0, 255, 136, 0.1);
        border: 1px solid var(--accent-green);
        border-radius: 8px;
    }

    .custom-download-notification-success i {
        color: var(--accent-green);
        font-size: 1.2rem;
    }

    .custom-download-notification-success span {
        color: var(--accent-green);
        font-weight: 600;
        font-size: 0.9rem;
    }

    @keyframes downloadPulse {
        0% {
            transform: translate(-50%, -50%) scale(0.8);
            opacity: 0;
        }

        50% {
            transform: translate(-50%, -50%) scale(1.05);
        }

        100% {
            transform: translate(-50%, -50%) scale(1);
            opacity: 1;
        }
    }

    @keyframes downloadSpin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    /* Ocultar mensaje original de Plotly */
    .plotly .notifier {
        display: none !important;
    }

    body>svg[class*="snapshot"] {
        display: none !important;
    }

    /* ===== OCULTAR MENSAJE DE DESCARGA DE PLOTLY ===== */
    .modebar-btn[data-title*="Download"]~.modebar-btn[data-title*="snapshot"],
    .js-plotly-plot .plotly .modebar-container .modebar-btn[data-attr="toImage"] {
        position: relative;
    }

    /* Ocultar notificación de Plotly */
    .js-plotly-plot .plotly .gtitle,
    .js-plotly-plot .plotly .annotation-text {
        display: none !important;
    }

    /* Ocultar específicamente el mensaje de snapshot */
    body>svg[class*="snapshot"] {
        display: none !important;
    }

    .plotly .notifier {
        display: none !important;
    }

    /* ===== VARIABLES DE COLORES PROFESIONALES ===== */
    :root {
        /* Colores dominantes del header */
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
    .dashboard-page-wrapper {
        position: relative;
        min-height: 100vh;
    }

    .dashboard-fixed-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('../public/img/chimborazo 1.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        z-index: -1;
    }

    .dashboard-fixed-background::before {
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
    .dashboard-container {
        position: relative;
        z-index: 3;
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 1.5rem;
        padding: 1rem;
        min-height: 100vh;
        margin: 0 auto;
        align-items: start;
    }

    /* ===== PANEL LATERAL DE FILTROS ===== */
    .sidebar-filters {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 15px;
        padding: 0.8rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-glow);
        height: fit-content;
        position: sticky;
        top: 1.2rem;
        max-height: calc(100vh - 2.4rem);
        overflow-y: auto;
        font-size: 0.68rem;
    }

    .sidebar-header {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .sidebar-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--primary-cyan);
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .sidebar-icon {
        color: var(--primary-cyan);
        font-size: 1.2rem;
    }

    /* ===== SELECTOR DE NIVEL ===== */
    .level-selector {
        margin-bottom: 1.5rem;
    }

    .level-selector-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.8rem;
        display: block;
    }

    .level-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.5rem;
    }

    .level-btn {
        padding: 0.6rem 0.4rem;
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 10px;
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
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
        color: white;
    }

    .chart-canvas-wrapper {
        position: relative;
        height: 308px;
        margin-bottom: 0.8rem;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        padding: 0.5rem;
        overflow: visible;
    }

    .chart-canvas-wrapper>div {
        width: 100% !important;
        height: 100% !important;
    }

    /* Plotly modebar personalizado */
    .modebar {
        top: 10px !important;
        right: 10px !important;
    }

    .modebar-btn {
        background: rgba(0, 255, 255, 0.1) !important;
        border: 1px solid rgba(0, 255, 255, 0.3) !important;
        border-radius: 6px !important;
        margin: 0 3px !important;
    }

    .modebar-btn:hover {
        background: rgba(0, 255, 255, 0.25) !important;
    }

    .modebar-btn svg {
        fill: rgba(255, 255, 255, 0.8) !important;
    }

    .modebar-btn.active svg {
        fill: rgb(0, 255, 255) !important;
    }

    .hoverlayer .hovertext {
        border-radius: 8px !important;
    }

    /* ===== FILTROS ===== */
    .filter-section {
        margin-bottom: 1.5rem;
    }

    .filter-section-title {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .filter-section-title i {
        color: white;
        font-size: 1rem;
    }

    .filter-group {
        margin-bottom: 1rem;
    }

    .filter-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.4rem;
        display: block;
    }

    .filter-input {
        width: 100%;
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        padding: 0.7rem;
        color: var(--text-primary);
        font-size: 0.75rem;
        transition: all 0.3s ease;
    }

    .filter-input:focus {
        outline: none;
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.1);
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
    }

    .filter-input option {
        background: var(--bg-secondary);
        color: var(--text-primary);
        padding: 0.5rem;
    }

    /* ===== RANGO TEMPORAL (AÑO Y MES) ===== */
    .date-range-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.8rem;
    }

    .date-range-col {
        display: flex;
        flex-direction: column;
    }

    /* ===== BOTONES DE ACCIÓN ===== */
    .filter-actions {
        display: grid;
        gap: 0.8rem;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-secondary);
    }

    .btn-filter {
        padding: 0.8rem 1rem;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-apply {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        color: #0a0e1a;
        box-shadow: 0 4px 15px rgba(0, 255, 255, 0.3);
    }

    .btn-apply:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 255, 255, 0.4);
    }

    .btn-reset {
        background: var(--bg-card);
        color: var(--text-secondary);
        border: 1px solid var(--border-secondary);
    }

    .btn-reset:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--primary-cyan);
        transform: translateY(-1px);
    }

    /* ===== BOTONES DE DESCARGA ===== */
    .download-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.8rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-secondary);
    }

    .btn-download {
        padding: 0.8rem 1rem;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-download-pdf {
        background: linear-gradient(135deg, #ff073a, #ff9500);
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(255, 7, 58, 0.3);
    }

    .btn-download-pdf:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 7, 58, 0.4);
    }

    .btn-download-csv {
        background: linear-gradient(135deg, #00ff88, #00ffff);
        color: #0a0e1a;
        box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
    }

    .btn-download-csv:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 255, 136, 0.4);
    }

    /* ===== CONTENIDO PRINCIPAL ===== */
    .main-content {
        display: flex;
        flex-direction: column;
        gap: 1.2rem;
    }

    /* ===== HEADER DASHBOARD ===== */
    .dashboard-header {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 12px;
        padding: 1.5rem 2rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-glow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
        gap: 2rem;
        flex-wrap: wrap;
    }

    .header-info h1 {
        font-size: 0.90rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.3rem;
    }

    .header-info p {
        font-size: 0.65rem;
        color: var(--text-secondary);
        margin: 0;
        max-width: 400px;
    }

    .header-stats {
        display: flex;
        gap: 2rem;
        flex-wrap: wrap;
        justify-content: center;
    }

    .stat-item {
        text-align: center;
        min-width: 80px;
        max-width: 120px;
    }

    .stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--primary-cyan);
        margin-bottom: 0.3rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 120px;
    }

    .stat-label {
        font-size: 0.50rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ===== SEPARADOR DE SECCIONES ===== */
    .section-divider {
        width: 100%;
        height: 2px;
        background: linear-gradient(90deg,
                transparent 0%,
                var(--primary-cyan) 20%,
                var(--primary-blue) 50%,
                var(--primary-cyan) 80%,
                transparent 100%);
        margin: 1rem 0;
        position: relative;
    }

    .section-divider::before {
        content: '';
        position: absolute;
        top: -4px;
        left: 50%;
        transform: translateX(-50%);
        width: 12px;
        height: 12px;
        background: var(--primary-cyan);
        border-radius: 50%;
        box-shadow: 0 0 20px rgba(0, 255, 255, 0.6);
    }

    /* ===== CONTENEDOR DE MAPA E INFORMACIÓN ===== */
    .map-info-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .map-section {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 12px;
        padding: 1.2rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-card);
        display: flex;
        flex-direction: column;
        height: 350px;
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        margin-bottom: 1rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .section-title {
        font-size: 0.95rem;
        font-weight: 700;
        color: var(--primary-cyan);
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ===== CONTENEDOR DE MAPA (SIN IMAGEN) ===== */
    .map-image-container {
        position: relative;
        width: 100%;
        height: 240px;
        background: var(--bg-secondary);
        border-radius: 10px;
        overflow: hidden;
        border: 1px solid var(--border-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .map-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
    }

    .carousel-container {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .carousel-slide {
        position: absolute;
        width: 100%;
        height: 100%;
        opacity: 0;
        transition: opacity 0.8s ease-in-out;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .carousel-slide.active {
        opacity: 1;
    }

    .carousel-image {
        width: 100%;
        height: 100%;
        object-fit: contain;
        object-position: center;
    }

    .waiting-data-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: var(--text-muted);
        text-align: center;
        padding: 2rem;
    }

    .waiting-data-message i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
        color: var(--primary-cyan);
    }

    .waiting-data-message p {
        font-size: 0.9rem;
        margin: 0;
    }

    .waiting-data-message .subtitle {
        font-size: 0.75rem;
        margin-top: 0.5rem;
        opacity: 0.7;
    }

    .carousel-indicator.active {
        background: var(--primary-cyan);
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.6);
        width: 10px;
        height: 10px;
    }

    .map-placeholder {
        text-align: center;
        color: var(--text-muted);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    .map-placeholder i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
    }

    .map-placeholder p {
        margin: 0;
        font-size: 0.85rem;
    }

    .info-card {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 12px;
        padding: 1.2rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-card);
        height: 350px;
        display: flex;
        flex-direction: column;
    }

    .info-header {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        margin-bottom: 1rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .info-header i {
        font-size: 0.9rem;
        color: var(--primary-cyan);
    }

    .info-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--primary-cyan);
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.8rem;
    }

    .info-item {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.6rem;
        align-items: center;
    }

    .detail-label {
        font-size: 0.70rem;
        color: var(--text-muted);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .detail-value {
        font-size: 0.70rem;
        color: var(--text-primary);
        font-weight: 700;
        background: var(--bg-card);
        padding: 0.3rem 0.5rem;
        border-radius: 6px;
        border: 1px solid var(--border-secondary);
    }

    /* ===== CONTENEDOR DE GRÁFICAS ===== */
    .charts-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .charts-section {
        display: grid;
        gap: 1.5rem;
    }

    .charts-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.8rem;
    }

    .charts-row-two-cols {
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .chart-card {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 12px;
        padding: 1rem;
        backdrop-filter: blur(20px);
        transition: all 0.3s ease;
        box-shadow: var(--shadow-card);
        min-width: 450px;
        overflow: visible;
    }

    .chart-card:hover {
        border-color: rgba(0, 255, 255, 0.5);
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 255, 255, 0.2);
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .chart-title {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .chart-level-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 6px;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .chart-canvas-wrapper {
        position: relative;
        height: 400px;
        margin-bottom: 0.8rem;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        padding: 0.5rem;
        overflow: visible;
    }

    .chart-canvas-wrapper>div {
        width: 100% !important;
        height: 100% !important;
    }

    /* ===== COLORES POR NIVEL ===== */
    .level-L0 .chart-level-badge {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        color: #0a0e1a;
    }

    .level-L1 .chart-level-badge {
        background: linear-gradient(135deg, var(--accent-orange), var(--accent-red));
        color: #ffffff;
    }

    .level-L2 .chart-level-badge {
        background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));
        color: #ffffff;
    }

    /* ===== SECCIÓN DE COMPARATIVA (COMPACTA) ===== */
    .comparison-section {
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 2px solid var(--border-primary);
    }

    .comparison-header {
        background: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary));
        border: 1px solid var(--border-primary);
        border-radius: 10px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.2rem;
        text-align: center;
        box-shadow: var(--shadow-glow);
    }

    .comparison-header h2 {
        font-size: 1rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--accent-green), var(--primary-cyan));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0 0 0.3rem 0;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .comparison-header p {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin: 0;
    }

    .comparison-charts-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .comparison-chart-card:nth-child(3) {
        grid-column: 1 / 3;
    }

    .comparison-chart-card {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 10px;
        padding: 0.8rem;
        backdrop-filter: blur(20px);
        transition: all 0.3s ease;
        box-shadow: var(--shadow-card);
    }

    .comparison-chart-card:hover {
        border-color: rgba(0, 255, 136, 0.5);
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 255, 136, 0.2);
    }

    .comparison-chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.6rem;
        padding-bottom: 0.6rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .comparison-chart-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0;
    }

    .comparison-badge {
        padding: 0.25rem 0.6rem;
        border-radius: 5px;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));
        color: #ffffff;
    }

    .comparison-canvas-wrapper {
        position: relative;
        height: 200px;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 6px;
        padding: 0.5rem;
    }

    /* ===== RESPONSIVO ===== */
    @media (max-width: 1400px) {
        .dashboard-container {
            grid-template-columns: 260px 1fr;
            gap: 1rem;
        }

        .download-actions {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 1200px) {
        .dashboard-container {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .sidebar-filters {
            position: static;
            max-height: none;
        }

        .charts-row {
            grid-template-columns: 1fr;
        }

        .map-info-section {
            grid-template-columns: 1fr;
        }

        .comparison-charts-row {
            grid-template-columns: 1fr;
        }

        .comparison-chart-card:nth-child(3) {
            grid-column: 1;
        }
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
        }

        .level-buttons {
            grid-template-columns: 1fr;
        }

        .level-btn {
            padding: 0.8rem 1rem;
            font-size: 0.85rem;
        }

        .dashboard-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .header-stats {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .comparison-charts-row {
            grid-template-columns: 1fr;
        }

        .comparison-chart-card:nth-child(3) {
            grid-column: 1;
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

    /* ===== ESTILOS PARA LOADING ===== */
    .loading-option {
        color: var(--text-muted);
        font-style: italic;
    }

    .disabled-option {
        color: var(--text-muted);
        background: var(--bg-tertiary);
    }

    /* Overlay para mensajes sobre canvas */
    .waiting-data-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(10, 14, 26, 0.95);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10;
        border-radius: 8px;
    }

    .waiting-data-overlay .waiting-data-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 2rem;
    }

    .waiting-data-overlay .waiting-data-message i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.5;
        color: var(--primary-cyan);
    }

    .waiting-data-overlay .waiting-data-message p {
        font-size: 0.9rem;
        margin: 0;
        color: var(--text-muted);
    }

    .waiting-data-overlay .waiting-data-message .subtitle {
        font-size: 0.75rem;
        margin-top: 0.5rem;
        opacity: 0.7;
    }

    /* ===== OCULTAR TODAS LAS NOTIFICACIONES DE PLOTLY ===== */

    /* Notificador principal de Plotly */
    .plotly .notifier,
    .js-plotly-plot .plotly .notifier {
        display: none !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }

    /* Contenedor de notificaciones */
    .plotly .notifier-container,
    .js-plotly-plot .plotly .notifier-container {
        display: none !important;
    }

    /* Mensajes de notificación individuales */
    .plotly .notifier-note,
    .js-plotly-plot .plotly .notifier-note {
        display: none !important;
    }

    /* Cualquier texto de snapshot */
    body>div[class*="notifier"],
    div[class*="plotly-notifier"] {
        display: none !important;
    }

    /* SVG de notificaciones */
    body>svg[class*="snapshot"],
    body>svg[class*="notifier"] {
        display: none !important;
    }

    /* Tooltips y overlays de Plotly */
    .plotly .svg-container .user-select-none {
        pointer-events: auto !important;
    }

    /* Forzar ocultamiento de cualquier div flotante de Plotly */
    body>div[style*="position: fixed"][style*="right"],
    body>div[style*="position: absolute"][style*="right"] {
        display: none !important;
    }

    /* ===== SECCIÓN DE COMPARATIVA INDEPENDIENTE ===== */
    .comparison-independent-section {
        margin-top: 0.5rem;
    }

    .comparison-main-header {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 12px;
        padding: 1rem 1.5rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-glow);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .comparison-header-info h2 {
        font-size: 0.90rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--accent-green), var(--primary-cyan));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.3rem;
    }

    .comparison-header-info p {
        font-size: 0.65rem;
        color: var(--text-secondary);
        margin: 0;
    }

    .comparison-charts-container {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* ===== RESPONSIVE PARA COMPARATIVA ===== */
    @media (max-width: 1200px) {
        .comparison-main-header {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }
    }

    @media (max-width: 768px) {
        .comparison-independent-section {
            margin-top: 2rem;
        }
    }

    /* ===== BOTÓN DE DESCARGA PDF ===== */
    .btn-download-main {
        width: 100%;
        padding: 14px 20px;
        margin-top: 25px;
        background: linear-gradient(135deg, #ff073a 0%, #ff4d00 100%);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        font-weight: 800;
        font-size: 0.9rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1);
        box-shadow: 0 4px 15px rgba(255, 7, 58, 0.3);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-download-main:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 8px 30px rgba(255, 7, 58, 0.6);
        letter-spacing: 1.5px;
    }

    .btn-download-main i {
        font-size: 1.2rem;
        transition: transform 0.3s ease;
    }

    .btn-download-main:hover i {
        transform: rotate(-10deg) scale(1.2);
    }
</style>

<!-- Fondo fijo del Chimborazo -->
<div class="dashboard-fixed-background"></div>

<div class="dashboard-page-wrapper">
    <div class="dashboard-container">

        <!-- Sistema de Filtros -->
        <aside class="sidebar-filters">
            <div class="sidebar-header">
                <i class="fas fa-filter sidebar-icon"></i>
                <h2 class="sidebar-title">Filtros</h2>
            </div>

            <!-- Nivel de Datos -->
            <div class="level-selector">
                <label class="level-selector-label">Nivel de Datos</label>
                <div class="level-buttons">
                    <button class="level-btn active" data-level="L0" onclick="switchLevel('L0')">
                        <i class="fas fa-wind"></i>
                        <span>L0</span>
                    </button>
                    <button class="level-btn" data-level="L1" onclick="switchLevel('L1')">
                        <i class="fas fa-cloud-sun"></i>
                        <span>L1</span>
                    </button>
                    <button class="level-btn" data-level="L2" onclick="switchLevel('L2')">
                        <i class="fas fa-chart-line"></i>
                        <span>L2</span>
                    </button>
                </div>
            </div>

            <!-- Estación Meteorológica (SE LLENARÁ DESDE BD) -->
            <div class="filter-section">
                <div class="filter-section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    Estación Meteorológica
                </div>
                <div class="filter-group">
                    <select class="filter-input" id="filterEstacion" onchange="onEstacionChange()">
                        <option value="">Seleccione una estación...</option>
                    </select>
                </div>
            </div>

            <!-- Rango Temporal (AÑO Y MES - SE LLENARÁN DESDE BD) -->
            <div class="filter-section">
                <div class="filter-section-title">
                    <i class="fas fa-calendar-alt"></i>
                    Rango Temporal
                </div>
                <div class="filter-group">
                    <div class="date-range-container">
                        <div class="date-range-col">
                            <label class="filter-label">Año</label>
                            <select class="filter-input" id="filterAnio" onchange="onAnioChange()">
                                <option value="">Seleccione año</option>
                            </select>
                        </div>
                        <div class="date-range-col">
                            <label class="filter-label">Mes</label>
                            <select class="filter-input" id="filterMes" onchange="onMesChange()">
                                <option value="">Seleccione mes</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="filter-actions">
                <button class="btn-filter btn-reset" onclick="resetFilters()">
                    <i class="fas fa-undo"></i>
                    Restablecer
                </button>
            </div>

            <!-- Botones de Descarga -->
            <button class="btn-download-main" onclick="openDownloadModal()">
                <i class="fas fa-file-pdf fa-lg"></i> DESCARGAR REPORTE PDF
            </button>


        </aside>

        <!-- Contenido Principal -->
        <main class="main-content">

            <!-- Header del Dashboard -->
            <div class="dashboard-header">
                <div class="header-info">
                    <h1>Dashboard Meteorológico</h1>
                    <p>Datos Limpios</p>
                </div>
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="totalRegistros">---</div>
                        <div class="stat-label">Registros</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="estacionesActivas">---</div>
                        <div class="stat-label">Estaciones</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="ultimaActualizacion">---</div>
                        <div class="stat-label">Última Actualización</div>
                    </div>
                </div>
            </div>

            <!-- Contenedor de Mapa e Información -->
            <div class="map-info-section">
                <!-- Mapa del Chimborazo (SIN IMAGEN - SOLO CONTENEDOR) -->
                <div class="map-section">
                    <div class="section-header">
                        <i class="fas fa-mountain"></i>
                        <h3 class="section-title">Mapa de Estaciones</h3>
                    </div>

                    <!-- Contenedor vacío para el mapa (se llenará desde BD) -->
                    <!-- Contenedor para el mapa (se llenará desde BD) -->
                    <div class="map-image-container" id="mapContainer">
                        <div class="map-placeholder" id="mapPlaceholder">
                            <i class="fas fa-map-marked-alt"></i>
                            <p>Seleccione una estación para ver el mapa</p>
                        </div>
                    </div>
                </div>

                <!-- Información de la Estación (SE LLENARÁ DESDE BD) -->
                <div class="info-card">
                    <div class="info-header">
                        <i class="fas fa-info-circle"></i>
                        <h3 class="info-title">Información de la Estación</h3>
                    </div>

                    <div class="info-content" id="stationInfoContent">
                        <div class="info-item">
                            <span class="detail-label">Código:</span>
                            <span class="detail-value" id="infoCodigo">---</span>
                        </div>
                        <div class="info-item">
                            <span class="detail-label">Comunidad:</span>
                            <span class="detail-value" id="infoComunidad">---</span>
                        </div>
                        <div class="info-item">
                            <span class="detail-label">Altura:</span>
                            <span class="detail-value" id="infoAltura">---</span>
                        </div>

                        <div class="info-item">
                            <span class="detail-label">Estado:</span>
                            <span class="detail-value" id="infoEstado">---</span>
                        </div>
                        <div class="info-item">
                            <span class="detail-label">Fecha Instalación:</span>
                            <span class="detail-value" id="infoFechaInsta">---</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========== SECCIÓN 1: GRÁFICAS DE LOS DATOS ========== -->
            <div class="charts-container">

                <!-- Sección L0 -->
                <div class="charts-section level-L0" id="chartsL0">
                    <div class="section-header">
                        <i class="fas fa-wind"></i>
                        <h3 class="section-title">Nivel L0 - Análisis de Viento</h3>
                    </div>

                    <!-- Fila 1: Serie Temporal (1 gráfica ancho completo) -->
                    <div class="charts-row">
                        <div class="chart-card level-L0">
                            <div class="chart-header">
                                <h4 class="chart-title">Serie Temporal - Velocidad del Viento</h4>
                                <div class="chart-level-badge">L0</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chart1" style="width:100%; height:100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Métricas + Rosa de Vientos (2 gráficas) -->
                    <div class="charts-row charts-row-two-cols">
                        <div class="chart-card level-L0">
                            <div class="chart-header">
                                <h4 class="chart-title">Dirección del Viento</h4>
                                <div class="chart-level-badge">L0</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chart2" style="width:100%; height:100%;"></div>
                            </div>
                        </div>

                        <div class="chart-card level-L0">
                            <div class="chart-header">
                                <h4 class="chart-title">Rosa de Vientos</h4>
                                <div class="chart-level-badge">L0</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chart3" style="width:100%; height:100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 3: Distribución + Intensidad por Hora (2 gráficas) -->
                    <div class="charts-row charts-row-two-cols">
                        <div class="chart-card level-L0">
                            <div class="chart-header">
                                <h4 class="chart-title">Evolución Horaria del Viento</h4>
                                <div class="chart-level-badge">L0</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chart4" style="width:100%; height:100%;"></div>
                            </div>
                        </div>

                        <div class="chart-card level-L0">
                            <div class="chart-header">
                                <h4 class="chart-title">Distribución Cardinal del Viento</h4>
                                <div class="chart-level-badge">L0</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chart5" style="width:100%; height:100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 4: Mapa de Calor - Velocidad del Viento (1 gráfica ancho completo) -->
                    <div class="charts-row">
                        <div class="chart-card level-L0">
                            <div class="chart-header">
                                <h4 class="chart-title">Mapa de Calor - Intensidad del Viento por Día y Hora</h4>
                                <div class="chart-level-badge">L0</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chart6" style="width:100%; height:100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 5: Detección de Rachas de Viento (1 gráfica ancho completo) -->
                    <div class="charts-row">
                        <div class="chart-card level-L0">
                            <div class="chart-header">
                                <h4 class="chart-title">Detección de Rachas de Viento - Eventos Extremos</h4>
                                <div class="chart-level-badge">L0</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chart7" style="width:100%; height:100%;"></div>
                            </div>
                        </div>
                    </div>




                </div>

                <!-- Sección L1 -->
                <div class="charts-section level-L1" id="chartsL1" style="display: none;">
                    <div class="section-header">
                        <i class="fas fa-cloud-sun"></i>
                        <h3 class="section-title">Nivel L1 - Análisis Meteorológico</h3>
                    </div>

                    <!-- Fila 1: Temperatura Diaria (1 gráfica ancho completo) -->
                    <div class="charts-row">
                        <div class="chart-card level-L1">
                            <div class="chart-header">
                                <h4 class="chart-title">Serie Temporal - Temperatura del Aire</h4>
                                <div class="chart-level-badge">L1</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartTempL1" style="width:100%; height:100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Humedad + Precipitación (2 gráficas) -->
                    <div class="charts-row charts-row-two-cols">
                        <div class="chart-card level-L1">
                            <div class="chart-header">
                                <h4 class="chart-title">Distribución de Humedad Relativa</h4>
                                <div class="chart-level-badge">L1</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartHumL1" style="width:100%; height:100%;"></div>
                            </div>
                        </div>

                        <div class="chart-card level-L1">
                            <div class="chart-header">
                                <h4 class="chart-title">Mapa de Calor - Radiación Solar por Día y Hora</h4>
                                <div class="chart-level-badge">L1</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartPresionL1" style="width:100%; height:100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 3: Viento + Presión (2 gráficas) -->
                    <div class="charts-row charts-row-two-cols">
                        <div class="chart-card level-L1">
                            <div class="chart-header">
                                <h4 class="chart-title">Velocidad del Viento y Temperatura</h4>
                                <div class="chart-level-badge">L1</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartVientoL1" style="width:100%; height:100%;"></div>
                            </div>
                        </div>

                        <div class="chart-card level-L1">
                            <div class="chart-header">
                                <h4 class="chart-title">Mapa de Calor - Presión Atmosférica por Día y Hora</h4>
                                <div class="chart-level-badge">L1</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartPresionAtmosfericaL1" style="width:100%; height:100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 4: Balance Energético (1 gráfica ancho completo) -->
                    <div class="charts-row">
                        <div class="chart-card level-L1">
                            <div class="chart-header">
                                <h4 class="chart-title">Balance Energético - Análisis Multivariable</h4>
                                <div class="chart-level-badge">L1</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartBalanceEnergeticoL1" style="width:100%; height:100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 5: Punto de Rocío Calculado (1 gráfica ancho completo) -->
                    <div class="charts-row">
                        <div class="chart-card level-L1">
                            <div class="chart-header">
                                <h4 class="chart-title">Punto de Rocío - Análisis de Condensación y Heladas</h4>
                                <div class="chart-level-badge">L1</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartPuntoRocioL1" style="width:100%; height:100%;"></div>
                            </div>
                        </div>
                    </div>


                </div>

                <!-- Sección L2 -->
                <div class="charts-section level-L2" id="chartsL2" style="display: none;">
                    <div class="section-header">
                        <i class="fas fa-chart-line"></i>
                        <h3 class="section-title">Nivel L2 - Análisis Climatológico</h3>
                    </div>

                    <!-- Fila 1: Tendencia Anual (1 gráfica ancho completo) -->
                    <div class="charts-row">
                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Tendencia Anual</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartTendenciaL2" style="width:100%; height:400px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: Climograma + Análisis de Tendencias (2 gráficas) -->
                    <div class="charts-row charts-row-two-cols">
                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Climograma Integral</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartClimogramaL2" style="width:100%; height:400px;"></div>
                            </div>
                        </div>

                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Análisis de Tendencias</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartAnalisisL2" style="width:100%; height:100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 3: Distribución + Precipitaciones (2 gráficas) -->
                    <div class="charts-row charts-row-two-cols">
                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Distribución Estacional</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartDistribucionL2" style="width:100%; height:100%;"></div>
                            </div>
                        </div>

                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Análisis de Precipitaciones</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartPrecipitacionesL2" style="width:100%; height:100%;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 4: Índice de Aridez (1 gráfica ancho completo) -->
                    <div class="charts-row">
                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Índice de Aridez</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartAridezL2" style="width:100%; height:400px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 5: Análisis de Heladas (1 gráfica ancho completo) -->
                    <div class="charts-row">
                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Análisis de Heladas</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <div id="chartHeladasL2" style="width:100%; height:400px;"></div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- ========== SEPARADOR VISUAL ========== -->
            <div class="section-divider"></div>

            <div class="comparison-independent-section">
                <div class="comparison-main-header">
                    <div class="comparison-header-info">
                        <h2>Comparativa entre Estaciones</h2>
                        <p>Análisis comparativo de datos meteorológicos entre múltiples estaciones</p>
                    </div>
                </div>

                <!-- Contenedor de gráficas con la MISMA estructura que L0/L1/L2 -->
                <div class="comparison-charts-container">

                    <!-- Fila 1: 2 gráficas (dos columnas) -->
                    <div class="charts-row charts-row-two-cols">
                        <div class="chart-card level-L0">
                            <div class="chart-header">
                                <h4 class="chart-title">Comparación de Temperatura</h4>
                                <div class="chart-level-badge"
                                    style="background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));">
                                    COMP</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <canvas id="comparisonChart1"></canvas>
                            </div>
                        </div>

                        <div class="chart-card level-L0">
                            <div class="chart-header">
                                <h4 class="chart-title">Comparación de Precipitación</h4>
                                <div class="chart-level-badge"
                                    style="background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));">
                                    COMP</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <canvas id="comparisonChart2"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: 1 gráfica (ancho completo) -->
                    <div class="charts-row">
                        <div class="chart-card level-L0">
                            <div class="chart-header">
                                <h4 class="chart-title">Modo de prueba 1</h4>
                                <div class="chart-level-badge"
                                    style="background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));">
                                    COMP</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <canvas id="comparisonChart3"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2: 1 gráfica (ancho completo) -->
                    <div class="charts-row">
                        <div class="chart-card level-L0">
                            <div class="chart-header">
                                <h4 class="chart-title">Modo Prueba 2</h4>
                                <div class="chart-level-badge"
                                    style="background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));">
                                    COMP</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <canvas id="comparisonChart3"></canvas>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <!-- Modal de Descarga -->
    <div class="modal-overlay" id="downloadModal">
        <div class="modal-card">
            <div class="modal-header">
                <div class="modal-title-section">
                    <div class="modal-logo">
                        <img src="../public/img/logo-geaa-espoch.jpg" alt="Logo GEAA ESPOCH">
                    </div>
                    <div class="modal-title-text">
                        <h3>Formulario de Descarga</h3>
                        <p>Complete la información para descargar los datos</p>
                    </div>
                </div>
                <button class="modal-close" onclick="closeDownloadModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="modal-body">
                <form id="downloadForm">
                    <div class="form-grid">
                        <!-- Columna 1 -->
                        <div class="form-group">
                            <label class="form-label">
                                Nombre Completo
                                <span class="required">*</span>
                            </label>
                            <input type="text" class="form-input" id="nombreCompleto"
                                placeholder="Ej: Juan Pérez García" maxlength="100">
                            <span class="form-error" id="errorNombre">Solo se permiten letras y espacios</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Institución
                                <span class="required">*</span>
                            </label>
                            <input type="text" class="form-input" id="institucion" placeholder="Ej: ESPOCH"
                                maxlength="100">
                            <span class="form-error" id="errorInstitucion">Solo se permiten letras, números y
                                espacios</span>
                        </div>

                        <!-- Columna 2 -->
                        <div class="form-group">
                            <label class="form-label">
                                Cédula/Pasaporte
                                <span class="required">*</span>
                            </label>
                            <input type="text" class="form-input" id="cedula" placeholder="Ej: 1234567890"
                                maxlength="10">
                            <span class="form-error" id="errorCedula">Debe contener exactamente 10 dígitos</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                Motivo de Descarga
                                <span class="required">*</span>
                            </label>
                            <input type="text" class="form-input" id="motivo"
                                placeholder="Ej: Proyecto de investigación" maxlength="100">
                            <span class="form-error" id="errorMotivo">Este campo es requerido</span>
                        </div>
                    </div>

                    <!-- Términos y Condiciones -->
                    <div class="terms-section">
                        <div class="terms-title">
                            <i class="fas fa-shield-alt"></i>
                            Declaración de Términos
                        </div>
                        <div class="terms-content">
                            Al descargar estos datos, me comprometo a:
                            <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                                <li>Citar correctamente al GEAA (Grupo de Energías Alternativas y Ambiente) - ESPOCH
                                </li>
                                <li>Referenciar el proyecto de investigación en cualquier publicación</li>
                                <li>Utilizar los datos únicamente con fines educativos y de investigación</li>
                                <li>No comercializar ni redistribuir los datos sin autorización previa</li>
                            </ul>
                        </div>
                        <div class="terms-checkbox">
                            <input type="checkbox" id="acceptTerms">
                            <label for="acceptTerms">
                                Acepto los términos y condiciones, y me comprometo a utilizar los datos de manera ética
                                y responsable.
                            </label>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button class="btn-modal btn-modal-cancel" onclick="closeDownloadModal()">
                    <i class="fas fa-times"></i>
                    Cancelar
                </button>
                <button class="btn-modal btn-modal-submit" id="btnSubmitDownload" onclick="submitDownloadForm()"
                    disabled>
                    <i class="fas fa-download"></i>
                    <span id="btnDownloadText">Descargar</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Plotly.js - Optimizado para Big Data -->
<script src="https://cdn.plot.ly/plotly-2.27.0.min.js" charset="utf-8"></script>
<script>
    // ===== VARIABLES GLOBALES DEL MODAL =====
    let tipoDescarga = null; // 'pdf' o 'csv'

    // ===== FUNCIONES PARA ABRIR/CERRAR MODAL =====
    function openDownloadModal(tipo) {
        console.log('📂 Abriendo modal de descarga:', tipo);

        tipoDescarga = tipo;
        const modal = document.getElementById('downloadModal');

        if (modal) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden'; // Bloquear scroll del body

            // Actualizar texto del botón según tipo
            const btnText = document.getElementById('btnDownloadText');
            if (btnText) {
                btnText.textContent = tipo === 'pdf' ? 'Descargar PDF' : 'Descargar CSV';
            }

            // Resetear formulario
            resetForm();
        }
    }

    function closeDownloadModal() {
        console.log('❌ Cerrando modal de descarga');

        const modal = document.getElementById('downloadModal');

        if (modal) {
            modal.classList.remove('active');
            document.body.style.overflow = ''; // Restaurar scroll del body

            // Resetear todo
            resetForm();
            tipoDescarga = null;
        }
    }

    // ===== VALIDACIÓN EN TIEMPO REAL =====
    document.addEventListener('DOMContentLoaded', function () {
        const nombreInput = document.getElementById('nombreCompleto');
        const institucionInput = document.getElementById('institucion');
        const cedulaInput = document.getElementById('cedula');
        const motivoInput = document.getElementById('motivo');
        const acceptTerms = document.getElementById('acceptTerms');
        const btnSubmit = document.getElementById('btnSubmitDownload');

        // Validar Nombre (solo letras y espacios)
        if (nombreInput) {
            nombreInput.addEventListener('input', function () {
                const value = this.value;
                const regex = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/;

                if (!regex.test(value) || value.length === 0) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }

                validateForm();
            });
        }

        // Validar Institución (letras, números y espacios)
        if (institucionInput) {
            institucionInput.addEventListener('input', function () {
                const value = this.value;
                const regex = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]*$/;

                if (!regex.test(value) || value.length === 0) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }

                validateForm();
            });
        }

        // Validar Cédula (exactamente 10 dígitos)
        if (cedulaInput) {
            cedulaInput.addEventListener('input', function () {
                // Solo permitir números
                this.value = this.value.replace(/[^0-9]/g, '');

                const value = this.value;

                if (value.length !== 10) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }

                validateForm();
            });
        }

        // Validar Motivo (no vacío)
        if (motivoInput) {
            motivoInput.addEventListener('input', function () {
                const value = this.value.trim();

                if (value.length === 0) {
                    this.classList.add('error');
                } else {
                    this.classList.remove('error');
                }

                validateForm();
            });
        }

        // Validar checkbox de términos
        if (acceptTerms) {
            acceptTerms.addEventListener('change', function () {
                validateForm();
            });
        }

        // Cerrar modal al hacer clic fuera
        const modal = document.getElementById('downloadModal');
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === this) {
                    closeDownloadModal();
                }
            });
        }

        // Cerrar modal con tecla ESC
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDownloadModal();
            }
        });
    });

    // ===== VALIDAR FORMULARIO COMPLETO =====
    function validateForm() {
        const nombreInput = document.getElementById('nombreCompleto');
        const institucionInput = document.getElementById('institucion');
        const cedulaInput = document.getElementById('cedula');
        const motivoInput = document.getElementById('motivo');
        const acceptTerms = document.getElementById('acceptTerms');
        const btnSubmit = document.getElementById('btnSubmitDownload');

        // Validar todos los campos
        const nombreValido = nombreInput && nombreInput.value.trim().length > 0 && !nombreInput.classList.contains('error');
        const institucionValida = institucionInput && institucionInput.value.trim().length > 0 && !institucionInput.classList.contains('error');
        const cedulaValida = cedulaInput && cedulaInput.value.length === 10 && !cedulaInput.classList.contains('error');
        const motivoValido = motivoInput && motivoInput.value.trim().length > 0 && !motivoInput.classList.contains('error');
        const terminosAceptados = acceptTerms && acceptTerms.checked;

        // Habilitar/deshabilitar botón
        if (btnSubmit) {
            btnSubmit.disabled = !(nombreValido && institucionValida && cedulaValida && motivoValido && terminosAceptados);
        }
    }

    // ===== RESETEAR FORMULARIO =====
    function resetForm() {
        const nombreInput = document.getElementById('nombreCompleto');
        const institucionInput = document.getElementById('institucion');
        const cedulaInput = document.getElementById('cedula');
        const motivoInput = document.getElementById('motivo');
        const acceptTerms = document.getElementById('acceptTerms');

        if (nombreInput) {
            nombreInput.value = '';
            nombreInput.classList.remove('error');
        }

        if (institucionInput) {
            institucionInput.value = '';
            institucionInput.classList.remove('error');
        }

        if (cedulaInput) {
            cedulaInput.value = '';
            cedulaInput.classList.remove('error');
        }

        if (motivoInput) {
            motivoInput.value = '';
            motivoInput.classList.remove('error');
        }

        if (acceptTerms) {
            acceptTerms.checked = false;
        }

        validateForm();
    }


    // ===== WRAPPER PARA RETROCOMPATIBILIDAD =====
    // Algunos botones antiguos todavía llaman a submitDownloadForm()
    // Esta función simplemente redirige a generatePDF()
    function submitDownloadForm() {
        console.log('⚠️ submitDownloadForm() llamada - redirigiendo a generatePDF()');
        generatePDF();
    }


    // ===== NOTIFICACIÓN DE DESCARGA EXITOSA =====
    function showDownloadSuccessNotification(filename) {
        const notification = document.createElement('div');
        notification.className = 'custom-download-notification';
        notification.innerHTML = `
        <div class="custom-download-notification-content">
            <i class="fas fa-check-circle custom-download-notification-icon" style="color: var(--accent-green);"></i>
            <div class="custom-download-notification-text">
                ¡Descarga Completada!
            </div>
            <div class="custom-download-notification-subtext">
                ${filename}
            </div>
            <div class="custom-download-notification-success">
                <i class="fas fa-folder-open"></i>
                <span>Revisa tu carpeta de descargas</span>
            </div>
        </div>
    `;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.style.animation = 'downloadPulse 0.3s ease-in reverse';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }, 3000);
    }
    // ===== VARIABLES GLOBALES =====
    let currentLevel = 'L0';
    let selectedStation = '';
    let availableStations = [];
    let availableYears = [];
    let availableMonths = [];
    let charts = {};
    let totalEstacionesActivas = 0;
    // ===== FUNCIONES DE CONTROL =====
    function switchLevel(level) {
        currentLevel = level;

        // Actualizar botones
        document.querySelectorAll('.level-btn').forEach(btn => btn.classList.remove('active'));
        document.querySelector(`[data-level="${level}"]`).classList.add('active');

        // Mostrar/ocultar secciones
        document.querySelectorAll('.charts-section').forEach(section => section.style.display = 'none');
        const targetSection = document.getElementById(`charts${level}`);
        if (targetSection) {
            targetSection.style.display = 'grid';
        }

        // Si hay estación seleccionada
        if (selectedStation) {
            loadStationLevelStats();

            clearYearMonthSelects();

            loadYearsFromDatabase();

            showWaitingDataInCharts();
        } else {
            updateStatsDisplay(0, totalEstacionesActivas, '---');
            clearYearMonthSelects();

            showWaitingDataInCharts();
        }
    }

    function showWaitingDataInCharts() {
        let chartIds = [];

        if (currentLevel === 'L0') {
            chartIds = ['chart1', 'chart2', 'chart3', 'chart4', 'chart5', 'chart6', 'chart7'];
        } else if (currentLevel === 'L1') {
            chartIds = ['chartTempL1', 'chartHumL1', 'chartPresionL1', 'chartVientoL1',
                'chartPresionAtmosfericaL1', 'chartBalanceEnergeticoL1', 'chartPuntoRocioL1'];
        } else if (currentLevel === 'L2') {
            chartIds = ['chartTendenciaL2', 'chartClimogramaL2', 'chartAnalisisL2',
                'chartDistribucionL2', 'chartPrecipitacionesL2', 'chartAridezL2',
                'chartHeladasL2', 'chartPerfilTermicoL2', 'chartSecasL2',
                'chartConfortClimaticoL2'];
        }

        chartIds.forEach(chartId => {
            // Destruir gráfica Plotly existente si hay una
            if (charts[chartId]) {
                try {
                    Plotly.purge(chartId);
                } catch (e) {
                    console.log('No hay gráfica Plotly para destruir en ' + chartId);
                }
                delete charts[chartId];
            }

            const chartDiv = document.getElementById(chartId);
            if (chartDiv) {
                const wrapper = chartDiv.closest('.chart-canvas-wrapper');
                if (wrapper) {
                    // Limpiar el div
                    chartDiv.innerHTML = '';

                    // Remover mensaje anterior si existe
                    const existingMessage = wrapper.querySelector('.waiting-data-overlay');
                    if (existingMessage) {
                        existingMessage.remove();
                    }

                    // Agregar overlay con mensaje
                    const overlay = document.createElement('div');
                    overlay.className = 'waiting-data-overlay';
                    overlay.innerHTML = `
                    <div class="waiting-data-message">
                        <i class="fas fa-clock"></i>
                        <p>Esperando Datos</p>
                        <p class="subtitle">Seleccione año y mes para visualizar</p>
                    </div>
                `;
                    wrapper.appendChild(overlay);
                }
            }
        });

        console.log('📊 Mostrando mensaje de "Esperando Datos" en gráficas');
    }
    function removeWaitingOverlay(chartId) {
        const chartDiv = document.getElementById(chartId);
        if (chartDiv) {
            const wrapper = chartDiv.closest('.chart-canvas-wrapper');
            if (wrapper) {
                const overlay = wrapper.querySelector('.waiting-data-overlay');
                if (overlay) {
                    overlay.remove();
                }
            }
        }
    }

    // ===== CARGAR GRÁFICAS DESDE EL BACKEND =====
    async function loadChartsFromDatabase(level, estacionId, anio, mes) {
        if (!estacionId || !anio || !mes) {
            console.warn('⚠️ Faltan parámetros para cargar gráficas:', { level, estacionId, anio, mes });
            return;
        }

        console.log(`📊 Cargando gráficas ${level} para estación ${estacionId}, ${anio}/${mes}`);

        try {
            if (level === 'L0') {
                await loadChartsL0(estacionId, anio, mes);
            } else if (level === 'L1') {
                await loadChartsL1(estacionId, anio, mes);
            } else if (level === 'L2') {
                await loadChartsL2(estacionId, anio, mes);
            }
        } catch (error) {
            console.error(`❌ Error al cargar gráficas de nivel ${level}:`, error);
            alert(`Error al cargar gráficas: ${error.message}`);
        }
    }

    // ===== CARGAR GRÁFICAS L2 =====
    async function loadChartsL2(estacionId, anio, mes) {
        console.log('📊 Cargando gráficas L2...', { estacionId, anio, mes });

        // Fallback a globales si faltan argumentos
        if (!estacionId) estacionId = selectedStation;
        if (!anio) anio = document.getElementById('anio')?.value;
        if (!mes) mes = document.getElementById('mes')?.value;

        // 1. Tendencia Anual (Existente) - Usa globales internamente
        if (typeof loadChartTendenciaL2 === 'function') loadChartTendenciaL2();

        // 2. Climograma (Existente) - Usa globales internamente
        if (typeof loadChartClimogramaL2 === 'function') loadChartClimogramaL2();

        // 3. Análisis de Tendencias (Comparativo)
        if (typeof loadChartAnalisisL2 === 'function') loadChartAnalisisL2(estacionId, anio);

        // 4. Distribución Estacional
        if (typeof loadChartDistribucionL2 === 'function') loadChartDistribucionL2(estacionId);

        // 5. Análisis de Precipitaciones
        if (typeof loadChartPrecipitacionesL2 === 'function') loadChartPrecipitacionesL2(estacionId, anio);

        // 6. Índice de Aridez
        if (typeof loadChartAridezL2 === 'function') loadChartAridezL2(estacionId, anio);

        // 7. Análisis de Heladas
        if (typeof loadChartHeladasL2 === 'function') loadChartHeladasL2(estacionId, anio);

        console.log('✅ Solicitud de carga de gráficas L2 enviada comepleta');
    }

    // ===== CREAR GRÁFICA DE TENDENCIA ANUAL L2 =====
    function createChartTendenciaL2(data) {
        const chartDiv = document.getElementById('chartTendenciaL2');
        if (!chartDiv) {
            console.error('❌ No se encontró el div chartTendenciaL2');
            return;
        }

        // Preparar datos para Plotly
        const meses = data.meses || [];
        const tempPromedio = data.temperatura_promedio || [];
        const precipitacion = data.precipitacion_total || [];
        const humedad = data.humedad_promedio || [];

        const traces = [
            {
                x: meses,
                y: tempPromedio,
                name: 'Temperatura (°C)',
                type: 'scatter',
                mode: 'lines+markers',
                marker: { color: '#ff6b6b', size: 8 },
                line: { width: 3 }
            },
            {
                x: meses,
                y: precipitacion,
                name: 'Precipitación (mm)',
                type: 'bar',
                yaxis: 'y2',
                marker: { color: '#4ecdc4' }
            },
            {
                x: meses,
                y: humedad,
                name: 'Humedad (%)',
                type: 'scatter',
                mode: 'lines',
                line: { dash: 'dot', width: 2, color: '#95e1d3' }
            }
        ];

        const layout = {
            title: 'Tendencia Anual - Variables Climatológicas',
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor: 'rgba(0,0,0,0.1)',
            font: { color: '#ffffff', family: 'Inter, sans-serif' },
            xaxis: { title: 'Mes', gridcolor: 'rgba(255,255,255,0.1)' },
            yaxis: {
                title: 'Temperatura (°C) / Humedad (%)',
                gridcolor: 'rgba(255,255,255,0.1)'
            },
            yaxis2: {
                title: 'Precipitación (mm)',
                overlaying: 'y',
                side: 'right',
                gridcolor: 'rgba(255,255,255,0.05)'
            },
            legend: { orientation: 'h', y: -0.2 },
            margin: { t: 60, r: 80, b: 60, l: 80 }
        };

        const config = {
            responsive: true,
            displayModeBar: true,
            displaylogo: false
        };

        Plotly.newPlot(chartDiv, traces, layout, config);
        charts['chartTendenciaL2'] = true;
    }

    // ===== CREAR GRÁFICA DE CLIMOGRAMA L2 =====
    function createChartClimagramaL2(data) {
        const chartDiv = document.getElementById('chartClimogramaL2');
        if (!chartDiv) {
            console.error('❌ No se encontró el div chartClimogramaL2');
            return;
        }

        // Preparar datos
        const periodos = data.periodos || [];
        const tempMax = data.temperatura_maxima || [];
        const tempMin = data.temperatura_minima || [];
        const tempPromedio = data.temperatura_promedio || [];
        const precipitacion = data.precipitacion_total || [];

        const traces = [
            {
                x: periodos,
                y: tempMax,
                name: 'Temp. Máxima',
                type: 'scatter',
                mode: 'lines',
                line: { color: '#ff073a', width: 2 },
                fill: 'tonexty'
            },
            {
                x: periodos,
                y: tempPromedio,
                name: 'Temp. Promedio',
                type: 'scatter',
                mode: 'lines+markers',
                line: { color: '#ffd60a', width: 3 },
                marker: { size: 6 }
            },
            {
                x: periodos,
                y: tempMin,
                name: 'Temp. Mínima',
                type: 'scatter',
                mode: 'lines',
                line: { color: '#0096ff', width: 2 },
                fill: 'tonexty'
            },
            {
                x: periodos,
                y: precipitacion,
                name: 'Precipitación',
                type: 'bar',
                yaxis: 'y2',
                marker: { color: '#00ff88', opacity: 0.6 }
            }
        ];

        const layout = {
            title: 'Climograma - Temperatura y Precipitación',
            paper_bgcolor: 'rgba(0,0,0,0)',
            plot_bgcolor: 'rgba(0,0,0,0.1)',
            font: { color: '#ffffff', family: 'Inter, sans-serif' },
            xaxis: {
                title: 'Período',
                gridcolor: 'rgba(255,255,255,0.1)'
            },
            yaxis: {
                title: 'Temperatura (°C)',
                gridcolor: 'rgba(255,255,255,0.1)'
            },
            yaxis2: {
                title: 'Precipitación (mm)',
                overlaying: 'y',
                side: 'right',
                gridcolor: 'rgba(255,255,255,0.05)'
            },
            legend: { orientation: 'h', y: -0.2 },
            margin: { t: 60, r: 80, b: 80, l: 80 }
        };

        const config = {
            responsive: true,
            displayModeBar: true,
            displaylogo: false
        };

        Plotly.newPlot(chartDiv, traces, layout, config);
        charts['chartClimogramaL2'] = true;
    }

    // ===== MOSTRAR ERROR EN GRÁFICA =====
    function showChartError(chartId, message) {
        const chartDiv = document.getElementById(chartId);
        if (chartDiv) {
            chartDiv.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #ff073a;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p style="font-size: 1rem; font-weight: 600;">${message}</p>
                </div>
            `;
        }
    }

    // ===== MOSTRAR INFORMACIÓN EN GRÁFICA =====
    function showChartInfo(chartId, title, message) {
        const chartDiv = document.getElementById(chartId);
        if (chartDiv) {
            chartDiv.innerHTML = `
                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: var(--primary-cyan);">
                    <i class="fas fa-info-circle" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                    <p style="font-size: 1.1rem; font-weight: 700; margin-bottom: 0.5rem;">${title}</p>
                    <p style="font-size: 0.9rem; color: var(--text-secondary);">${message}</p>
                </div>
            `;
        }
    }

    // ===== STUBS PARA L0 Y L1 (si no existen) =====
    async function loadChartsL0(estacionId, anio, mes) {
        console.log('📊 Función loadChartsL0 - Implementar según necesidades');
        // TODO: Implementar carga específica de gráficas L0
    }

    async function loadChartsL1(estacionId, anio, mes) {
        console.log('📊 Función loadChartsL1 - Implementar según necesidades');
        // TODO: Implementar carga específica de gráficas L1
    }

    // ===== ELIMINAR ESTA FUNCIÓN COMPLETA =====
    function applyFilters() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion) {
            return;
        }

        if (!anio) {
            return;
        }

        if (!mes) {
            return;
        }

        // Cargar datos desde la BD
        loadDataFromDatabase(estacion, anio, mes);
        loadChartsFromDatabase(currentLevel, estacion, anio, mes);
    }

    function resetFilters() {
        document.getElementById('filterEstacion').selectedIndex = 0;
        clearYearMonthSelects();
        selectedStation = '';

        // Limpiar información de estación
        updateStationInfo(null);

        // Detener carrusel
        stopCarousel();

        // Resetear a valores iniciales
        updateStatsDisplay(0, totalEstacionesActivas, '---');

        showWaitingDataInCharts();
    }

    function downloadPDF() {
        // Validar que haya filtros seleccionados
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            alert('⚠️ Por favor, selecciona Estación, Año y Mes antes de descargar.');
            return;
        }

        // Abrir modal con tipo 'pdf'
        openDownloadModal('pdf');
    }

    function downloadCSV() {
        // Validar que haya filtros seleccionados
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            alert('⚠️ Por favor, selecciona Estación, Año y Mes antes de descargar.');
            return;
        }

        // Abrir modal con tipo 'csv'
        openDownloadModal('csv');
    }



    // ===== FUNCIONES DE EVENTOS DE SELECTS =====
    function onEstacionChange() {
        const estacionId = document.getElementById('filterEstacion').value;
        selectedStation = estacionId;

        if (!estacionId) {
            clearYearMonthSelects();
            updateStationInfo(null);
            updateStatsDisplay(0, totalEstacionesActivas, '---');
            showWaitingDataInCharts();
            return;
        }

        // Cargar información de la estación
        loadStationInfo(estacionId);

        // Cargar estadísticas específicas del nivel actual
        loadStationLevelStats();

        // Cargar años para el nivel actual
        loadYearsFromDatabase();

        showWaitingDataInCharts();
    }

    function updateStatsDisplay(registros, estaciones, ultimaActualizacion) {
        document.getElementById('totalRegistros').textContent = formatLargeNumber(registros);
        document.getElementById('estacionesActivas').textContent = estaciones;

        // Validar que ultimaActualizacion sea válido
        if (ultimaActualizacion && ultimaActualizacion !== '---' && ultimaActualizacion !== null) {
            const fecha = new Date(ultimaActualizacion);
            if (!isNaN(fecha.getTime())) {
                document.getElementById('ultimaActualizacion').textContent = fecha.toLocaleDateString('es-EC', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric'
                });
            } else {
                document.getElementById('ultimaActualizacion').textContent = '---';
            }
        } else {
            document.getElementById('ultimaActualizacion').textContent = '---';
        }
    }

    function formatLargeNumber(num) {
        if (num === 0) return '0';

        const absNum = Math.abs(num);

        // Millones (1,000,000+)
        if (absNum >= 1000000) {
            const millions = (num / 1000000).toFixed(2);
            return millions.endsWith('.00')
                ? (num / 1000000).toFixed(0) + 'M'
                : millions + 'M';
        }

        // Miles (1,000+)
        if (absNum >= 1000) {
            const thousands = (num / 1000).toFixed(1);
            return thousands.endsWith('.0')
                ? (num / 1000).toFixed(0) + 'K'
                : thousands + 'K';
        }

        // Números menores a 1000
        return num.toLocaleString('es-EC');
    }

    async function loadStationLevelStats() {
        if (!selectedStation) {
            updateStatsDisplay(0, totalEstacionesActivas, '---');
            return;
        }

        try {
            console.log('🔄 Cargando estadísticas de estación y nivel...');

            const response = await fetch(`../controller/CDashboard.php?action=get_station_level_stats&id_estacion=${selectedStation}&nivel=${currentLevel}`);

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }

            const data = await response.json();

            if (data.success) {
                const stats = data.data;
                updateStatsDisplay(
                    stats.total_registros,
                    totalEstacionesActivas,
                    stats.ultima_actualizacion
                );
                console.log('✅ Estadísticas de estación cargadas');
            } else {
                throw new Error(data.error || 'Error desconocido al cargar estadísticas');
            }

        } catch (error) {
            console.error('❌ Error al cargar estadísticas de estación:', error);
            updateStatsDisplay(0, totalEstacionesActivas, '---');
        }
    }

    function onAnioChange() {
        const anio = document.getElementById('filterAnio').value;

        if (!anio || !selectedStation) {
            clearMonthSelect();
            showWaitingDataInCharts();
            return;
        }

        // Cargar meses para la estación, año y nivel actual
        loadMonthsFromDatabase();

        showWaitingDataInCharts();
    }

    // ===== REEMPLAZAR la función onMesChange =====
    function onMesChange() {
        const mes = document.getElementById('filterMes').value;

        if (!mes || !selectedStation) {
            showWaitingDataInCharts();
            return;
        }

        const anio = document.getElementById('filterAnio').value;

        if (!anio) {
            showWaitingDataInCharts();
            return;
        }

        console.log('📋 Filtros seleccionados:', {
            estacion: selectedStation,
            anio: anio,
            mes: mes,
            nivel: currentLevel
        });

        loadStationLevelStats();

        if (currentLevel === 'L0') {
            console.log('🎯 Cargando todas las gráficas L0...');
            loadChartVelocidadViento();
            loadChartMetricasRealTime();
            loadChartRosaVientos();
            loadChartUrbina1();
            loadChartUrbina2();
            loadChartHeatmap();
            loadChartRachas();

        } else if (currentLevel === 'L1') {
            console.log('🎯 Cargando todas las gráficas L1...');
            loadChartTemperaturaL1();
            loadChartHumedadL1();
            loadChartPresionL1();
            loadChartVientoTemperaturaL1();
            loadChartPresionAtmosfericaL1();
            loadChartBalanceEnergeticoL1();
            loadChartPuntoRocioL1();

        } else if (currentLevel === 'L2') {
            console.log('🎯 Cargando todas las gráficas L1...');
            loadChartTendenciaL2();
            loadChartClimogramaL2();


        }
    }

    // ===== FUNCIONES PARA CONECTAR CON LA BASE DE DATOS =====
    async function loadStationsFromDatabase() {
        try {
            console.log('🔄 Cargando estaciones desde la base de datos...');

            const response = await fetch('../controller/CDashboard.php?action=get_stations');

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }

            const data = await response.json();

            if (data.success) {
                availableStations = data.data;
                populateStationSelect();
                console.log('✅ Estaciones cargadas:', availableStations.length);
            } else {
                throw new Error(data.error || 'Error desconocido al cargar estaciones');
            }

        } catch (error) {
            console.error('❌ Error al cargar estaciones:', error);
        }
    }

    function populateStationSelect() {
        const select = document.getElementById('filterEstacion');
        select.innerHTML = '<option value="">Seleccione una estación...</option>';

        availableStations.forEach(estacion => {
            const option = document.createElement('option');
            option.value = estacion.id;
            option.textContent = `${estacion.nombre} (${estacion.codigo})`;
            select.appendChild(option);
        });
    }

    async function loadYearsFromDatabase() {
        if (!selectedStation) return;

        try {
            console.log('🔄 Cargando años desde la base de datos...');

            const response = await fetch(`../controller/CDashboard.php?action=get_years&id_estacion=${selectedStation}&nivel=${currentLevel}`);

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }

            const data = await response.json();

            if (data.success) {
                availableYears = data.data;
                populateYearSelect();
                console.log('✅ Años cargados:', availableYears.length);
            } else {
                throw new Error(data.error || 'Error desconocido al cargar años');
            }

        } catch (error) {
            console.error('❌ Error al cargar años:', error);
            clearYearSelect();
        }
    }

    function populateYearSelect() {
        const select = document.getElementById('filterAnio');
        select.innerHTML = '<option value="">Seleccione año...</option>';

        if (availableYears.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No hay años disponibles';
            option.className = 'disabled-option';
            select.appendChild(option);
            return;
        }

        availableYears.forEach(anio => {
            const option = document.createElement('option');
            option.value = anio;
            option.textContent = anio;
            select.appendChild(option);
        });
    }

    async function loadMonthsFromDatabase() {
        if (!selectedStation) return;

        const anio = document.getElementById('filterAnio').value;
        if (!anio) return;

        try {
            console.log('🔄 Cargando meses desde la base de datos...');

            const response = await fetch(`../controller/CDashboard.php?action=get_months&id_estacion=${selectedStation}&anio=${anio}&nivel=${currentLevel}`);

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }

            const data = await response.json();

            if (data.success) {
                availableMonths = data.data;
                populateMonthSelect();
                console.log('✅ Meses cargados:', availableMonths.length);
            } else {
                throw new Error(data.error || 'Error desconocido al cargar meses');
            }

        } catch (error) {
            console.error('❌ Error al cargar meses:', error);
            clearMonthSelect();
        }
    }

    function populateMonthSelect() {
        const select = document.getElementById('filterMes');
        select.innerHTML = '<option value="">Seleccione mes...</option>';

        if (availableMonths.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No hay meses disponibles';
            option.className = 'disabled-option';
            select.appendChild(option);
            return;
        }

        const optionTodos = document.createElement('option');
        optionTodos.value = 'all';
        optionTodos.textContent = 'Todos';
        optionTodos.style.fontWeight = '700';
        optionTodos.style.color = 'var(--primary-cyan)';
        select.appendChild(optionTodos);

        availableMonths.forEach(mes => {
            const option = document.createElement('option');
            option.value = mes.numero;
            option.textContent = `${mes.numero.toString().padStart(2, '0')} - ${mes.nombre}`;
            select.appendChild(option);
        });
    }

    async function loadStationInfo(stationId) {
        try {
            console.log('🔄 Cargando información de estación...');

            const response = await fetch(`../controller/CDashboard.php?action=get_station_info&id_estacion=${stationId}`);

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }

            const data = await response.json();

            if (data.success) {
                updateStationInfo(data.data);
                console.log('✅ Información de estación cargada');
            } else {
                throw new Error(data.error || 'Error desconocido al cargar información');
            }

        } catch (error) {
            console.error('❌ Error al cargar información de estación:', error);
        }
    }
    let carouselInterval = null;
    let currentSlide = 0;
    // ===== REEMPLAZAR ESTA FUNCIÓN COMPLETA =====
    function updateStationInfo(stationData) {
        if (!stationData) {
            // Limpiar información
            document.getElementById('infoCodigo').textContent = '---';
            document.getElementById('infoComunidad').textContent = '---';
            document.getElementById('infoAltura').textContent = '---';
            document.getElementById('infoEstado').textContent = '---';
            document.getElementById('infoFechaInsta').textContent = '---';

            stopCarousel();
            const mapContainer = document.getElementById('mapContainer');
            mapContainer.innerHTML = `
            <div class="map-placeholder" id="mapPlaceholder">
                <i class="fas fa-map-marked-alt"></i>
                <p>Seleccione una estación para ver el mapa</p>
            </div>
        `;
            return;
        }

        document.getElementById('infoCodigo').textContent = stationData.codigo || '---';
        document.getElementById('infoComunidad').textContent = stationData.comunidad || '---';
        document.getElementById('infoAltura').textContent = stationData.altura || '---';
        document.getElementById('infoEstado').textContent = stationData.estado || '---';
        document.getElementById('infoFechaInsta').textContent = stationData.fecha_instalacion || '---';

        const mapContainer = document.getElementById('mapContainer');
        const rutaFotografia = stationData.imagenes?.fotografia;
        const rutaMapa = stationData.imagenes?.mapa;

        const tieneFotografia = rutaFotografia && rutaFotografia !== 'default.png';
        const tieneMapa = rutaMapa && rutaMapa !== 'default.png';

        if (!tieneFotografia && !tieneMapa) {
            // No hay imágenes disponibles
            mapContainer.innerHTML = `
            <div class="map-placeholder">
                <i class="fas fa-image"></i>
                <p>Imágenes no disponibles para esta estación</p>
            </div>
        `;
            return;
        }

        let carouselHTML = '<div class="carousel-container">';

        // Slide 1: Fotografía
        if (tieneFotografia) {
            carouselHTML += `
            <div class="carousel-slide active" data-slide="0">
                <img src="../${rutaFotografia}" 
                     alt="Fotografía de ${stationData.nombre}" 
                     class="carousel-image"
                     onerror="this.style.display='none'">
            </div>
        `;
        }

        // Slide 2: Mapa
        if (tieneMapa) {
            carouselHTML += `
            <div class="carousel-slide ${!tieneFotografia ? 'active' : ''}" data-slide="${tieneFotografia ? '1' : '0'}">
                <img src="../${rutaMapa}" 
                     alt="Mapa de ${stationData.nombre}" 
                     class="carousel-image"
                     onerror="this.style.display='none'">
            </div>
        `;
        }

        carouselHTML += '</div>';

        mapContainer.innerHTML = carouselHTML;

        if (tieneFotografia && tieneMapa) {
            startCarousel();
        }
    }


    // ================================================================
    // GRÁFICA URBINA TIPO 1: VELOCIDAD POR MINUTO (Chart.js)
    // ================================================================
    async function loadChartUrbina1() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros para Urbina 1');
            return;
        }

        try {
            console.log('🚀 Cargando Urbina Tipo 1 con Plotly...');

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_urbina_hora&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            if (!result.success) {
                console.error('❌ Error del servidor:', result.error || 'Sin mensaje');
                mostrarMensajeSinDatos('chart4');
                return;
            }

            if (result.data.total_horas === 0) {
                console.log('⚠️ Sin datos para Urbina 1');
                mostrarMensajeSinDatos('chart4');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);

            // Remover overlay de espera
            removeWaitingOverlay('chart4');

            // Destruir gráfica Plotly anterior si existe
            if (charts['chart4']) {
                try {
                    Plotly.purge('chart4');
                } catch (e) {
                    console.log('No hay gráfica Plotly anterior');
                }
                delete charts['chart4'];
            }

            const chartDiv = document.getElementById('chart4');
            if (!chartDiv) {
                console.error('❌ No se encontró el elemento #chart4');
                return;
            }

            // ===== CALCULAR RANGOS PARA EVITAR SALIRSE =====
            const velocidadesMax = Math.max(...datos.vel_promedio, ...datos.vel_maxima);
            const velocidadesMin = Math.min(...datos.vel_promedio);
            const rangoY = velocidadesMax - velocidadesMin;

            const yMin = Math.max(0, velocidadesMin - rangoY * 0.1);
            const yMax = velocidadesMax + rangoY * 0.1;
            const xMin = 0;
            const xMax = datos.etiquetas.length - 1;

            // ===== CONFIGURACIÓN DE DATOS =====

            // Trace 1: Barras - Velocidad Promedio
            const traceVelocidad = {
                name: 'Velocidad Promedio (m/s)',
                x: datos.etiquetas,
                y: datos.vel_promedio,
                type: 'bar',
                marker: {
                    color: datos.vel_promedio.map((vel) => {
                        if (vel < 2) return 'rgba(0, 255, 136, 0.8)';
                        if (vel < 4) return 'rgba(0, 255, 255, 0.8)';
                        if (vel < 6) return 'rgba(255, 214, 10, 0.8)';
                        if (vel < 8) return 'rgba(255, 149, 0, 0.8)';
                        return 'rgba(255, 7, 58, 0.8)';
                    }),
                    line: {
                        color: '#00ffff',
                        width: 1
                    }
                },
                hovertemplate: '<b>%{x}</b><br>Velocidad: %{y:.2f} m/s<extra></extra>',
                yaxis: 'y'
            };

            // Trace 2: Línea - Dirección del Viento
            const traceDireccion = {
                name: 'Dirección Predominante (°)',
                x: datos.etiquetas,
                y: datos.dir_promedio,
                type: 'scatter',
                mode: 'lines+markers',
                line: {
                    color: 'rgb(255, 7, 58)',
                    width: 3,
                    shape: 'spline'
                },
                marker: {
                    size: 6,
                    color: '#ff073a',
                    line: {
                        color: '#ffffff',
                        width: 2
                    }
                },
                hovertemplate: '<b>%{x}</b><br>Dirección: %{y:.1f}°<extra></extra>',
                yaxis: 'y2'
            };

            const data = [traceVelocidad, traceDireccion];

            // ===== LAYOUT DE LA GRÁFICA (IGUAL A SERIE TEMPORAL) =====
            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.2)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 11,
                    color: 'rgba(255, 255, 255, 0.8)'
                },
                margin: {
                    l: 60,
                    r: 60,
                    t: 40,
                    b: 70
                },

                // Eje X
                xaxis: {
                    title: '',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.05)',
                    zeroline: false,
                    tickangle: -45,
                    tickfont: {
                        size: 9,
                        color: 'rgba(255, 255, 255, 0.6)'
                    },
                    range: [0, Math.min(23, xMax)],
                    fixedrange: false,
                    rangemode: 'normal',
                    autorange: false
                },

                // Eje Y1: Velocidad (izquierda)
                yaxis: {
                    title: 'Velocidad (m/s)',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.08)',
                    zeroline: true,
                    zerolinecolor: 'rgba(255, 255, 255, 0.1)',
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    range: [yMin, yMax],
                    fixedrange: true,
                    autorange: false,
                    side: 'left'
                },

                // Eje Y2: Dirección (derecha)
                yaxis2: {
                    title: 'Dirección (grados)',
                    showgrid: false,
                    zeroline: false,
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    overlaying: 'y',
                    side: 'right',
                    range: [0, 360],
                    fixedrange: true
                },

                // Leyenda BIEN POSICIONADA (arriba a la izquierda, dentro del área)
                legend: {
                    orientation: 'v',
                    x: 0.02,
                    xanchor: 'left',
                    y: 0.98,
                    yanchor: 'top',
                    font: {
                        size: 9,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.7)',
                    bordercolor: 'rgba(0, 255, 255, 0.3)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 30
                },

                hovermode: 'x unified',
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(0, 255, 255, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                },
                dragmode: 'pan'
            };

            // ===== CONFIGURACIÓN (SOLO 2 BOTONES: HOME Y DOWNLOAD) =====
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                scrollZoom: true,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'resetScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d'
                ],
                modeBarButtonsToAdd: [
                    {
                        name: '🏠 Resetear Vista',
                        icon: Plotly.Icons.home,
                        click: function (gd) {
                            Plotly.relayout(gd, {
                                'xaxis.range': [0, Math.min(23, xMax)],
                                'yaxis.range': [yMin, yMax],
                                'yaxis2.range': [0, 360]
                            });
                        }
                    }
                ],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `urbina_hora_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA CON NOTIFICACIÓN PERSONALIZADA =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chart4 .modebar-btn[data-title*="Download"]');
                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chart4')) {
                    downloadBtn.setAttribute('data-custom-handler-chart4', 'true');

                    downloadBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();
                        showCustomDownloadNotification(`urbina_hora_${estacion}_${anio}_${mes}.png`);
                    });
                }
            });

            // ===== LIMITAR RANGO DEL EJE X (NO SALIRSE) =====
            chartDiv.on('plotly_relayout', function (eventdata) {
                if (eventdata['xaxis.range[0]'] !== undefined) {
                    let newMin = eventdata['xaxis.range[0]'];
                    let newMax = eventdata['xaxis.range[1]'];

                    if (newMin < xMin) {
                        const diff = newMax - newMin;
                        newMin = xMin;
                        newMax = xMin + diff;
                    }
                    if (newMax > xMax) {
                        const diff = newMax - newMin;
                        newMax = xMax;
                        newMin = xMax - diff;
                    }

                    if (newMin !== eventdata['xaxis.range[0]'] || newMax !== eventdata['xaxis.range[1]']) {
                        Plotly.relayout(chartDiv, {
                            'xaxis.range': [newMin, newMax]
                        });
                    }
                }
            });

            charts['chart4'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Urbina Tipo 1 creada con Plotly (por hora)');

        } catch (error) {
            console.error('❌ Error en Urbina 1:', error);
            mostrarMensajeError('chart4', 'Error al cargar Urbina 1: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA URBINA TIPO 2: VELOCIDAD Y FRECUENCIA POR SECTOR
    // ================================================================
    async function loadChartUrbina2() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros para Urbina 2');
            return;
        }

        try {
            console.log('🚀 Cargando Urbina Tipo 2 con Plotly...');

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_urbina_sector&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            if (!result.success) {
                console.error('❌ Error del servidor:', result.error || 'Sin mensaje');
                mostrarMensajeSinDatos('chart5');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);

            // Remover overlay de espera
            removeWaitingOverlay('chart5');

            // Destruir gráfica Plotly anterior si existe
            if (charts['chart5']) {
                try {
                    Plotly.purge('chart5');
                } catch (e) {
                    console.log('No hay gráfica Plotly anterior');
                }
                delete charts['chart5'];
            }

            const chartDiv = document.getElementById('chart5');
            if (!chartDiv) {
                console.error('❌ No se encontró el elemento #chart5');
                return;
            }

            // Colores vibrantes por sector cardinal
            const coloresSectores = {
                'N': 'rgba(0, 255, 255, 0.8)',      // Cyan
                'NE': 'rgba(0, 255, 136, 0.8)',     // Verde
                'E': 'rgba(255, 214, 10, 0.8)',     // Amarillo
                'SE': 'rgba(255, 149, 0, 0.8)',     // Naranja
                'S': 'rgba(255, 7, 58, 0.8)',       // Rojo
                'SW': 'rgba(255, 0, 110, 0.8)',     // Rosa
                'W': 'rgba(157, 78, 221, 0.8)',     // Morado
                'NW': 'rgba(0, 150, 255, 0.8)'      // Azul
            };

            // ===== TRACE 1: BARRAS - FRECUENCIA =====
            const traceFrecuencia = {
                name: 'Frecuencia de Ocurrencias',
                x: datos.sectores,
                y: datos.frecuencias,
                type: 'bar',
                marker: {
                    color: datos.sectores.map(sector => coloresSectores[sector]),
                    line: {
                        color: '#ffffff',
                        width: 2
                    }
                },
                hovertemplate: '<b>Sector %{x}</b><br>Frecuencia: %{y} ocurrencias<extra></extra>',
                yaxis: 'y'
            };

            // ===== TRACE 2: LÍNEA - VELOCIDAD =====
            const traceVelocidad = {
                name: 'Velocidad Promedio (m/s)',
                x: datos.sectores,
                y: datos.velocidades,
                type: 'scatter',
                mode: 'lines+markers',
                line: {
                    color: 'rgb(255, 214, 10)',
                    width: 4,
                    shape: 'spline'
                },
                marker: {
                    size: 10,
                    color: '#ffd60a',
                    line: {
                        color: '#ffffff',
                        width: 2
                    },
                    symbol: 'diamond'
                },
                hovertemplate: '<b>Sector %{x}</b><br>Velocidad: %{y:.2f} m/s<extra></extra>',
                yaxis: 'y2'
            };

            const data = [traceFrecuencia, traceVelocidad];

            // ===== LAYOUT MEJORADO - LEYENDA ABAJO A LA DERECHA =====
            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.2)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 11,
                    color: 'rgba(255, 255, 255, 0.8)'
                },
                margin: {
                    l: 60,      // Izquierda
                    r: 60,      // Derecha
                    t: 20,      // ✅ Arriba - Reducido para dar espacio
                    b: 100      // ✅ Abajo - Aumentado para la leyenda
                },

                // Eje X: Sectores cardinales
                xaxis: {
                    title: '',
                    showgrid: false,
                    zeroline: false,
                    tickfont: {
                        size: 12,
                        color: '#ffffff',
                        weight: 'bold'
                    },
                    categoryorder: 'array',
                    categoryarray: ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'],
                    fixedrange: true
                },

                // Eje Y1: Frecuencia (izquierda)
                yaxis: {
                    title: 'Frecuencia',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.08)',
                    zeroline: true,
                    zerolinecolor: 'rgba(255, 255, 255, 0.1)',
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    side: 'left',
                    fixedrange: true
                },

                // Eje Y2: Velocidad (derecha)
                yaxis2: {
                    title: 'Velocidad (m/s)',
                    showgrid: false,
                    zeroline: false,
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    overlaying: 'y',
                    side: 'right',
                    fixedrange: true
                },

                // ✅ LEYENDA MOVIDA ABAJO A LA DERECHA (FUERA DEL ÁREA DE GRÁFICA)
                legend: {
                    orientation: 'v',       // Vertical
                    x: 0.98,               // Pegada a la derecha
                    xanchor: 'right',
                    y: -0.25,              // ✅ Debajo de la gráfica (valor negativo)
                    yanchor: 'top',        // Anclada desde arriba
                    font: {
                        size: 9,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.85)',
                    bordercolor: 'rgba(0, 255, 255, 0.3)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 30
                },

                hovermode: 'x unified',
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(0, 255, 255, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                }
            };

            // ✅ CONFIGURACIÓN: SOLO BOTÓN DE DESCARGA (SIN HOME)
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d',
                    'resetScale2d',
                    'hoverClosestCartesian',
                    'hoverCompareCartesian'
                ],
                modeBarButtonsToAdd: [],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `urbina_sector_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA CON NOTIFICACIÓN =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chart5 .modebar-btn[data-title*="Download"]');
                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chart5')) {
                    downloadBtn.setAttribute('data-custom-handler-chart5', 'true');

                    downloadBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();
                        showCustomDownloadNotification(`urbina_sector_${estacion}_${anio}_${mes}.png`);
                    });
                }
            });

            charts['chart5'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Urbina Tipo 2 creada con leyenda abajo-derecha (sin tapar barras)');

        } catch (error) {
            console.error('❌ Error en Urbina 2:', error);
            mostrarMensajeError('chart5', 'Error al cargar Urbina 2: ' + error.message);
        }
    }


    // ================================================================
    // GRÁFICA HEATMAP: MAPA DE CALOR DE VELOCIDAD DEL VIENTO
    // ================================================================
    async function loadChartHeatmap() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros para Heatmap');
            return;
        }

        try {
            console.log('🚀 Cargando Heatmap con Plotly...');

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_heatmap_velocidad&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            if (!result.success) {
                console.error('❌ Error del servidor:', result.error || 'Sin mensaje');
                mostrarMensajeSinDatos('chart6');
                return;
            }

            if (result.data.total_puntos === 0) {
                console.log('⚠️ Sin datos para Heatmap');
                mostrarMensajeSinDatos('chart6');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);
            console.log('🔍 Modo:', datos.modo);

            // Remover overlay de espera
            removeWaitingOverlay('chart6');

            // Destruir gráfica Plotly anterior si existe
            if (charts['chart6']) {
                try {
                    Plotly.purge('chart6');
                } catch (e) {
                    console.log('No hay gráfica Plotly anterior');
                }
                delete charts['chart6'];
            }

            const chartDiv = document.getElementById('chart6');
            if (!chartDiv) {
                console.error('❌ No se encontró el elemento #chart6');
                return;
            }

            // ===== PREPARAR DATOS PARA HEATMAP =====

            // Transponer matriz para Plotly (necesita [horas][períodos])
            const matrizTranspuesta = [];
            for (let h = 0; h < 24; h++) {
                const fila = [];
                for (let p = 0; p < datos.matriz_velocidades.length; p++) {
                    const valor = datos.matriz_velocidades[p][h];
                    fila.push(valor !== null ? valor : null);
                }
                matrizTranspuesta.push(fila);
            }

            // ===== CALCULAR RANGOS PARA EVITAR SALIRSE =====
            const xMin = 0;
            const xMax = datos.etiquetas_periodo.length - 1;
            const yMin = 0;
            const yMax = 23;

            // Definir rango inicial según el modo
            const rangoInicialX = datos.modo === 'anual'
                ? [0, 11]  // Mostrar los 12 meses completos
                : [0, Math.min(30, xMax)]; // Mostrar hasta 31 días

            // ===== CONFIGURACIÓN DEL HEATMAP =====
            const traceHeatmap = {
                type: 'heatmap',
                z: matrizTranspuesta,
                x: datos.etiquetas_periodo,
                y: datos.etiquetas_horas,
                colorscale: [
                    [0, 'rgb(0, 50, 100)'],        // Azul oscuro (calma)
                    [0.2, 'rgb(0, 150, 255)'],     // Azul (brisa)
                    [0.4, 'rgb(0, 255, 255)'],     // Cyan (moderado)
                    [0.6, 'rgb(0, 255, 136)'],     // Verde (fresco)
                    [0.75, 'rgb(255, 214, 10)'],   // Amarillo (fuerte)
                    [0.85, 'rgb(255, 149, 0)'],    // Naranja (muy fuerte)
                    [1, 'rgb(255, 7, 58)']         // Rojo (extremo)
                ],
                colorbar: {
                    title: {
                        text: 'Velocidad<br>(m/s)',
                        side: 'right',
                        font: {
                            size: 11,
                            color: '#ffffff'
                        }
                    },
                    thickness: 20,
                    len: 0.8,
                    x: 1.02,
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.8)'
                    },
                    tickcolor: 'rgba(255, 255, 255, 0.3)',
                    outlinecolor: 'rgba(0, 255, 255, 0.3)',
                    outlinewidth: 1
                },
                hovertemplate: '<b>%{x}</b><br>Hora: %{y}<br>Velocidad: %{z:.2f} m/s<extra></extra>',
                zsmooth: 'best',
                showscale: true
            };

            const data = [traceHeatmap];

            // ===== TÍTULOS DINÁMICOS SEGÚN MODO =====
            const tituloEjeX = datos.modo === 'anual' ? 'Mes del Año' : 'Día del Mes';

            // ===== LAYOUT PROFESIONAL (BASADO EN SERIE TEMPORAL) =====
            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.2)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 11,
                    color: 'rgba(255, 255, 255, 0.8)'
                },
                margin: {
                    l: 70,
                    r: 100,
                    t: 40,
                    b: 80
                },

                // Eje X: Días o Meses según el modo
                xaxis: {
                    title: tituloEjeX,
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.05)',
                    zeroline: false,
                    tickangle: -45,
                    tickfont: {
                        size: 9,
                        color: 'rgba(255, 255, 255, 0.6)'
                    },
                    range: rangoInicialX,
                    fixedrange: false,
                    rangemode: 'normal',
                    autorange: false
                },

                // Eje Y: Horas del día
                yaxis: {
                    title: 'Hora del Día',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.08)',
                    zeroline: true,
                    zerolinecolor: 'rgba(255, 255, 255, 0.1)',
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    range: [yMin, yMax],
                    fixedrange: true,
                    autorange: false
                },

                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(0, 255, 255, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                },
                dragmode: 'pan'
            };

            // ===== CONFIGURACIÓN (SOLO HOME + DOWNLOAD, SIN PAN) =====
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                scrollZoom: true,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'resetScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d'
                ],
                modeBarButtonsToAdd: [
                    {
                        name: '🏠 Resetear Vista',
                        icon: Plotly.Icons.home,
                        click: function (gd) {
                            Plotly.relayout(gd, {
                                'xaxis.range': rangoInicialX,
                                'yaxis.range': [yMin, yMax]
                            });
                        }
                    }
                ],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `heatmap_viento_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA CON NOTIFICACIÓN =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chart6 .modebar-btn[data-title*="Download"]');
                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chart6')) {
                    downloadBtn.setAttribute('data-custom-handler-chart6', 'true');

                    downloadBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();
                        showCustomDownloadNotification(`heatmap_viento_${estacion}_${anio}_${mes}.png`);
                    });
                }
            });

            // ===== LIMITAR RANGO DEL EJE X (NO SALIRSE) =====
            chartDiv.on('plotly_relayout', function (eventdata) {
                if (eventdata['xaxis.range[0]'] !== undefined) {
                    let newMin = eventdata['xaxis.range[0]'];
                    let newMax = eventdata['xaxis.range[1]'];

                    if (newMin < xMin) {
                        const diff = newMax - newMin;
                        newMin = xMin;
                        newMax = xMin + diff;
                    }
                    if (newMax > xMax) {
                        const diff = newMax - newMin;
                        newMax = xMax;
                        newMin = xMax - diff;
                    }

                    if (newMin !== eventdata['xaxis.range[0]'] || newMax !== eventdata['xaxis.range[1]']) {
                        Plotly.relayout(chartDiv, {
                            'xaxis.range': [newMin, newMax]
                        });
                    }
                }
            });

            charts['chart6'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Heatmap creada con Plotly (modo: ' + datos.modo + ')');

        } catch (error) {
            console.error('❌ Error en Heatmap:', error);
            mostrarMensajeError('chart6', 'Error al cargar Heatmap: ' + error.message);
        }
    }


    // ================================================================
    // GRÁFICA DETECCIÓN DE RACHAS: IDENTIFICACIÓN DE EVENTOS EXTREMOS
    // ================================================================
    async function loadChartRachas() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros para Detección de Rachas');
            return;
        }

        try {
            console.log('🚀 Cargando Detección de Rachas con Plotly...');

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_rachas_viento&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            if (!result.success) {
                console.error('❌ Error del servidor:', result.error || 'Sin mensaje');
                mostrarMensajeSinDatos('chart7');
                return;
            }

            if (result.data.total_registros === 0) {
                console.log('⚠️ Sin datos para Detección de Rachas');
                mostrarMensajeSinDatos('chart7');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);

            // Remover overlay de espera
            removeWaitingOverlay('chart7');

            // Destruir gráfica Plotly anterior
            if (charts['chart7']) {
                try {
                    Plotly.purge('chart7');
                } catch (e) {
                    console.log('No hay gráfica anterior');
                }
                delete charts['chart7'];
            }

            const chartDiv = document.getElementById('chart7');
            if (!chartDiv) {
                console.error('❌ No se encontró #chart7');
                return;
            }

            // ===== CALCULAR RANGOS =====
            const velocidadesMax = Math.max(...datos.velocidades);
            const velocidadesMin = Math.min(...datos.velocidades);
            const rangoY = velocidadesMax - velocidadesMin;

            const yMin = Math.max(0, velocidadesMin - rangoY * 0.1);
            const yMax = velocidadesMax + rangoY * 0.1;
            const xMin = 0;
            const xMax = datos.labels.length - 1;

            // ===== RANGO INICIAL SEGÚN MODO =====
            let rangoInicialX;
            if (mes === 'all') {
                // Año completo: mostrar primeros 30 días
                rangoInicialX = [0, Math.min(xMax, 720)]; // 30 días × 24 horas
            } else {
                // Mes específico: mostrar primeros 7 días
                rangoInicialX = [0, Math.min(xMax, 167)]; // 7 días × 24 horas
            }

            // ===== TRACES =====

            // 1. Línea base con relleno
            const traceBase = {
                name: 'Velocidad del Viento',
                x: datos.labels,
                y: datos.velocidades,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgba(0, 255, 255, 0.6)',
                    width: 2
                },
                fill: 'tozeroy',
                fillcolor: 'rgba(0, 255, 255, 0.1)',
                hovertemplate: '<b>%{x}</b><br>Velocidad: %{y:.2f} m/s<extra></extra>',
                showlegend: true
            };

            // 2. Línea del umbral P75
            const traceUmbral = {
                name: `Umbral P75: ${datos.umbral_p75} m/s`,
                x: [datos.labels[0], datos.labels[datos.labels.length - 1]],
                y: [datos.umbral_p75, datos.umbral_p75],
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(255, 149, 0)',
                    width: 3,
                    dash: 'dash'
                },
                hovertemplate: '<b>Umbral P75</b><br>%{y:.2f} m/s<extra></extra>',
                showlegend: true
            };

            // 3. Rachas detectadas
            const traceRachas = {
                name: `${datos.total_rachas} Rachas`,
                x: datos.labels_rachas,
                y: datos.velocidades_rachas,
                type: 'scatter',
                mode: 'markers',
                marker: {
                    color: 'rgb(255, 7, 58)',
                    size: 12,
                    symbol: 'star',
                    line: {
                        color: '#ffffff',
                        width: 2
                    }
                },
                hovertemplate: '<b>RACHA</b><br>%{x}<br>%{y:.2f} m/s<extra></extra>',
                showlegend: true
            };

            const data = [traceBase, traceUmbral, traceRachas];

            // ===== LAYOUT (SIN ANOTACIONES) =====
            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.2)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 11,
                    color: 'rgba(255, 255, 255, 0.8)'
                },
                margin: {
                    l: 60,
                    r: 40,
                    t: 40,
                    b: 70
                },

                // Eje X
                xaxis: {
                    title: '',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.05)',
                    zeroline: false,
                    tickangle: -45,
                    tickfont: {
                        size: 9,
                        color: 'rgba(255, 255, 255, 0.6)'
                    },
                    range: rangoInicialX,
                    fixedrange: false,
                    rangemode: 'normal',
                    autorange: false
                },

                // Eje Y
                yaxis: {
                    title: 'Velocidad (m/s)',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.08)',
                    zeroline: true,
                    zerolinecolor: 'rgba(255, 255, 255, 0.1)',
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    range: [yMin, yMax],
                    fixedrange: true,
                    autorange: false
                },

                // Leyenda
                legend: {
                    orientation: 'v',
                    x: 0.02,
                    xanchor: 'left',
                    y: 0.98,
                    yanchor: 'top',
                    font: {
                        size: 9,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.7)',
                    bordercolor: 'rgba(0, 255, 255, 0.3)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 30
                },

                hovermode: 'x unified',
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(0, 255, 255, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                },
                dragmode: 'pan'
            };

            // ===== CONFIG (IGUAL A SERIE TEMPORAL) =====
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                scrollZoom: true,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'resetScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d'
                ],
                modeBarButtonsToAdd: [
                    {
                        name: '🏠 Resetear Vista',
                        icon: Plotly.Icons.home,
                        click: function (gd) {
                            Plotly.relayout(gd, {
                                'xaxis.range': rangoInicialX,
                                'yaxis.range': [yMin, yMax]
                            });
                        }
                    }
                ],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `rachas_viento_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chart7 .modebar-btn[data-title*="Download"]');
                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chart7')) {
                    downloadBtn.setAttribute('data-custom-handler-chart7', 'true');

                    downloadBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();
                        showCustomDownloadNotification(`rachas_viento_${estacion}_${anio}_${mes}.png`);
                    });
                }
            });

            // ===== LIMITAR RANGO DEL EJE X =====
            chartDiv.on('plotly_relayout', function (eventdata) {
                if (eventdata['xaxis.range[0]'] !== undefined) {
                    let newMin = eventdata['xaxis.range[0]'];
                    let newMax = eventdata['xaxis.range[1]'];

                    if (newMin < xMin) {
                        const diff = newMax - newMin;
                        newMin = xMin;
                        newMax = xMin + diff;
                    }
                    if (newMax > xMax) {
                        const diff = newMax - newMin;
                        newMax = xMax;
                        newMin = xMax - diff;
                    }

                    if (newMin !== eventdata['xaxis.range[0]'] || newMax !== eventdata['xaxis.range[1]']) {
                        Plotly.relayout(chartDiv, {
                            'xaxis.range': [newMin, newMax]
                        });
                    }
                }
            });

            charts['chart7'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Rachas creada (sin anotaciones)');

        } catch (error) {
            console.error('❌ Error en Rachas:', error);
            mostrarMensajeError('chart7', 'Error: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA L1-1: SERIE TEMPORAL - TEMPERATURA DEL AIRE
    // ================================================================
    async function loadChartTemperaturaL1() {
        console.log('🚀 Iniciando carga de gráfica Temperatura L1 con Plotly...');

        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        console.log('📋 Filtros:', { estacion, anio, mes });

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros, mostrando mensaje');
            mostrarMensajeSinDatos('chartTempL1');
            return;
        }

        try {
            if (typeof Plotly === 'undefined') {
                console.error('❌ ERROR: Plotly.js NO está cargado');
                mostrarMensajeError('chartTempL1', 'Error: Librería de gráficas no disponible');
                return;
            }

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l1_temperatura&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            if (!result.success || result.data.total_horas === 0) {
                console.log('⚠️ Sin datos para Temperatura L1');
                mostrarMensajeSinDatos('chartTempL1');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);

            // Remover overlay de espera
            removeWaitingOverlay('chartTempL1');

            // Destruir gráfica Plotly anterior si existe
            if (charts['chartTempL1']) {
                try {
                    Plotly.purge('chartTempL1');
                } catch (e) {
                    console.log('⚠️ No se pudo destruir gráfica anterior:', e);
                }
                delete charts['chartTempL1'];
            }

            const chartDiv = document.getElementById('chartTempL1');
            if (!chartDiv) {
                console.error('❌ ERROR: No se encontró el elemento #chartTempL1');
                return;
            }

            // ===== CALCULAR RANGOS PARA EVITAR SALIRSE =====
            const temperaturaMax = Math.max(...datos.temp_promedio, ...datos.temp_maxima);
            const temperaturaMin = Math.min(...datos.temp_promedio, ...datos.temp_minima);
            const rangoY = temperaturaMax - temperaturaMin;

            const yMin = temperaturaMin - rangoY * 0.1;
            const yMax = temperaturaMax + rangoY * 0.1;
            const xMin = 0;
            const xMax = datos.labels.length - 1;

            // ===== CONFIGURACIÓN DE DATOS =====

            // Trace 1: Temperatura Promedio
            const trace1 = {
                name: 'Temperatura Promedio',
                x: datos.labels,
                y: datos.temp_promedio,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(255, 149, 0)',
                    width: 2.5
                },
                fill: 'tozeroy',
                fillcolor: 'rgba(255, 149, 0, 0.2)',
                hovertemplate: '<b>%{x}</b><br>Temperatura: %{y:.2f} °C<extra></extra>'
            };

            // Trace 2: Temperatura Máxima
            const trace2 = {
                name: 'Temperatura Máxima',
                x: datos.labels,
                y: datos.temp_maxima,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(255, 7, 58)',
                    width: 1.5,
                    dash: 'dot'
                },
                hovertemplate: '<b>%{x}</b><br>Máxima: %{y:.2f} °C<extra></extra>'
            };

            // Trace 3: Temperatura Mínima
            const trace3 = {
                name: 'Temperatura Mínima',
                x: datos.labels,
                y: datos.temp_minima,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(0, 150, 255)',
                    width: 1.5,
                    dash: 'dot'
                },
                hovertemplate: '<b>%{x}</b><br>Mínima: %{y:.2f} °C<extra></extra>'
            };

            const data = [trace1, trace2, trace3];

            // ===== LAYOUT PROFESIONAL =====
            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.2)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 11,
                    color: 'rgba(255, 255, 255, 0.8)'
                },
                margin: {
                    l: 60,
                    r: 40,
                    t: 40,
                    b: 70
                },

                // Eje X
                xaxis: {
                    title: '',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.05)',
                    zeroline: false,
                    tickangle: -45,
                    tickfont: {
                        size: 9,
                        color: 'rgba(255, 255, 255, 0.6)'
                    },
                    range: [0, Math.min(23, xMax)],
                    fixedrange: false,
                    rangemode: 'normal',
                    autorange: false
                },

                // Eje Y
                yaxis: {
                    title: 'Temperatura (°C)',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.08)',
                    zeroline: true,
                    zerolinecolor: 'rgba(255, 255, 255, 0.1)',
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    range: [yMin, yMax],
                    fixedrange: true,
                    autorange: false
                },

                // Leyenda
                legend: {
                    orientation: 'v',
                    x: 0.02,
                    xanchor: 'left',
                    y: 0.98,
                    yanchor: 'top',
                    font: {
                        size: 9,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.7)',
                    bordercolor: 'rgba(255, 149, 0, 0.3)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 30
                },

                hovermode: 'x unified',
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(255, 149, 0, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                },
                dragmode: 'pan'
            };

            // ===== CONFIGURACIÓN =====
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                scrollZoom: true,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'resetScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d'
                ],
                modeBarButtonsToAdd: [
                    {
                        name: '🏠 Resetear Vista',
                        icon: Plotly.Icons.home,
                        click: function (gd) {
                            Plotly.relayout(gd, {
                                'xaxis.range': [0, Math.min(23, xMax)],
                                'yaxis.range': [yMin, yMax]
                            });
                        }
                    }
                ],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `temperatura_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA CON NOTIFICACIÓN PERSONALIZADA =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chartTempL1 .modebar-btn[data-title*="Download"]');

                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chartTempL1')) {
                    downloadBtn.setAttribute('data-custom-handler-chartTempL1', 'true');

                    downloadBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();
                        showCustomDownloadNotification(`temperatura_${estacion}_${anio}_${mes}.png`);
                    });
                }
            });

            // ===== LIMITAR RANGO DEL EJE X (NO SALIRSE) =====
            chartDiv.on('plotly_relayout', function (eventdata) {
                if (eventdata['xaxis.range[0]'] !== undefined) {
                    let newMin = eventdata['xaxis.range[0]'];
                    let newMax = eventdata['xaxis.range[1]'];

                    if (newMin < xMin) {
                        const diff = newMax - newMin;
                        newMin = xMin;
                        newMax = xMin + diff;
                    }
                    if (newMax > xMax) {
                        const diff = newMax - newMin;
                        newMax = xMax;
                        newMin = xMax - diff;
                    }

                    if (newMin !== eventdata['xaxis.range[0]'] || newMax !== eventdata['xaxis.range[1]']) {
                        Plotly.relayout(chartDiv, {
                            'xaxis.range': [newMin, newMax]
                        });
                    }
                }
            });

            // Guardar referencia
            charts['chartTempL1'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Temperatura L1 creada con Plotly');

        } catch (error) {
            console.error('❌ Error en Temperatura L1:', error);
            mostrarMensajeError('chartTempL1', 'Error al cargar temperatura: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA L1-2: DISTRIBUCIÓN DE HUMEDAD RELATIVA (RADAR/POLAR)
    // ================================================================
    async function loadChartHumedadL1() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros para Humedad L1');
            return;
        }

        try {
            console.log('🚀 Cargando Humedad L1 con Plotly...');

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l1_humedad&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            if (!result.success || result.data.total_rangos === 0) {
                console.log('⚠️ Sin datos para Humedad L1');
                mostrarMensajeSinDatos('chartHumL1');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);

            // Remover overlay de espera
            removeWaitingOverlay('chartHumL1');

            // Destruir gráfica Plotly anterior si existe
            if (charts['chartHumL1']) {
                try {
                    Plotly.purge('chartHumL1');
                } catch (e) {
                    console.log('No hay gráfica Plotly anterior');
                }
                delete charts['chartHumL1'];
            }

            const chartDiv = document.getElementById('chartHumL1');
            if (!chartDiv) {
                console.error('❌ No se encontró el elemento #chartHumL1');
                return;
            }

            // Colores según nivel de humedad
            const coloresHumedad = [
                'rgba(255, 7, 58, 0.8)',      // Muy Seca - Rojo
                'rgba(255, 149, 0, 0.8)',     // Seca - Naranja
                'rgba(255, 214, 10, 0.8)',    // Normal - Amarillo
                'rgba(0, 255, 136, 0.8)',     // Húmeda - Verde
                'rgba(0, 150, 255, 0.8)'      // Muy Húmeda - Azul
            ];

            // ===== TRACE 1: FRECUENCIAS =====
            const traceFrecuencia = {
                type: 'scatterpolar',
                name: 'Frecuencia de Ocurrencias',
                r: datos.frecuencias,
                theta: datos.rangos,
                fill: 'toself',
                fillcolor: 'rgba(0, 255, 255, 0.25)',
                line: {
                    color: 'rgb(0, 255, 255)',
                    width: 2.5
                },
                marker: {
                    size: 6,
                    color: coloresHumedad
                },
                hovertemplate: '<b>%{theta}</b><br>Frecuencia: %{r}<extra></extra>'
            };

            // ===== TRACE 2: HUMEDAD PROMEDIO =====
            const traceHumedad = {
                type: 'scatterpolar',
                name: 'Humedad Promedio (%)',
                r: datos.humedades_promedio,
                theta: datos.rangos,
                fill: 'toself',
                fillcolor: 'rgba(255, 149, 0, 0.15)',
                line: {
                    color: 'rgb(255, 149, 0)',
                    width: 2,
                    dash: 'dot'
                },
                marker: {
                    size: 4,
                    color: '#ff9500'
                },
                hovertemplate: '<b>%{theta}</b><br>Humedad: %{r:.2f}%<extra></extra>'
            };

            const data = [traceFrecuencia, traceHumedad];

            // ===== LAYOUT =====
            const layout = {
                polar: {
                    bgcolor: 'rgba(0, 0, 0, 0.2)',
                    radialaxis: {
                        visible: true,
                        gridcolor: 'rgba(255, 255, 255, 0.2)',
                        tickfont: {
                            size: 9,
                            color: 'rgba(255, 255, 255, 0.5)'
                        }
                    },
                    angularaxis: {
                        tickfont: {
                            size: 10,
                            color: '#00ffff'
                        },
                        gridcolor: 'rgba(255, 255, 255, 0.25)'
                    }
                },
                plot_bgcolor: 'transparent',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    color: '#ffffff'
                },
                margin: {
                    l: 60,
                    r: 60,
                    t: 40,
                    b: 80
                },
                legend: {
                    orientation: 'h',
                    x: 0.5,
                    xanchor: 'center',
                    y: -0.20,
                    yanchor: 'top',
                    font: {
                        size: 7.5,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.85)',
                    bordercolor: 'rgba(0, 255, 255, 0.4)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 20,
                    tracegroupgap: 3
                },
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(0, 255, 255, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                }
            };

            // ===== CONFIGURACIÓN =====
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'zoom2d',
                    'pan2d',
                    'select2d',
                    'lasso2d',
                    'zoomIn2d',
                    'zoomOut2d',
                    'autoScale2d',
                    'resetScale2d',
                    'hoverClosestCartesian',
                    'hoverCompareCartesian',
                    'toggleSpikelines'
                ],
                modeBarButtonsToAdd: [],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `humedad_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                }
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA CON LEYENDA AMPLIADA =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chartHumL1 .modebar-btn[data-title*="Download"]');

                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chartHumL1')) {
                    downloadBtn.setAttribute('data-custom-handler-chartHumL1', 'true');

                    downloadBtn.addEventListener('click', async function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();

                        // Agrandar leyenda temporalmente
                        const layoutParaDescarga = {
                            'legend.font.size': 12,
                            'legend.itemwidth': 40
                        };

                        await Plotly.relayout(chartDiv, layoutParaDescarga);

                        setTimeout(async () => {
                            await Plotly.downloadImage(chartDiv, {
                                format: 'png',
                                filename: `humedad_${estacion}_${anio}_${mes}`,
                                height: 800,
                                width: 1600,
                                scale: 2
                            });

                            // Restaurar leyenda compacta
                            const layoutOriginal = {
                                'legend.font.size': 7.5,
                                'legend.itemwidth': 20
                            };

                            await Plotly.relayout(chartDiv, layoutOriginal);
                            showCustomDownloadNotification(`humedad_${estacion}_${anio}_${mes}.png`);
                        }, 300);
                    });
                }
            });

            charts['chartHumL1'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Humedad L1 creada con Plotly (Radar)');

        } catch (error) {
            console.error('❌ Error en Humedad L1:', error);
            mostrarMensajeError('chartHumL1', 'Error al cargar humedad: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA L1-3: PRESIÓN BAROMÉTRICA DIARIA (BARRAS)
    // ================================================================
    async function loadChartPresionL1() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros para Presión L1');
            return;
        }

        try {
            console.log('🚀 Cargando Presión Barométrica L1 con Plotly...');

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l1_presion&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            if (!result.success || result.data.total_dias === 0) {
                console.log('⚠️ Sin datos para Presión L1');
                mostrarMensajeSinDatos('chartPresionL1');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);

            // Remover overlay de espera
            removeWaitingOverlay('chartPresionL1');

            // Destruir gráfica Plotly anterior si existe
            if (charts['chartPresionL1']) {
                try {
                    Plotly.purge('chartPresionL1');
                } catch (e) {
                    console.log('No hay gráfica Plotly anterior');
                }
                delete charts['chartPresionL1'];
            }

            const chartDiv = document.getElementById('chartPresionL1');
            if (!chartDiv) {
                console.error('❌ No se encontró el elemento #chartPresionL1');
                return;
            }

            // ===== CALCULAR RANGOS =====
            const presionMax = Math.max(...datos.presion_promedio, ...datos.presion_maxima);
            const presionMin = Math.min(...datos.presion_promedio, ...datos.presion_minima);
            const rangoY = presionMax - presionMin;

            const yMin = presionMin - rangoY * 0.1;
            const yMax = presionMax + rangoY * 0.1;
            const xMin = 0;
            const xMax = datos.labels.length - 1;

            // ===== COLORES POR NIVEL DE PRESIÓN =====
            const coloresPorPresion = datos.presion_promedio.map(presion => {
                if (presion < 1000) return 'rgba(255, 7, 58, 0.8)';       // Baja - Rojo
                if (presion < 1010) return 'rgba(255, 149, 0, 0.8)';      // Normal-Baja - Naranja
                if (presion < 1020) return 'rgba(0, 255, 136, 0.8)';      // Normal - Verde
                if (presion < 1030) return 'rgba(0, 255, 255, 0.8)';      // Normal-Alta - Cyan
                return 'rgba(0, 150, 255, 0.8)';                          // Alta - Azul
            });

            // ===== TRACE 1: BARRAS - PRESIÓN PROMEDIO =====
            const tracePresion = {
                name: 'Presión Promedio (hPa)',
                x: datos.labels,
                y: datos.presion_promedio,
                type: 'bar',
                marker: {
                    color: coloresPorPresion,
                    line: {
                        color: '#ffffff',
                        width: 1
                    }
                },
                hovertemplate: '<b>%{x}</b><br>Presión: %{y:.2f} hPa<extra></extra>'
            };

            // ===== TRACE 2: LÍNEA - PRESIÓN MÁXIMA =====
            const traceMaxima = {
                name: 'Presión Máxima',
                x: datos.labels,
                y: datos.presion_maxima,
                type: 'scatter',
                mode: 'lines+markers',
                line: {
                    color: 'rgb(255, 7, 58)',
                    width: 2,
                    dash: 'dot'
                },
                marker: {
                    size: 4,
                    color: '#ff073a'
                },
                hovertemplate: '<b>%{x}</b><br>Máxima: %{y:.2f} hPa<extra></extra>'
            };

            // ===== TRACE 3: LÍNEA - PRESIÓN MÍNIMA =====
            const traceMinima = {
                name: 'Presión Mínima',
                x: datos.labels,
                y: datos.presion_minima,
                type: 'scatter',
                mode: 'lines+markers',
                line: {
                    color: 'rgb(0, 150, 255)',
                    width: 2,
                    dash: 'dot'
                },
                marker: {
                    size: 4,
                    color: '#0096ff'
                },
                hovertemplate: '<b>%{x}</b><br>Mínima: %{y:.2f} hPa<extra></extra>'
            };

            const data = [tracePresion, traceMaxima, traceMinima];

            // ===== LAYOUT =====
            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.2)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 11,
                    color: 'rgba(255, 255, 255, 0.8)'
                },
                margin: {
                    l: 60,
                    r: 40,
                    t: 40,
                    b: 70
                },

                // Eje X
                xaxis: {
                    title: '',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.05)',
                    zeroline: false,
                    tickangle: -45,
                    tickfont: {
                        size: 9,
                        color: 'rgba(255, 255, 255, 0.6)'
                    },
                    range: [0, Math.min(30, xMax)],
                    fixedrange: false,
                    rangemode: 'normal',
                    autorange: false
                },

                // Eje Y
                yaxis: {
                    title: 'Presión (hPa)',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.08)',
                    zeroline: true,
                    zerolinecolor: 'rgba(255, 255, 255, 0.1)',
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    range: [yMin, yMax],
                    fixedrange: true,
                    autorange: false
                },

                // Leyenda
                legend: {
                    orientation: 'v',
                    x: 0.02,
                    xanchor: 'left',
                    y: 0.98,
                    yanchor: 'top',
                    font: {
                        size: 9,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.7)',
                    bordercolor: 'rgba(0, 255, 136, 0.3)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 30
                },

                hovermode: 'x unified',
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(0, 255, 136, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                },
                dragmode: 'pan'
            };

            // ===== CONFIGURACIÓN =====
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                scrollZoom: true,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'resetScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d'
                ],
                modeBarButtonsToAdd: [
                    {
                        name: '🏠 Resetear Vista',
                        icon: Plotly.Icons.home,
                        click: function (gd) {
                            Plotly.relayout(gd, {
                                'xaxis.range': [0, Math.min(30, xMax)],
                                'yaxis.range': [yMin, yMax]
                            });
                        }
                    }
                ],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `presion_barometrica_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chartPresionL1 .modebar-btn[data-title*="Download"]');

                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chartPresionL1')) {
                    downloadBtn.setAttribute('data-custom-handler-chartPresionL1', 'true');

                    downloadBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();
                        showCustomDownloadNotification(`presion_barometrica_${estacion}_${anio}_${mes}.png`);
                    });
                }
            });

            // ===== LIMITAR RANGO DEL EJE X =====
            chartDiv.on('plotly_relayout', function (eventdata) {
                if (eventdata['xaxis.range[0]'] !== undefined) {
                    let newMin = eventdata['xaxis.range[0]'];
                    let newMax = eventdata['xaxis.range[1]'];

                    if (newMin < xMin) {
                        const diff = newMax - newMin;
                        newMin = xMin;
                        newMax = xMin + diff;
                    }
                    if (newMax > xMax) {
                        const diff = newMax - newMin;
                        newMax = xMax;
                        newMin = xMax - diff;
                    }

                    if (newMin !== eventdata['xaxis.range[0]'] || newMax !== eventdata['xaxis.range[1]']) {
                        Plotly.relayout(chartDiv, {
                            'xaxis.range': [newMin, newMax]
                        });
                    }
                }
            });

            charts['chartPresionL1'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Presión Barométrica L1 creada con Plotly');

        } catch (error) {
            console.error('❌ Error en Presión L1:', error);
            mostrarMensajeError('chartPresionL1', 'Error al cargar presión: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA L1-4: VELOCIDAD DEL VIENTO + TEMPERATURA (DOBLE EJE Y)
    // ================================================================
    async function loadChartVientoTemperaturaL1() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros para Viento-Temperatura L1');
            return;
        }

        try {
            console.log('🚀 Cargando Viento-Temperatura L1 con Plotly...');

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l1_viento_temperatura&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            if (!result.success || result.data.total_horas === 0) {
                console.log('⚠️ Sin datos para Viento-Temperatura L1');
                mostrarMensajeSinDatos('chartVientoL1');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);

            // Remover overlay de espera
            removeWaitingOverlay('chartVientoL1');

            // Destruir gráfica Plotly anterior si existe
            if (charts['chartVientoL1']) {
                try {
                    Plotly.purge('chartVientoL1');
                } catch (e) {
                    console.log('No hay gráfica Plotly anterior');
                }
                delete charts['chartVientoL1'];
            }

            const chartDiv = document.getElementById('chartVientoL1');
            if (!chartDiv) {
                console.error('❌ No se encontró el elemento #chartVientoL1');
                return;
            }

            // ===== CALCULAR RANGOS PARA EVITAR SALIRSE =====
            const velocidadesMax = Math.max(...datos.vel_promedio, ...datos.vel_maxima);
            const velocidadesMin = Math.min(...datos.vel_promedio);
            const rangoYVel = velocidadesMax - velocidadesMin;

            const yMinVel = Math.max(0, velocidadesMin - rangoYVel * 0.1);
            const yMaxVel = velocidadesMax + rangoYVel * 0.1;

            const temperaturaMax = Math.max(...datos.temp_promedio);
            const temperaturaMin = Math.min(...datos.temp_promedio);
            const rangoYTemp = temperaturaMax - temperaturaMin;

            const yMinTemp = temperaturaMin - rangoYTemp * 0.1;
            const yMaxTemp = temperaturaMax + rangoYTemp * 0.1;

            const xMin = 0;
            const xMax = datos.etiquetas.length - 1;

            // ===== TRACE 1: BARRAS - VELOCIDAD PROMEDIO =====
            const traceVelocidad = {
                name: 'Velocidad Promedio (m/s)',
                x: datos.etiquetas,
                y: datos.vel_promedio,
                type: 'bar',
                marker: {
                    color: datos.vel_promedio.map((vel) => {
                        if (vel < 2) return 'rgba(0, 255, 136, 0.8)';
                        if (vel < 4) return 'rgba(0, 255, 255, 0.8)';
                        if (vel < 6) return 'rgba(255, 214, 10, 0.8)';
                        if (vel < 8) return 'rgba(255, 149, 0, 0.8)';
                        return 'rgba(255, 7, 58, 0.8)';
                    }),
                    line: {
                        color: '#ffffff',
                        width: 1
                    }
                },
                hovertemplate: '<b>%{x}</b><br>Velocidad: %{y:.2f} m/s<extra></extra>',
                yaxis: 'y'
            };

            // ===== TRACE 2: LÍNEA - TEMPERATURA PROMEDIO =====
            const traceTemperatura = {
                name: 'Temperatura Promedio (°C)',
                x: datos.etiquetas,
                y: datos.temp_promedio,
                type: 'scatter',
                mode: 'lines+markers',
                line: {
                    color: 'rgb(255, 149, 0)',
                    width: 3,
                    shape: 'spline'
                },
                marker: {
                    size: 6,
                    color: '#ff9500',
                    line: {
                        color: '#ffffff',
                        width: 2
                    }
                },
                hovertemplate: '<b>%{x}</b><br>Temperatura: %{y:.2f} °C<extra></extra>',
                yaxis: 'y2'
            };

            const data = [traceVelocidad, traceTemperatura];

            // ===== LAYOUT DE LA GRÁFICA =====
            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.2)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 11,
                    color: 'rgba(255, 255, 255, 0.8)'
                },
                margin: {
                    l: 60,
                    r: 60,
                    t: 40,
                    b: 70
                },

                // Eje X
                xaxis: {
                    title: '',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.05)',
                    zeroline: false,
                    tickangle: -45,
                    tickfont: {
                        size: 9,
                        color: 'rgba(255, 255, 255, 0.6)'
                    },
                    range: [0, Math.min(23, xMax)],
                    fixedrange: false,
                    rangemode: 'normal',
                    autorange: false
                },

                // Eje Y1: Velocidad (izquierda)
                yaxis: {
                    title: 'Velocidad (m/s)',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.08)',
                    zeroline: true,
                    zerolinecolor: 'rgba(255, 255, 255, 0.1)',
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    range: [yMinVel, yMaxVel],
                    fixedrange: true,
                    autorange: false,
                    side: 'left'
                },

                // Eje Y2: Temperatura (derecha)
                yaxis2: {
                    title: 'Temperatura (°C)',
                    showgrid: false,
                    zeroline: false,
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    overlaying: 'y',
                    side: 'right',
                    range: [yMinTemp, yMaxTemp],
                    fixedrange: true
                },

                // Leyenda vertical arriba-izquierda
                legend: {
                    orientation: 'v',
                    x: 0.02,
                    xanchor: 'left',
                    y: 0.98,
                    yanchor: 'top',
                    font: {
                        size: 9,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.7)',
                    bordercolor: 'rgba(0, 255, 255, 0.3)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 30
                },

                hovermode: 'x unified',
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(0, 255, 255, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                },
                dragmode: 'pan'
            };

            // ===== CONFIGURACIÓN (HOME + DOWNLOAD) =====
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                scrollZoom: true,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'resetScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d'
                ],
                modeBarButtonsToAdd: [
                    {
                        name: '🏠 Resetear Vista',
                        icon: Plotly.Icons.home,
                        click: function (gd) {
                            Plotly.relayout(gd, {
                                'xaxis.range': [0, Math.min(23, xMax)],
                                'yaxis.range': [yMinVel, yMaxVel],
                                'yaxis2.range': [yMinTemp, yMaxTemp]
                            });
                        }
                    }
                ],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `viento_temperatura_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA CON NOTIFICACIÓN PERSONALIZADA =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chartVientoL1 .modebar-btn[data-title*="Download"]');
                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chartVientoL1')) {
                    downloadBtn.setAttribute('data-custom-handler-chartVientoL1', 'true');

                    downloadBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();
                        showCustomDownloadNotification(`viento_temperatura_${estacion}_${anio}_${mes}.png`);
                    });
                }
            });

            // ===== LIMITAR RANGO DEL EJE X (NO SALIRSE) =====
            chartDiv.on('plotly_relayout', function (eventdata) {
                if (eventdata['xaxis.range[0]'] !== undefined) {
                    let newMin = eventdata['xaxis.range[0]'];
                    let newMax = eventdata['xaxis.range[1]'];

                    if (newMin < xMin) {
                        const diff = newMax - newMin;
                        newMin = xMin;
                        newMax = xMin + diff;
                    }
                    if (newMax > xMax) {
                        const diff = newMax - newMin;
                        newMax = xMax;
                        newMin = xMax - diff;
                    }

                    if (newMin !== eventdata['xaxis.range[0]'] || newMax !== eventdata['xaxis.range[1]']) {
                        Plotly.relayout(chartDiv, {
                            'xaxis.range': [newMin, newMax]
                        });
                    }
                }
            });

            charts['chartVientoL1'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Viento-Temperatura L1 creada con Plotly (doble eje Y)');

        } catch (error) {
            console.error('❌ Error en Viento-Temperatura L1:', error);
            mostrarMensajeError('chartVientoL1', 'Error al cargar viento-temperatura: ' + error.message);
        }
    }


    function startCarousel() {
        stopCarousel();

        carouselInterval = setInterval(() => {
            nextSlide();
        }, 5000);

        console.log('✅ Carrusel iniciado');
    }
    function stopCarousel() {
        if (carouselInterval) {
            clearInterval(carouselInterval);
            carouselInterval = null;
            currentSlide = 0;
            console.log('⏹️ Carrusel detenido');
        }
    }
    function nextSlide() {
        const slides = document.querySelectorAll('.carousel-slide');

        if (slides.length === 0) return;

        slides[currentSlide].classList.remove('active');

        currentSlide = (currentSlide + 1) % slides.length;

        slides[currentSlide].classList.add('active');
    }



    async function loadDataFromDatabase(estacion, anio, mes) {
        try {
            const action = `get_data_${currentLevel.toLowerCase()}`;
            const params = new URLSearchParams({
                action: action,
                id_estacion: estacion,
                anio: anio,
                mes: mes
            });

            console.log('🔄 Cargando datos desde la base de datos...', { estacion, anio, mes });

            const response = await fetch(`../controller/CDashboard.php?${params}`);

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }

            const data = await response.json();

            if (data.success) {
                console.log('✅ Datos cargados:', data.count, 'registros');
            } else {
                throw new Error(data.error || 'Error desconocido al cargar datos');
            }

        } catch (error) {
            console.error('❌ Error al cargar datos:', error);
        }
    }

    async function loadChartsFromDatabase(level, estacion, anio, mes) {
        console.log('📊 Cargando gráficas del nivel', level, 'desde BD...');
        let canvasIds = [];

        if (level === 'L0') {
            canvasIds = ['chart1', 'chart2', 'chart3', 'chart4', 'chart5',
                'chart6', 'chart7', 'chart8', 'chart9', 'chart10'];
        } else if (level === 'L1') {
            canvasIds = ['chartTempL1', 'chartHumL1', 'chartPrecipL1', 'chartVientoL1',
                'chartPresionL1', 'chartRadiacionL1', 'chartUVL1', 'chartRoccioL1',
                'chartVisibilidadL1', 'chartConfortL1'];
        } else if (level === 'L2') {
            canvasIds = ['chartTendenciaL2', 'chartClimogramaL2', 'chartAnalisisL2',
                'chartDistribucionL2', 'chartPrecipitacionesL2', 'chartAridezL2',
                'chartHeladasL2', 'chartPerfilTermicoL2', 'chartSecasL2',
                'chartConfortClimaticoL2'];
        }

        // Restaurar canvas para las gráficas
        canvasIds.forEach(canvasId => {
            const wrapper = document.querySelector(`#${canvasId}`)?.closest('.chart-canvas-wrapper');
            if (wrapper) {
                wrapper.innerHTML = `<canvas id="${canvasId}"></canvas>`;
            }
        });

        // Aquí cargarás los datos reales y crearás las gráficas
        console.log('✅ Canvas restaurados, listos para gráficas con datos reales');
    }

    function clearYearMonthSelects() {
        clearYearSelect();
        clearMonthSelect();
    }

    function clearYearSelect() {
        const select = document.getElementById('filterAnio');
        select.innerHTML = '<option value="">Seleccione año...</option>';
        availableYears = [];
    }

    function clearMonthSelect() {
        const select = document.getElementById('filterMes');
        select.innerHTML = '<option value="">Seleccione mes...</option>';
        availableMonths = [];
    }

    async function loadGeneralStats() {
        try {
            console.log('🔄 Cargando estadísticas generales iniciales...');

            const response = await fetch('../controller/CDashboard.php?action=get_general_stats');

            if (!response.ok) {
                throw new Error('Error en la respuesta del servidor');
            }

            const data = await response.json();

            if (data.success) {
                const stats = data.data;

                totalEstacionesActivas = stats.estaciones_activas;

                updateStatsDisplay(0, totalEstacionesActivas, '---');

                console.log('✅ Estadísticas generales cargadas');
            } else {
                throw new Error(data.error || 'Error desconocido al cargar estadísticas');
            }

        } catch (error) {
            console.error('❌ Error al cargar estadísticas:', error);
            updateStatsDisplay(0, 0, '---');
        }
    }
    async function loadChartVelocidadViento() {
        console.log('🚀 Iniciando carga de gráfica 1 con Plotly...');

        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        console.log('📋 Filtros:', { estacion, anio, mes });

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros, mostrando mensaje');
            mostrarMensajeSinDatos('chart1');
            return;
        }

        try {
            if (typeof Plotly === 'undefined') {
                console.error('❌ ERROR: Plotly.js NO está cargado');
                mostrarMensajeError('chart1', 'Error: Librería de gráficas no disponible');
                return;
            }

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l0_velocidad_hora&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            const response = await fetch(url);
            const result = await response.json();

            if (!result.success || result.data.total_horas === 0) {
                mostrarMensajeSinDatos('chart1');
                return;
            }

            const datos = result.data;

            removeWaitingOverlay('chart1');

            const chartDiv = document.getElementById('chart1');
            if (!chartDiv) {
                console.error('❌ ERROR: No se encontró el elemento #chart1');
                return;
            }

            if (charts['chart1']) {
                try {
                    Plotly.purge('chart1');
                } catch (e) {
                    console.log('⚠️ No se pudo destruir gráfica anterior:', e);
                }
                delete charts['chart1'];
            }

            const velocidadesMax = Math.max(...datos.velocidades, ...datos.maximas);
            const velocidadesMin = Math.min(...datos.velocidades, ...datos.minimas);
            const rangoY = velocidadesMax - velocidadesMin;

            const yMin = Math.max(0, velocidadesMin - rangoY * 0.1);
            const yMax = velocidadesMax + rangoY * 0.1;
            const xMin = 0;
            const xMax = datos.labels.length - 1;

            const trace1 = {
                name: 'Velocidad Promedio',
                x: datos.labels,
                y: datos.velocidades,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(0, 255, 255)',
                    width: 2.5
                },
                fill: 'tozeroy',
                fillcolor: 'rgba(0, 255, 255, 0.2)',
                hovertemplate: '<b>%{x}</b><br>Velocidad: %{y:.2f} m/s<extra></extra>'
            };

            const trace2 = {
                name: 'Velocidad Máxima',
                x: datos.labels,
                y: datos.maximas,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(255, 7, 58)',
                    width: 1.5,
                    dash: 'dot'
                },
                hovertemplate: '<b>%{x}</b><br>Máxima: %{y:.2f} m/s<extra></extra>'
            };

            const trace3 = {
                name: 'Velocidad Mínima',
                x: datos.labels,
                y: datos.minimas,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(0, 255, 136)',
                    width: 1.5,
                    dash: 'dot'
                },
                hovertemplate: '<b>%{x}</b><br>Mínima: %{y:.2f} m/s<extra></extra>'
            };

            const data = [trace1, trace2, trace3];

            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.2)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 11,
                    color: 'rgba(255, 255, 255, 0.8)'
                },
                margin: {
                    l: 60,
                    r: 40,
                    t: 40,
                    b: 70
                },
                xaxis: {
                    title: '',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.05)',
                    zeroline: false,
                    tickangle: -45,
                    tickfont: {
                        size: 9,
                        color: 'rgba(255, 255, 255, 0.6)'
                    },
                    range: [0, Math.min(23, datos.labels.length - 1)],
                    fixedrange: false,
                    rangemode: 'normal',
                    autorange: false
                },
                yaxis: {
                    title: 'Velocidad (m/s)',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.08)',
                    zeroline: true,
                    zerolinecolor: 'rgba(255, 255, 255, 0.1)',
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    range: [yMin, yMax],
                    fixedrange: true,
                    autorange: false
                },
                legend: {
                    orientation: 'v',
                    x: 0.02,
                    xanchor: 'left',
                    y: 0.98,
                    yanchor: 'top',
                    font: {
                        size: 9,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.7)',
                    bordercolor: 'rgba(0, 255, 255, 0.3)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 30
                },
                hovermode: 'x unified',
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(0, 255, 255, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                },
                dragmode: 'pan'
            };

            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                scrollZoom: true,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'resetScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d'
                ],
                modeBarButtonsToAdd: [
                    {
                        name: '🏠 Resetear Vista',
                        icon: Plotly.Icons.home,
                        click: function (gd) {
                            Plotly.relayout(gd, {
                                'xaxis.range': [0, Math.min(23, xMax)],
                                'yaxis.range': [yMin, yMax]
                            });
                        }
                    }
                ],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `velocidad_viento_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            await Plotly.newPlot(chartDiv, data, layout, config);

            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('[data-title*="Download"]');

                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler')) {
                    downloadBtn.setAttribute('data-custom-handler', 'true');

                    downloadBtn.addEventListener('click', function () {
                        hideAllPlotlyNotifications();
                        showCustomDownloadNotification(`velocidad_viento_${estacion}_${anio}_${mes}.png`);
                    });
                }
            });

            chartDiv.on('plotly_relayout', function (eventdata) {
                if (eventdata['xaxis.range[0]'] !== undefined) {
                    let newMin = eventdata['xaxis.range[0]'];
                    let newMax = eventdata['xaxis.range[1]'];

                    if (newMin < xMin) {
                        const diff = newMax - newMin;
                        newMin = xMin;
                        newMax = xMin + diff;
                    }
                    if (newMax > xMax) {
                        const diff = newMax - newMin;
                        newMax = xMax;
                        newMin = xMax - diff;
                    }

                    if (newMin !== eventdata['xaxis.range[0]'] || newMax !== eventdata['xaxis.range[1]']) {
                        Plotly.relayout(chartDiv, {
                            'xaxis.range': [newMin, newMax]
                        });
                    }
                }
            });

            charts['chart1'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Plotly profesional creada');

        } catch (error) {
            console.error('❌ Error:', error);
            mostrarMensajeError('chart1', 'Error al cargar datos: ' + error.message);
        }
    }

    function hideAllPlotlyNotifications() {
        // Seleccionar todos los elementos de notificación de Plotly
        const selectors = [
            '.plotly .notifier',
            '.plotly .notifier-container',
            '.plotly .notifier-note',
            'div[class*="notifier"]',
            'div[class*="plotly-notifier"]',
            'body > svg[class*="snapshot"]',
            'body > svg[class*="notifier"]'
        ];

        selectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                el.style.display = 'none';
                el.style.opacity = '0';
                el.style.visibility = 'hidden';
                el.style.pointerEvents = 'none';
            });
        });

        // Buscar divs flotantes en la derecha
        const allDivs = document.querySelectorAll('body > div');
        allDivs.forEach(div => {
            const style = window.getComputedStyle(div);
            const position = style.position;
            const right = style.right;

            // Si es un div posicionado a la derecha
            if ((position === 'fixed' || position === 'absolute') &&
                (right === '0px' || parseInt(right) < 50)) {

                // Verificar si contiene texto relacionado con snapshot
                const text = div.textContent.toLowerCase();
                if (text.includes('snapshot') || text.includes('taking') || text.includes('succeeded')) {
                    div.style.display = 'none';
                    div.remove(); // Eliminarlo completamente del DOM
                }
            }
        });
    }
    function showCustomDownloadNotification(filename) {
        hideAllPlotlyNotifications();

        // Crear notificación personalizada
        const notification = document.createElement('div');
        notification.className = 'custom-download-notification';
        notification.innerHTML = `
        <div class="custom-download-notification-content">
            <i class="fas fa-download custom-download-notification-icon"></i>
            <div class="custom-download-notification-text">
                Generando imagen...
            </div>
            <div class="custom-download-notification-subtext">
                Por favor espera un momento
            </div>
        </div>
    `;

        document.body.appendChild(notification);
        const hideInterval = setInterval(() => {
            hideAllPlotlyNotifications();
        }, 100);
        setTimeout(() => {
            clearInterval(hideInterval);

            // Cambiar a mensaje de éxito
            notification.innerHTML = `
            <div class="custom-download-notification-content">
                <i class="fas fa-check-circle custom-download-notification-icon" style="color: var(--accent-green);"></i>
                <div class="custom-download-notification-text">
                    ¡Descarga Completada!
                </div>
                <div class="custom-download-notification-subtext">
                    ${filename}
                </div>
                <div class="custom-download-notification-success">
                    <i class="fas fa-folder-open"></i>
                    <span>Revisa tu carpeta de descargas</span>
                </div>
            </div>
        `;

            setTimeout(() => {
                notification.style.animation = 'downloadPulse 0.3s ease-in reverse';
                setTimeout(() => {
                    notification.remove();
                    hideAllPlotlyNotifications(); // Última limpieza
                }, 300);
            }, 3000);

        }, 1500);
    }




    function calcularMediaMovil(datos, periodos) {
        const resultado = [];

        for (let i = 0; i < datos.length; i++) {
            if (i < periodos - 1) {
                resultado.push(null);
            } else {
                let suma = 0;
                for (let j = 0; j < periodos; j++) {
                    suma += datos[i - j];
                }
                resultado.push(suma / periodos);
            }
        }

        return resultado;
    }

    async function loadChartMetricasRealTime() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) return;

        try {
            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l0_direccion&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            const response = await fetch(url);
            const result = await response.json();

            if (!result.success || result.data.total_horas === 0) {
                mostrarMensajeSinDatos('chart2');
                return;
            }

            const datos = result.data;

            removeWaitingOverlay('chart2');

            if (charts['chart2']) {
                if (typeof charts['chart2'].destroy === 'function') {
                    charts['chart2'].destroy();
                }
                delete charts['chart2'];
            }

            // Agrupar por sectores
            const sectores = { N: [], NE: [], E: [], SE: [], S: [], SW: [], W: [], NW: [] };
            const nombresSectores = Object.keys(sectores);

            datos.direcciones.forEach((dir, i) => {
                const sector = Math.floor(((dir + 22.5) % 360) / 45);
                sectores[nombresSectores[sector]].push(datos.velocidades[i]);
            });

            const promedios = nombresSectores.map(sector => {
                const vels = sectores[sector];
                return vels.length > 0 ? parseFloat((vels.reduce((a, b) => a + b, 0) / vels.length).toFixed(2)) : 0;
            });

            const frecuencias = nombresSectores.map(sector => sectores[sector].length);

            const traceVelocidad = {
                type: 'scatterpolar',
                name: 'Velocidad Promedio (m/s)',
                r: promedios,
                theta: nombresSectores,
                fill: 'toself',
                fillcolor: 'rgba(0, 255, 255, 0.25)',
                line: {
                    color: 'rgb(0, 255, 255)',
                    width: 2.5
                },
                marker: {
                    size: 6,
                    color: ['#00ffff', '#00ff88', '#ffd60a', '#ff9500', '#ff073a', '#ff006e', '#9d4edd', '#0096ff']
                },
                hovertemplate: '<b>%{theta}</b><br>Velocidad: %{r:.2f} m/s<extra></extra>'
            };

            const traceFrecuencia = {
                type: 'scatterpolar',
                name: 'Frecuencia de Ocurrencias',
                r: frecuencias,
                theta: nombresSectores,
                fill: 'toself',
                fillcolor: 'rgba(255, 214, 10, 0.15)',
                line: {
                    color: 'rgb(255, 214, 10)',
                    width: 2,
                    dash: 'dot'
                },
                marker: {
                    size: 4,
                    color: '#ffd60a'
                },
                hovertemplate: '<b>%{theta}</b><br>Frecuencia: %{r}<extra></extra>'
            };

            const data = [traceVelocidad, traceFrecuencia];

            const layout = {
                polar: {
                    bgcolor: 'rgba(0, 0, 0, 0.2)',
                    radialaxis: {
                        visible: true,
                        gridcolor: 'rgba(255, 255, 255, 0.2)',
                        tickfont: {
                            size: 9,
                            color: 'rgba(255, 255, 255, 0.5)'
                        }
                    },
                    angularaxis: {
                        tickfont: {
                            size: 12,
                            color: '#00ffff'
                        },
                        gridcolor: 'rgba(255, 255, 255, 0.25)',
                        direction: 'clockwise',
                        rotation: 90
                    },
                },
                plot_bgcolor: 'transparent',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    color: '#ffffff'
                },
                margin: {
                    l: 60,
                    r: 60,
                    t: 40,
                    b: 80
                },
                legend: {
                    orientation: 'h',
                    x: 0.5,
                    xanchor: 'center',
                    y: -0.20,
                    yanchor: 'top',
                    font: {
                        size: 7.5,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.85)',
                    bordercolor: 'rgba(0, 255, 255, 0.4)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 20,
                    tracegroupgap: 3
                },
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(0, 255, 255, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                }
            };

            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'zoom2d',
                    'pan2d',
                    'select2d',
                    'lasso2d',
                    'zoomIn2d',
                    'zoomOut2d',
                    'autoScale2d',
                    'resetScale2d',
                    'hoverClosestCartesian',
                    'hoverCompareCartesian',
                    'toggleSpikelines'
                ],
                modeBarButtonsToAdd: [],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `direccion_viento_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                }
            };

            const chartDiv = document.getElementById('chart2');
            await Plotly.newPlot(chartDiv, data, layout, config);
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chart2 .modebar-btn[data-title*="Download"]');

                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chart2')) {
                    downloadBtn.setAttribute('data-custom-handler-chart2', 'true');

                    downloadBtn.addEventListener('click', async function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();

                        const layoutParaDescarga = {
                            'legend.font.size': 12,
                            'legend.itemwidth': 40
                        };

                        // Aplicar cambios temporales
                        await Plotly.relayout(chartDiv, layoutParaDescarga);

                        // Esperar un momento para que se apliquen los cambios
                        setTimeout(async () => {
                            // Descargar la imagen
                            await Plotly.downloadImage(chartDiv, {
                                format: 'png',
                                filename: `direccion_viento_${estacion}_${anio}_${mes}`,
                                height: 800,
                                width: 1600,
                                scale: 2
                            });

                            const layoutOriginal = {
                                'legend.font.size': 7.5,
                                'legend.itemwidth': 20
                            };

                            await Plotly.relayout(chartDiv, layoutOriginal);
                            showCustomDownloadNotification(`direccion_viento_${estacion}_${anio}_${mes}.png`);
                        }, 300);
                    });
                }
            });

            charts['chart2'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Radar Plotly con leyenda compacta (descarga con tamaño normal)');

        } catch (error) {
            console.error('❌ Error:', error);
            mostrarMensajeError('chart2', 'Error al cargar direcciones');
        }
    }


    function obtenerPuntoCardinal(grados) {
        const puntos = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
        const indice = Math.round(((grados % 360) / 45)) % 8;
        return puntos[indice];
    }

    async function loadChartRosaVientos() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) return;

        try {
            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l0_rosa_vientos&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            const response = await fetch(url);
            const result = await response.json();

            if (!result.success) {
                mostrarMensajeSinDatos('chart3');
                return;
            }

            const datos = result.data;

            removeWaitingOverlay('chart3');

            if (charts['chart3']) {
                try {
                    Plotly.purge('chart3');
                } catch (e) {
                    console.log('No hay gráfica Plotly para destruir en chart3');
                }
                delete charts['chart3'];
            }

            const sectoresOrdenados = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
            const coloresSectores = ['#00ffff', '#00ff88', '#0096ff', '#9d4edd', '#ff073a', '#ff9500', '#ffd60a', '#ff006e'];

            const frecuenciasOrdenadas = [];
            const velocidadesOrdenadas = [];

            sectoresOrdenados.forEach(sector => {
                const indice = datos.sectores.indexOf(sector);
                if (indice !== -1) {
                    frecuenciasOrdenadas.push(datos.frecuencias[indice]);
                    velocidadesOrdenadas.push(datos.velocidades[indice]);
                } else {
                    frecuenciasOrdenadas.push(0);
                    velocidadesOrdenadas.push(0);
                }
            });

            const traces = sectoresOrdenados.map((sector, index) => ({
                type: 'barpolar',
                name: sector,
                r: [frecuenciasOrdenadas[index]],
                theta: [sector],
                marker: {
                    color: coloresSectores[index],
                    line: {
                        color: '#ffffff',
                        width: 2
                    }
                },
                hovertemplate: `<b>${sector}</b><br>Frecuencia: %{r}<br>Velocidad: ${velocidadesOrdenadas[index].toFixed(2)} m/s<extra></extra>`,
                showlegend: true
            }));

            const layout = {
                polar: {
                    bgcolor: 'rgba(0, 0, 0, 0.2)',
                    radialaxis: {
                        visible: true,
                        gridcolor: 'rgba(255, 255, 255, 0.2)',
                        tickfont: {
                            size: 9,
                            color: 'rgba(255, 255, 255, 0.5)'
                        }
                    },
                    angularaxis: {
                        tickfont: {
                            size: 12,
                            color: '#00ffff'
                        },
                        gridcolor: 'rgba(255, 255, 255, 0.25)',
                        direction: 'clockwise',
                        rotation: 90
                    }
                },
                plot_bgcolor: 'transparent',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    color: '#ffffff'
                },
                margin: {
                    l: 60,
                    r: 60,
                    t: 30,
                    b: 120
                },
                legend: {
                    orientation: 'h',
                    x: 0.5,
                    xanchor: 'center',
                    y: -0.30,
                    yanchor: 'top',
                    font: {
                        size: 8.5,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.85)',
                    bordercolor: 'rgba(0, 255, 255, 0.4)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 22,
                    tracegroupgap: 5,
                    traceorder: 'normal'
                },
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(0, 255, 255, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                }
            };

            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'zoom2d',
                    'pan2d',
                    'select2d',
                    'lasso2d',
                    'zoomIn2d',
                    'zoomOut2d',
                    'autoScale2d',
                    'resetScale2d',
                    'hoverClosestCartesian',
                    'hoverCompareCartesian',
                    'toggleSpikelines'
                ],
                modeBarButtonsToAdd: [],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `rosa_vientos_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                }
            };

            const chartDiv = document.getElementById('chart3');
            await Plotly.newPlot(chartDiv, traces, layout, config);

            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chart3 .modebar-btn[data-title*="Download"]');

                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chart3')) {
                    downloadBtn.setAttribute('data-custom-handler-chart3', 'true');

                    downloadBtn.addEventListener('click', async function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();

                        const layoutParaDescarga = {
                            'legend.font.size': 14,
                            'legend.itemwidth': 35
                        };

                        await Plotly.relayout(chartDiv, layoutParaDescarga);

                        setTimeout(async () => {
                            await Plotly.downloadImage(chartDiv, {
                                format: 'png',
                                filename: `rosa_vientos_${estacion}_${anio}_${mes}`,
                                height: 800,
                                width: 1600,
                                scale: 2
                            });

                            const layoutOriginal = {
                                'legend.font.size': 8.5,
                                'legend.itemwidth': 22
                            };

                            await Plotly.relayout(chartDiv, layoutOriginal);

                            showCustomDownloadNotification(`rosa_vientos_${estacion}_${anio}_${mes}.png`);
                        }, 300);
                    });
                }
            });

            charts['chart3'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Rosa de vientos con leyenda de colores completa');

        } catch (error) {
            console.error('❌ Error:', error);
            mostrarMensajeError('chart3', 'Error al cargar rosa de vientos');
        }
    }

    async function loadChartDistribucion() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) return;

        try {
            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l0_velocidad&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;
            const response = await fetch(url);
            const result = await response.json();

            if (!result.success || result.data.total_registros === 0) {
                mostrarMensajeSinDatos('chart4');
                return;
            }

            const velocidades = result.data.velocidades;

            const rangos = [0, 2, 4, 6, 8, 10, 12];
            const frecuencias = new Array(rangos.length - 1).fill(0);

            velocidades.forEach(vel => {
                for (let i = 0; i < rangos.length - 1; i++) {
                    if (vel >= rangos[i] && vel < rangos[i + 1]) {
                        frecuencias[i]++;
                        break;
                    }
                }
            });

            const labels = rangos.slice(0, -1).map((r, i) => `${r}-${rangos[i + 1]} m/s`);

            removeWaitingOverlay('chart4');
            if (charts['chart4']) {
                charts['chart4'].destroy();
                delete charts['chart4'];
            }

            const ctx = document.getElementById('chart4').getContext('2d');
            const gradiente = ctx.createLinearGradient(0, 0, 0, 400);
            gradiente.addColorStop(0, 'rgba(157, 78, 221, 0.8)');
            gradiente.addColorStop(1, 'rgba(0, 150, 255, 0.8)');

            charts['chart4'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Frecuencia',
                        data: frecuencias,
                        backgroundColor: gradiente,
                        borderColor: '#9d4edd',
                        borderWidth: 2,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(10, 14, 26, 0.95)',
                            titleColor: '#9d4edd',
                            bodyColor: '#ffffff',
                            borderColor: '#9d4edd',
                            borderWidth: 1,
                            callbacks: {
                                label: (context) => `Ocurrencias: ${context.parsed.y}`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255, 255, 255, 0.08)' },
                            ticks: { color: 'rgba(255, 255, 255, 0.7)' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.7)',
                                font: { size: 9 }
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('Error en chart4:', error);
            mostrarMensajeError('chart4', 'Error al cargar distribución');
        }
    }

    async function loadChartIntensidadHora() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) return;

        try {
            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l0_velocidad&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;
            const response = await fetch(url);
            const result = await response.json();

            if (!result.success || result.data.total_registros === 0) {
                mostrarMensajeSinDatos('chart5');
                return;
            }

            const datos = result.data;

            // Agrupar por hora (0-23)
            const promediosPorHora = new Array(24).fill(0);
            const conteosPorHora = new Array(24).fill(0);

            datos.labels.forEach((label, index) => {
                const horaMatch = label.match(/(\d{2}):(\d{2})/);
                if (horaMatch) {
                    const hora = parseInt(horaMatch[1]);
                    promediosPorHora[hora] += datos.velocidades[index];
                    conteosPorHora[hora]++;
                }
            });

            const promedios = promediosPorHora.map((sum, i) =>
                conteosPorHora[i] > 0 ? sum / conteosPorHora[i] : 0
            );

            const horas = Array.from({ length: 24 }, (_, i) => `${i.toString().padStart(2, '0')}:00`);

            removeWaitingOverlay('chart5');
            if (charts['chart5']) {
                charts['chart5'].destroy();
                delete charts['chart5'];
            }

            const ctx = document.getElementById('chart5').getContext('2d');

            // Colores según intensidad
            const colores = promedios.map(vel => {
                if (vel < 2) return 'rgba(0, 255, 136, 0.7)';
                if (vel < 4) return 'rgba(0, 255, 255, 0.7)';
                if (vel < 6) return 'rgba(255, 214, 10, 0.7)';
                if (vel < 8) return 'rgba(255, 149, 0, 0.7)';
                return 'rgba(255, 7, 58, 0.7)';
            });

            charts['chart5'] = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: horas,
                    datasets: [{
                        label: 'Velocidad Promedio (m/s)',
                        data: promedios,
                        backgroundColor: colores,
                        borderWidth: 0,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(10, 14, 26, 0.95)',
                            titleColor: '#00ffff',
                            bodyColor: '#ffffff',
                            borderColor: '#00ffff',
                            borderWidth: 1,
                            callbacks: {
                                label: (context) => `${context.parsed.y.toFixed(2)} m/s`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255, 255, 255, 0.08)' },
                            ticks: { color: 'rgba(255, 255, 255, 0.7)' }
                        },
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: 'rgba(255, 255, 255, 0.7)',
                                font: { size: 8 },
                                maxRotation: 90,
                                minRotation: 90
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('Error en chart5:', error);
            mostrarMensajeError('chart5', 'Error al cargar intensidad por hora');
        }
    }

    async function loadChartScatter() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) return;

        try {
            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l0_velocidad&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;
            const response = await fetch(url);
            const result = await response.json();

            if (!result.success || result.data.total_registros === 0) {
                mostrarMensajeSinDatos('chart6');
                return;
            }

            const datos = result.data;

            const scatterData = datos.velocidades.map((vel, i) => ({
                x: i,
                y: vel
            }));

            removeWaitingOverlay('chart6');
            if (charts['chart6']) {
                charts['chart6'].destroy();
                delete charts['chart6'];
            }

            const ctx = document.getElementById('chart6').getContext('2d');
            charts['chart6'] = new Chart(ctx, {
                type: 'scatter',
                data: {
                    datasets: [
                        {
                            label: 'Velocidad Promedio (m/s)',
                            data: promediosPorSector,
                            backgroundColor: 'rgba(0, 255, 255, 0.3)',
                            borderColor: '#00ffff',
                            borderWidth: 2,
                            pointBackgroundColor: [
                                '#00ffff', '#00ff88', '#ffd60a', '#ff9500',
                                '#ff073a', '#ff006e', '#9d4edd', '#0096ff'
                            ],
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1.5,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointHoverBackgroundColor: '#ffffff',
                            pointHoverBorderColor: '#00ffff',
                            pointHoverBorderWidth: 2
                        },
                        {
                            label: 'Frecuencia de Ocurrencias',
                            data: frecuenciaPorSector,
                            backgroundColor: 'rgba(255, 214, 10, 0.2)',
                            borderColor: '#ffd60a',
                            borderWidth: 1.5,
                            borderDash: [5, 5],
                            pointBackgroundColor: '#ffd60a',
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 1,
                            pointRadius: 3,
                            pointHoverRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(10, 14, 26, 0.95)',
                            titleColor: '#00ffff',
                            bodyColor: '#ffffff',
                            borderColor: '#00ffff',
                            borderWidth: 1,
                            callbacks: {
                                label: (context) => `Velocidad: ${context.parsed.y.toFixed(2)} m/s`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Velocidad (m/s)',
                                color: '#00ffff'
                            },
                            grid: { color: 'rgba(255, 255, 255, 0.08)' },
                            ticks: { color: 'rgba(255, 255, 255, 0.7)' }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Índice Temporal',
                                color: '#00ffff'
                            },
                            grid: { color: 'rgba(255, 255, 255, 0.05)' },
                            ticks: { color: 'rgba(255, 255, 255, 0.7)' }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('Error en chart6:', error);
            mostrarMensajeError('chart6', 'Error al cargar scatter plot');
        }
    }

    async function loadChartDirecciones() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) return;

        try {
            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l0_velocidad&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;
            const response = await fetch(url);
            const result = await response.json();

            if (!result.success || result.data.total_registros === 0) {
                mostrarMensajeSinDatos('chart7');
                return;
            }

            const direcciones = result.data.direcciones;

            // Clasificar en cuadrantes
            const cuadrantes = { 'Norte (315-45°)': 0, 'Este (45-135°)': 0, 'Sur (135-225°)': 0, 'Oeste (225-315°)': 0 };

            direcciones.forEach(dir => {
                if ((dir >= 315 && dir <= 360) || (dir >= 0 && dir < 45)) cuadrantes['Norte (315-45°)']++;
                else if (dir >= 45 && dir < 135) cuadrantes['Este (45-135°)']++;
                else if (dir >= 135 && dir < 225) cuadrantes['Sur (135-225°)']++;
                else cuadrantes['Oeste (225-315°)']++;
            });

            removeWaitingOverlay('chart7');
            if (charts['chart7']) {
                charts['chart7'].destroy();
                delete charts['chart7'];
            }

            const ctx = document.getElementById('chart7').getContext('2d');
            charts['chart7'] = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(cuadrantes),
                    datasets: [{
                        data: Object.values(cuadrantes),
                        backgroundColor: [
                            'rgba(0, 255, 255, 0.7)',
                            'rgba(255, 214, 10, 0.7)',
                            'rgba(255, 7, 58, 0.7)',
                            'rgba(157, 78, 221, 0.7)'
                        ],
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: '#ffffff',
                                font: { size: 10 },
                                padding: 10
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(10, 14, 26, 0.95)',
                            titleColor: '#00ffff',
                            bodyColor: '#ffffff',
                            borderColor: '#00ffff',
                            borderWidth: 1,
                            callbacks: {
                                label: (context) => {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const porcentaje = ((context.parsed / total) * 100).toFixed(1);
                                    return `${context.label}: ${context.parsed} (${porcentaje}%)`;
                                }
                            }
                        }
                    }
                }
            });

        } catch (error) {
            console.error('Error en chart7:', error);
            mostrarMensajeError('chart7', 'Error al cargar direcciones');
        }
    }
    // ================================================================
    // GRÁFICA L1-5: MAPA DE CALOR - RADIACIÓN SOLAR ☀️
    // ================================================================
    async function loadChartPresionAtmosfericaL1() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros para Heatmap Radiación Solar');
            return;
        }

        try {
            console.log(' Cargando Heatmap Radiación Solar con Plotly...');

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l1_heatmap_radiacion&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            if (!result.success || result.data.total_puntos === 0) {
                console.log('⚠️ Sin datos para Heatmap Radiación Solar');
                mostrarMensajeSinDatos('chartPresionAtmosfericaL1');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);
            console.log('🔍 Modo:', datos.modo);

            // Remover overlay de espera
            removeWaitingOverlay('chartPresionAtmosfericaL1');

            // Destruir gráfica Plotly anterior
            if (charts['chartPresionAtmosfericaL1']) {
                try {
                    Plotly.purge('chartPresionAtmosfericaL1');
                } catch (e) {
                    console.log('No hay gráfica Plotly anterior');
                }
                delete charts['chartPresionAtmosfericaL1'];
            }

            const chartDiv = document.getElementById('chartPresionAtmosfericaL1');
            if (!chartDiv) {
                console.error('❌ No se encontró #chartPresionAtmosfericaL1');
                return;
            }

            // ===== TRANSPONER MATRIZ PARA PLOTLY =====
            const matrizTranspuesta = [];
            for (let h = 0; h < 24; h++) {
                const fila = [];
                for (let p = 0; p < datos.matriz_radiacion.length; p++) {
                    const valor = datos.matriz_radiacion[p][h];
                    fila.push(valor !== null ? valor : null);
                }
                matrizTranspuesta.push(fila);
            }

            // ===== CALCULAR RANGOS =====
            const xMin = 0;
            const xMax = datos.etiquetas_periodo.length - 1;
            const yMin = 0;
            const yMax = 23;

            // Rango inicial según modo
            const rangoInicialX = datos.modo === 'anual'
                ? [0, 11]  // 12 meses completos
                : [0, Math.min(30, xMax)]; // Hasta 31 días

            // ===== ESCALA DE COLORES TIPO SOL (ESPECTACULAR) =====
            const traceHeatmap = {
                type: 'heatmap',
                z: matrizTranspuesta,
                x: datos.etiquetas_periodo,
                y: datos.etiquetas_horas,
                colorscale: [
                    [0, 'rgb(10, 10, 40)'],        // Negro-azul (NOCHE - 0 W/m²)
                    [0.05, 'rgb(30, 30, 80)'],     // Azul muy oscuro (PRE-AMANECER)
                    [0.15, 'rgb(100, 60, 150)'],   // Morado (AMANECER)
                    [0.25, 'rgb(255, 100, 100)'],  // Rosa-Rojo (SOL BAJO)
                    [0.40, 'rgb(255, 180, 0)'],    // Naranja (MAÑANA)
                    [0.60, 'rgb(255, 220, 0)'],    // Amarillo brillante (MEDIO DÍA)
                    [0.80, 'rgb(255, 255, 150)'],  // Amarillo claro (PICO SOLAR)
                    [1, 'rgb(255, 255, 255)']      // Blanco (MÁXIMA RADIACIÓN)
                ],
                colorbar: {
                    title: {
                        text: '<br>Radiación<br>Solar<br>(W/m²)',
                        side: 'right',
                        font: {
                            size: 13,
                            color: '#ffffff',
                            weight: 'bold'
                        }
                    },
                    thickness: 30,
                    len: 0.95,
                    x: 1.02,
                    tickfont: {
                        size: 11,
                        color: '#ffffff',
                        weight: 'bold'
                    },
                    tickcolor: 'rgba(255, 255, 255, 0.6)',
                    outlinecolor: 'rgba(255, 214, 10, 0.8)',
                    outlinewidth: 3,
                    bgcolor: 'rgba(0, 0, 0, 0.85)',
                    tickmode: 'linear',
                    tick0: 0,
                    dtick: 200
                },
                hovertemplate: '<b>%{x}</b><br>Hora: %{y}<br><b>☀️ Radiación: %{z:.2f} W/m²</b><extra></extra>',
                zsmooth: 'best',
                showscale: true
            };

            const data = [traceHeatmap];

            // ===== TÍTULOS DINÁMICOS =====
            const tituloEjeX = datos.modo === 'anual' ? 'Mes del Año' : 'Día del Mes';

            // ===== LAYOUT PROFESIONAL =====
            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.4)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 12,
                    color: '#ffffff'
                },
                margin: {
                    l: 70,
                    r: 140,
                    t: 60,
                    b: 80
                },

                // Título de la gráfica
                title: {
                    text: '☀️ Mapa de Calor: Radiación Solar',
                    font: {
                        size: 16,
                        color: '#ffd60a',
                        weight: 'bold'
                    },
                    x: 0.5,
                    xanchor: 'center'
                },

                // Eje X
                xaxis: {
                    title: {
                        text: tituloEjeX,
                        font: {
                            size: 14,
                            color: '#ffd60a'
                        }
                    },
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.15)',
                    zeroline: false,
                    tickangle: -45,
                    tickfont: {
                        size: 11,
                        color: '#ffffff'
                    },
                    range: rangoInicialX,
                    fixedrange: false,
                    rangemode: 'normal',
                    autorange: false
                },

                // Eje Y
                yaxis: {
                    title: {
                        text: 'Hora del Día',
                        font: {
                            size: 14,
                            color: '#ffd60a'
                        }
                    },
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.15)',
                    zeroline: false,
                    tickfont: {
                        size: 11,
                        color: '#ffffff'
                    },
                    range: [yMin, yMax],
                    fixedrange: true,
                    autorange: false
                },

                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(255, 214, 10, 0.8)',
                    font: {
                        size: 13,
                        color: '#ffffff'
                    }
                },
                dragmode: 'pan'
            };

            // ===== CONFIGURACIÓN =====
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                scrollZoom: true,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'resetScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d'
                ],
                modeBarButtonsToAdd: [
                    {
                        name: '🏠 Resetear Vista',
                        icon: Plotly.Icons.home,
                        click: function (gd) {
                            Plotly.relayout(gd, {
                                'xaxis.range': rangoInicialX,
                                'yaxis.range': [yMin, yMax]
                            });
                        }
                    }
                ],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `heatmap_radiacion_solar_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chartPresionAtmosfericaL1 .modebar-btn[data-title*="Download"]');
                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chartPresionAtmosfericaL1')) {
                    downloadBtn.setAttribute('data-custom-handler-chartPresionAtmosfericaL1', 'true');

                    downloadBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();
                        showCustomDownloadNotification(`heatmap_radiacion_solar_${estacion}_${anio}_${mes}.png`);
                    });
                }
            });

            // ===== LIMITAR RANGO DEL EJE X =====
            chartDiv.on('plotly_relayout', function (eventdata) {
                if (eventdata['xaxis.range[0]'] !== undefined) {
                    let newMin = eventdata['xaxis.range[0]'];
                    let newMax = eventdata['xaxis.range[1]'];

                    if (newMin < xMin) {
                        const diff = newMax - newMin;
                        newMin = xMin;
                        newMax = xMin + diff;
                    }
                    if (newMax > xMax) {
                        const diff = newMax - newMin;
                        newMax = xMax;
                        newMin = xMax - diff;
                    }

                    if (newMin !== eventdata['xaxis.range[0]'] || newMax !== eventdata['xaxis.range[1]']) {
                        Plotly.relayout(chartDiv, {
                            'xaxis.range': [newMin, newMax]
                        });
                    }
                }
            });

            charts['chartPresionAtmosfericaL1'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Heatmap Radiación Solar creado (modo: ' + datos.modo + ')');

        } catch (error) {
            console.error('❌ Error en Heatmap Radiación Solar:', error);
            mostrarMensajeError('chartPresionAtmosfericaL1', 'Error: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA L1-6: BALANCE ENERGÉTICO (RADIACIÓN + TEMPERATURA + VIENTO)
    // ================================================================
    async function loadChartBalanceEnergeticoL1() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros para Balance Energético L1');
            return;
        }

        try {
            console.log('🚀 Cargando Balance Energético L1 con Plotly...');

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l1_balance_energetico&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            if (!result.success || result.data.total_horas === 0) {
                console.log('⚠️ Sin datos para Balance Energético L1');
                mostrarMensajeSinDatos('chartBalanceEnergeticoL1');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);

            // Remover overlay de espera
            removeWaitingOverlay('chartBalanceEnergeticoL1');

            // Destruir gráfica Plotly anterior si existe
            if (charts['chartBalanceEnergeticoL1']) {
                try {
                    Plotly.purge('chartBalanceEnergeticoL1');
                } catch (e) {
                    console.log('No hay gráfica Plotly anterior');
                }
                delete charts['chartBalanceEnergeticoL1'];
            }

            const chartDiv = document.getElementById('chartBalanceEnergeticoL1');
            if (!chartDiv) {
                console.error('❌ No se encontró el elemento #chartBalanceEnergeticoL1');
                return;
            }

            // ===== CALCULAR RANGOS PARA 3 EJES =====

            // Eje Y1: Radiación Solar (W/m²)
            const radiacionMax = Math.max(...datos.radiacion_promedio);
            const radiacionMin = Math.min(...datos.radiacion_promedio.filter(r => r > 0));
            const rangoRadiacion = radiacionMax - radiacionMin;
            const yMinRadiacion = Math.max(0, radiacionMin - rangoRadiacion * 0.1);
            const yMaxRadiacion = radiacionMax + rangoRadiacion * 0.1;

            // Eje Y2: Temperatura (°C)
            const tempMax = Math.max(...datos.temperatura_promedio);
            const tempMin = Math.min(...datos.temperatura_promedio);
            const rangoTemp = tempMax - tempMin;
            const yMinTemp = tempMin - rangoTemp * 0.1;
            const yMaxTemp = tempMax + rangoTemp * 0.1;

            // Eje Y3: Velocidad Viento (m/s)
            const vientoMax = Math.max(...datos.velocidad_viento);
            const vientoMin = Math.min(...datos.velocidad_viento);
            const rangoViento = vientoMax - vientoMin;
            const yMinViento = Math.max(0, vientoMin - rangoViento * 0.1);
            const yMaxViento = vientoMax + rangoViento * 0.1;

            const xMin = 0;
            const xMax = datos.labels.length - 1;

            // ===== TRACE 1: RADIACIÓN SOLAR (Área amarilla) =====
            const traceRadiacion = {
                name: 'Radiación Solar (W/m²)',
                x: datos.labels,
                y: datos.radiacion_promedio,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(255, 214, 10)',
                    width: 3,
                    shape: 'spline'
                },
                fill: 'tozeroy',
                fillcolor: 'rgba(255, 214, 10, 0.3)',
                hovertemplate: '<b>%{x}</b><br>Radiación: %{y:.2f} W/m²<extra></extra>',
                yaxis: 'y'
            };

            // ===== TRACE 2: TEMPERATURA (Línea naranja) =====
            const traceTemperatura = {
                name: 'Temperatura del Aire (°C)',
                x: datos.labels,
                y: datos.temperatura_promedio,
                type: 'scatter',
                mode: 'lines+markers',
                line: {
                    color: 'rgb(255, 149, 0)',
                    width: 2.5,
                    shape: 'spline'
                },
                marker: {
                    size: 4,
                    color: '#ff9500'
                },
                hovertemplate: '<b>%{x}</b><br>Temperatura: %{y:.2f} °C<extra></extra>',
                yaxis: 'y2'
            };

            // ===== TRACE 3: VELOCIDAD DEL VIENTO (Línea cyan) =====
            const traceViento = {
                name: 'Velocidad del Viento (m/s)',
                x: datos.labels,
                y: datos.velocidad_viento,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(0, 255, 255)',
                    width: 2,
                    dash: 'dot',
                    shape: 'spline'
                },
                hovertemplate: '<b>%{x}</b><br>Viento: %{y:.2f} m/s<extra></extra>',
                yaxis: 'y3'
            };

            const data = [traceRadiacion, traceTemperatura, traceViento];

            // ===== LAYOUT CON 3 EJES Y =====
            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.2)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 11,
                    color: 'rgba(255, 255, 255, 0.8)'
                },
                margin: {
                    l: 60,
                    r: 80,
                    t: 40,
                    b: 70
                },

                // Eje X
                xaxis: {
                    title: '',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.05)',
                    zeroline: false,
                    tickangle: -45,
                    tickfont: {
                        size: 9,
                        color: 'rgba(255, 255, 255, 0.6)'
                    },
                    range: [0, Math.min(23, xMax)],
                    fixedrange: false,
                    rangemode: 'normal',
                    autorange: false
                },

                // Eje Y1: Radiación Solar (izquierda)
                yaxis: {
                    title: 'Radiación (W/m²)',
                    titlefont: { color: '#ffd60a' },
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.08)',
                    zeroline: true,
                    zerolinecolor: 'rgba(255, 255, 255, 0.1)',
                    tickfont: {
                        size: 10,
                        color: '#ffd60a'
                    },
                    range: [yMinRadiacion, yMaxRadiacion],
                    fixedrange: true,
                    autorange: false,
                    side: 'left'
                },

                // Eje Y2: Temperatura (derecha superior)
                yaxis2: {
                    title: 'Temperatura (°C)',
                    titlefont: { color: '#ff9500' },
                    showgrid: false,
                    zeroline: false,
                    tickfont: {
                        size: 10,
                        color: '#ff9500'
                    },
                    overlaying: 'y',
                    side: 'right',
                    range: [yMinTemp, yMaxTemp],
                    fixedrange: true,
                    position: 0.95
                },

                // Eje Y3: Velocidad Viento (derecha inferior)
                yaxis3: {
                    title: 'Viento (m/s)',
                    titlefont: { color: '#00ffff' },
                    showgrid: false,
                    zeroline: false,
                    tickfont: {
                        size: 9,
                        color: '#00ffff'
                    },
                    overlaying: 'y',
                    side: 'right',
                    range: [yMinViento, yMaxViento],
                    fixedrange: true,
                    position: 1,
                    anchor: 'free'
                },

                // Leyenda
                legend: {
                    orientation: 'h',
                    x: 0.5,
                    xanchor: 'center',
                    y: -0.20,
                    yanchor: 'top',
                    font: {
                        size: 7.5,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.85)',
                    bordercolor: 'rgba(255, 214, 10, 0.3)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 20,
                    tracegroupgap: 3
                },

                hovermode: 'x unified',
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(255, 214, 10, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                },
                dragmode: 'pan'
            };

            // ===== CONFIGURACIÓN =====
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                scrollZoom: true,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'resetScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d'
                ],
                modeBarButtonsToAdd: [
                    {
                        name: '🏠 Resetear Vista',
                        icon: Plotly.Icons.home,
                        click: function (gd) {
                            Plotly.relayout(gd, {
                                'xaxis.range': [0, Math.min(23, xMax)],
                                'yaxis.range': [yMinRadiacion, yMaxRadiacion],
                                'yaxis2.range': [yMinTemp, yMaxTemp],
                                'yaxis3.range': [yMinViento, yMaxViento]
                            });
                        }
                    }
                ],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `balance_energetico_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chartBalanceEnergeticoL1 .modebar-btn[data-title*="Download"]');

                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chartBalanceEnergeticoL1')) {
                    downloadBtn.setAttribute('data-custom-handler-chartBalanceEnergeticoL1', 'true');

                    downloadBtn.addEventListener('click', async function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();

                        // Agrandar leyenda temporalmente
                        const layoutParaDescarga = {
                            'legend.font.size': 12,
                            'legend.itemwidth': 40
                        };

                        await Plotly.relayout(chartDiv, layoutParaDescarga);

                        setTimeout(async () => {
                            await Plotly.downloadImage(chartDiv, {
                                format: 'png',
                                filename: `balance_energetico_${estacion}_${anio}_${mes}`,
                                height: 800,
                                width: 1600,
                                scale: 2
                            });

                            // Restaurar leyenda compacta
                            const layoutOriginal = {
                                'legend.font.size': 7.5,
                                'legend.itemwidth': 20
                            };

                            await Plotly.relayout(chartDiv, layoutOriginal);
                            showCustomDownloadNotification(`balance_energetico_${estacion}_${anio}_${mes}.png`);
                        }, 300);
                    });
                }
            });

            // ===== LIMITAR RANGO DEL EJE X =====
            chartDiv.on('plotly_relayout', function (eventdata) {
                if (eventdata['xaxis.range[0]'] !== undefined) {
                    let newMin = eventdata['xaxis.range[0]'];
                    let newMax = eventdata['xaxis.range[1]'];

                    if (newMin < xMin) {
                        const diff = newMax - newMin;
                        newMin = xMin;
                        newMax = xMin + diff;
                    }
                    if (newMax > xMax) {
                        const diff = newMax - newMin;
                        newMax = xMax;
                        newMin = xMax - diff;
                    }

                    if (newMin !== eventdata['xaxis.range[0]'] || newMax !== eventdata['xaxis.range[1]']) {
                        Plotly.relayout(chartDiv, {
                            'xaxis.range': [newMin, newMax]
                        });
                    }
                }
            });

            charts['chartBalanceEnergeticoL1'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Balance Energético L1 creada con Plotly (3 variables)');

        } catch (error) {
            console.error('❌ Error en Balance Energético L1:', error);
            mostrarMensajeError('chartBalanceEnergeticoL1', 'Error al cargar balance energético: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA L1-7: PUNTO DE ROCÍO CALCULADO (DEW POINT)
    // ================================================================
    async function loadChartPuntoRocioL1() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros para Punto de Rocío L1');
            return;
        }

        try {
            console.log('🚀 Cargando Punto de Rocío L1 con Plotly...');

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l1_punto_rocio&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            if (!result.success || result.data.total_horas === 0) {
                console.log('⚠️ Sin datos para Punto de Rocío L1');
                mostrarMensajeSinDatos('chartPuntoRocioL1');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);

            // Remover overlay de espera
            removeWaitingOverlay('chartPuntoRocioL1');

            // Destruir gráfica Plotly anterior si existe
            if (charts['chartPuntoRocioL1']) {
                try {
                    Plotly.purge('chartPuntoRocioL1');
                } catch (e) {
                    console.log('No hay gráfica Plotly anterior');
                }
                delete charts['chartPuntoRocioL1'];
            }

            const chartDiv = document.getElementById('chartPuntoRocioL1');
            if (!chartDiv) {
                console.error('❌ No se encontró el elemento #chartPuntoRocioL1');
                return;
            }

            // ===== CALCULAR RANGOS PARA EVITAR SALIRSE =====
            const allValues = [...datos.punto_rocio, ...datos.temperatura_aire];
            const valorMax = Math.max(...allValues);
            const valorMin = Math.min(...allValues);
            const rangoY = valorMax - valorMin;

            const yMin = valorMin - rangoY * 0.1;
            const yMax = valorMax + rangoY * 0.1;
            const xMin = 0;
            const xMax = datos.labels.length - 1;

            // ===== GENERAR COLORES DINÁMICOS SEGÚN RIESGO =====
            const coloresPuntoRocio = datos.punto_rocio.map(td => {
                if (td < 0) return 'rgba(0, 150, 255, 0.8)';      // Azul oscuro (RIESGO HELADAS)
                if (td < 10) return 'rgba(0, 255, 136, 0.8)';     // Verde (Seco/confortable)
                if (td < 15) return 'rgba(255, 214, 10, 0.8)';    // Amarillo (Moderado)
                if (td < 20) return 'rgba(255, 149, 0, 0.8)';     // Naranja (Húmedo)
                return 'rgba(255, 7, 58, 0.8)';                   // Rojo (Muy húmedo/incómodo)
            });

            // ===== CONFIGURACIÓN DE DATOS =====

            // Trace 1: Área del Punto de Rocío (principal)
            const tracePuntoRocio = {
                name: 'Punto de Rocío (°C)',
                x: datos.labels,
                y: datos.punto_rocio,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(0, 150, 255)',
                    width: 3,
                    shape: 'spline'
                },
                fill: 'tozeroy',
                fillcolor: 'rgba(0, 150, 255, 0.3)',
                hovertemplate: '<b>%{x}</b><br>Punto de Rocío: %{y:.2f} °C<extra></extra>'
            };

            // Trace 2: Línea de Temperatura del Aire (referencia)
            const traceTemperatura = {
                name: 'Temperatura del Aire (°C)',
                x: datos.labels,
                y: datos.temperatura_aire,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(255, 149, 0)',
                    width: 2,
                    dash: 'dash',
                    shape: 'spline'
                },
                hovertemplate: '<b>%{x}</b><br>Temperatura: %{y:.2f} °C<extra></extra>'
            };

            // Trace 3: Marcadores de Alerta de Heladas (Td < 0°C)
            const alertasHeladas = {
                name: '⚠️ RIESGO DE HELADAS',
                x: datos.labels_alertas_heladas,
                y: datos.valores_alertas_heladas,
                type: 'scatter',
                mode: 'markers',
                marker: {
                    color: 'rgb(0, 150, 255)',
                    size: 12,
                    symbol: 'triangle-down',
                    line: {
                        color: '#ffffff',
                        width: 2
                    }
                },
                hovertemplate: '<b>ALERTA HELADAS</b><br>%{x}<br>Td: %{y:.2f} °C<extra></extra>'
            };

            // Trace 4: Marcadores de Alto Riesgo Húmedo (Td > 20°C)
            const alertasHumedad = {
                name: '🌡️ AIRE MUY HÚMEDO',
                x: datos.labels_alertas_humedad,
                y: datos.valores_alertas_humedad,
                type: 'scatter',
                mode: 'markers',
                marker: {
                    color: 'rgb(255, 7, 58)',
                    size: 12,
                    symbol: 'triangle-up',
                    line: {
                        color: '#ffffff',
                        width: 2
                    }
                },
                hovertemplate: '<b>AIRE PESADO</b><br>%{x}<br>Td: %{y:.2f} °C<extra></extra>'
            };

            const data = [tracePuntoRocio, traceTemperatura];

            // Solo agregar marcadores si existen alertas
            if (datos.labels_alertas_heladas.length > 0) {
                data.push(alertasHeladas);
            }
            if (datos.labels_alertas_humedad.length > 0) {
                data.push(alertasHumedad);
            }

            // ===== LAYOUT CON ZONAS DE RIESGO =====
            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.2)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 11,
                    color: 'rgba(255, 255, 255, 0.8)'
                },
                margin: {
                    l: 60,
                    r: 40,
                    t: 40,
                    b: 70
                },

                // Eje X
                xaxis: {
                    title: '',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.05)',
                    zeroline: false,
                    tickangle: -45,
                    tickfont: {
                        size: 9,
                        color: 'rgba(255, 255, 255, 0.6)'
                    },
                    range: [0, Math.min(23, xMax)],
                    fixedrange: false,
                    rangemode: 'normal',
                    autorange: false
                },

                // Eje Y
                yaxis: {
                    title: 'Temperatura (°C)',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.08)',
                    zeroline: true,
                    zerolinecolor: 'rgba(255, 255, 255, 0.3)',
                    zerolinewidth: 2,
                    tickfont: {
                        size: 10,
                        color: 'rgba(255, 255, 255, 0.7)'
                    },
                    range: [yMin, yMax],
                    fixedrange: true,
                    autorange: false
                },

                // Shapes: Zonas de riesgo de fondo
                shapes: [
                    // Zona 1: Riesgo de Heladas (< 0°C) - Azul oscuro
                    {
                        type: 'rect',
                        xref: 'paper',
                        yref: 'y',
                        x0: 0,
                        y0: yMin,
                        x1: 1,
                        y1: 0,
                        fillcolor: 'rgba(0, 150, 255, 0.15)',
                        line: { width: 0 },
                        layer: 'below'
                    },
                    // Zona 2: Seco/Confortable (0-10°C) - Verde claro
                    {
                        type: 'rect',
                        xref: 'paper',
                        yref: 'y',
                        x0: 0,
                        y0: 0,
                        x1: 1,
                        y1: 10,
                        fillcolor: 'rgba(0, 255, 136, 0.08)',
                        line: { width: 0 },
                        layer: 'below'
                    },
                    // Zona 3: Moderado (10-15°C) - Amarillo
                    {
                        type: 'rect',
                        xref: 'paper',
                        yref: 'y',
                        x0: 0,
                        y0: 10,
                        x1: 1,
                        y1: 15,
                        fillcolor: 'rgba(255, 214, 10, 0.08)',
                        line: { width: 0 },
                        layer: 'below'
                    },
                    // Zona 4: Húmedo (15-20°C) - Naranja
                    {
                        type: 'rect',
                        xref: 'paper',
                        yref: 'y',
                        x0: 0,
                        y0: 15,
                        x1: 1,
                        y1: 20,
                        fillcolor: 'rgba(255, 149, 0, 0.08)',
                        line: { width: 0 },
                        layer: 'below'
                    },
                    // Zona 5: Muy Húmedo (> 20°C) - Rojo
                    {
                        type: 'rect',
                        xref: 'paper',
                        yref: 'y',
                        x0: 0,
                        y0: 20,
                        x1: 1,
                        y1: yMax,
                        fillcolor: 'rgba(255, 7, 58, 0.1)',
                        line: { width: 0 },
                        layer: 'below'
                    }
                ],

                // Anotaciones: Etiquetas de zonas
                annotations: [
                    {
                        xref: 'paper',
                        yref: 'y',
                        x: 0.02,
                        y: -2,
                        text: '❄️ RIESGO HELADAS',
                        showarrow: false,
                        font: {
                            size: 9,
                            color: 'rgba(0, 150, 255, 0.8)',
                            weight: 'bold'
                        },
                        xanchor: 'left'
                    },
                    {
                        xref: 'paper',
                        yref: 'y',
                        x: 0.02,
                        y: 5,
                        text: '✅ CONFORTABLE',
                        showarrow: false,
                        font: {
                            size: 9,
                            color: 'rgba(0, 255, 136, 0.8)',
                            weight: 'bold'
                        },
                        xanchor: 'left'
                    },
                    {
                        xref: 'paper',
                        yref: 'y',
                        x: 0.02,
                        y: 22,
                        text: '🌡️ AIRE PESADO',
                        showarrow: false,
                        font: {
                            size: 9,
                            color: 'rgba(255, 7, 58, 0.8)',
                            weight: 'bold'
                        },
                        xanchor: 'left'
                    }
                ],

                // Leyenda
                legend: {
                    orientation: 'v',
                    x: 0.02,
                    xanchor: 'left',
                    y: 0.98,
                    yanchor: 'top',
                    font: {
                        size: 9,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.7)',
                    bordercolor: 'rgba(0, 150, 255, 0.3)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 30
                },

                hovermode: 'x unified',
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(0, 150, 255, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                },
                dragmode: 'pan'
            };

            // ===== CONFIGURACIÓN =====
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                scrollZoom: true,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'resetScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d'
                ],
                modeBarButtonsToAdd: [
                    {
                        name: '🏠 Resetear Vista',
                        icon: Plotly.Icons.home,
                        click: function (gd) {
                            Plotly.relayout(gd, {
                                'xaxis.range': [0, Math.min(23, xMax)],
                                'yaxis.range': [yMin, yMax]
                            });
                        }
                    }
                ],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `punto_rocio_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chartPuntoRocioL1 .modebar-btn[data-title*="Download"]');

                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chartPuntoRocioL1')) {
                    downloadBtn.setAttribute('data-custom-handler-chartPuntoRocioL1', 'true');

                    downloadBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();
                        showCustomDownloadNotification(`punto_rocio_${estacion}_${anio}_${mes}.png`);
                    });
                }
            });

            // ===== LIMITAR RANGO DEL EJE X =====
            chartDiv.on('plotly_relayout', function (eventdata) {
                if (eventdata['xaxis.range[0]'] !== undefined) {
                    let newMin = eventdata['xaxis.range[0]'];
                    let newMax = eventdata['xaxis.range[1]'];

                    if (newMin < xMin) {
                        const diff = newMax - newMin;
                        newMin = xMin;
                        newMax = xMin + diff;
                    }
                    if (newMax > xMax) {
                        const diff = newMax - newMin;
                        newMax = xMax;
                        newMin = xMax - diff;
                    }

                    if (newMin !== eventdata['xaxis.range[0]'] || newMax !== eventdata['xaxis.range[1]']) {
                        Plotly.relayout(chartDiv, {
                            'xaxis.range': [newMin, newMax]
                        });
                    }
                }
            });

            charts['chartPuntoRocioL1'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Punto de Rocío L1 creada con Plotly');

        } catch (error) {
            console.error('❌ Error en Punto de Rocío L1:', error);
            mostrarMensajeError('chartPuntoRocioL1', 'Error al cargar punto de rocío: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA L2-1: TENDENCIA ANUAL - ANÁLISIS MULTIVARIABLE
    // ================================================================
    async function loadChartTendenciaL2() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros para Tendencia Anual L2');
            return;
        }

        try {
            console.log('🚀 Cargando Tendencia Anual L2 con Plotly...');

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l2_tendencia_anual&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            if (!result.success || result.data.total_meses === 0) {
                console.log('⚠️ Sin datos para Tendencia Anual L2');
                mostrarMensajeSinDatos('chartTendenciaL2');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);

            // Remover overlay de espera
            removeWaitingOverlay('chartTendenciaL2');

            // Destruir gráfica Plotly anterior si existe
            if (charts['chartTendenciaL2']) {
                try {
                    Plotly.purge('chartTendenciaL2');
                } catch (e) {
                    console.log('No hay gráfica Plotly anterior');
                }
                delete charts['chartTendenciaL2'];
            }

            const chartDiv = document.getElementById('chartTendenciaL2');
            if (!chartDiv) {
                console.error('❌ No se encontró el elemento #chartTendenciaL2');
                return;
            }

            // ===== CALCULAR RANGOS PARA LOS 4 EJES =====

            // Eje Y1: Temperatura (°C)
            const tempMax = Math.max(...datos.temperatura_promedio);
            const tempMin = Math.min(...datos.temperatura_promedio);
            const rangoTemp = tempMax - tempMin;
            const yMinTemp = tempMin - rangoTemp * 0.1;
            const yMaxTemp = tempMax + rangoTemp * 0.1;

            // Eje Y2: Humedad (%)
            const humMax = Math.max(...datos.humedad_promedio);
            const humMin = Math.min(...datos.humedad_promedio);
            const rangoHum = humMax - humMin;
            const yMinHum = Math.max(0, humMin - rangoHum * 0.1);
            const yMaxHum = Math.min(100, humMax + rangoHum * 0.1);

            // Eje Y3: Presión (hPa)
            const presMax = Math.max(...datos.presion_promedio);
            const presMin = Math.min(...datos.presion_promedio);
            const rangoPres = presMax - presMin;
            const yMinPres = presMin - rangoPres * 0.1;
            const yMaxPres = presMax + rangoPres * 0.1;

            // Eje Y4: Radiación (W/m²)
            const radMax = Math.max(...datos.radiacion_total);
            const radMin = Math.min(...datos.radiacion_total.filter(r => r > 0));
            const rangoRad = radMax - radMin;
            const yMinRad = Math.max(0, radMin - rangoRad * 0.1);
            const yMaxRad = radMax + rangoRad * 0.1;

            const xMin = 0;
            const xMax = datos.labels.length - 1;

            // ===== CONFIGURACIÓN DE TRACES =====

            // Trace 1: Temperatura del Aire (Área rellena - Eje Y1)
            const traceTemperatura = {
                name: 'Temperatura (°C)',
                x: datos.labels,
                y: datos.temperatura_promedio,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(255, 149, 0)',
                    width: 3,
                    shape: 'spline'
                },
                fill: 'tozeroy',
                fillcolor: 'rgba(255, 149, 0, 0.3)',
                hovertemplate: '<b>%{x}</b><br>Temperatura: %{y:.2f} °C<extra></extra>',
                yaxis: 'y'
            };

            // Trace 2: Humedad Relativa (Línea - Eje Y2)
            const traceHumedad = {
                name: 'Humedad (%)',
                x: datos.labels,
                y: datos.humedad_promedio,
                type: 'scatter',
                mode: 'lines+markers',
                line: {
                    color: 'rgb(0, 150, 255)',
                    width: 2.5,
                    shape: 'spline'
                },
                marker: {
                    size: 5,
                    color: '#0096ff'
                },
                hovertemplate: '<b>%{x}</b><br>Humedad: %{y:.2f}%<extra></extra>',
                yaxis: 'y2'
            };

            // Trace 3: Presión Barométrica (Línea punteada - Eje Y3)
            const tracePresion = {
                name: 'Presión (hPa)',
                x: datos.labels,
                y: datos.presion_promedio,
                type: 'scatter',
                mode: 'lines',
                line: {
                    color: 'rgb(157, 78, 221)',
                    width: 2,
                    dash: 'dot',
                    shape: 'spline'
                },
                hovertemplate: '<b>%{x}</b><br>Presión: %{y:.2f} hPa<extra></extra>',
                yaxis: 'y3'
            };

            // Trace 4: Radiación Solar (Barras - Eje Y4)
            const traceRadiacion = {
                name: 'Radiación (W/m²)',
                x: datos.labels,
                y: datos.radiacion_total,
                type: 'bar',
                marker: {
                    color: 'rgba(255, 214, 10, 0.6)',
                    line: {
                        color: '#ffd60a',
                        width: 1
                    }
                },
                hovertemplate: '<b>%{x}</b><br>Radiación: %{y:.0f} W/m²<extra></extra>',
                yaxis: 'y4'
            };

            const data = [traceTemperatura, traceHumedad, tracePresion, traceRadiacion];

            // ===== LAYOUT CON 4 EJES Y =====
            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.2)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 11,
                    color: 'rgba(255, 255, 255, 0.8)'
                },
                margin: {
                    l: 60,
                    r: 150,  // ✅ Más espacio para 3 ejes derechos
                    t: 40,
                    b: 70
                },

                // Eje X
                xaxis: {
                    title: '',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.05)',
                    zeroline: false,
                    tickangle: -45,
                    tickfont: {
                        size: 9,
                        color: 'rgba(255, 255, 255, 0.6)'
                    },
                    range: [xMin, xMax],
                    fixedrange: true,
                    autorange: false
                },

                // Eje Y1: Temperatura (izquierda)
                yaxis: {
                    title: 'Temperatura (°C)',
                    titlefont: { color: '#ff9500' },
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.08)',
                    zeroline: true,
                    zerolinecolor: 'rgba(255, 255, 255, 0.1)',
                    tickfont: {
                        size: 10,
                        color: '#ff9500'
                    },
                    range: [yMinTemp, yMaxTemp],
                    fixedrange: true,
                    autorange: false,
                    side: 'left'
                },

                // Eje Y2: Humedad (derecha posición 1)
                yaxis2: {
                    title: 'Humedad (%)',
                    titlefont: { color: '#0096ff', size: 10 },
                    showgrid: false,
                    zeroline: false,
                    tickfont: {
                        size: 9,
                        color: '#0096ff'
                    },
                    overlaying: 'y',
                    side: 'right',
                    range: [yMinHum, yMaxHum],
                    fixedrange: true,
                    position: 0.92
                },

                // Eje Y3: Presión (derecha posición 2)
                yaxis3: {
                    title: 'Presión (hPa)',
                    titlefont: { color: '#9d4edd', size: 9 },
                    showgrid: false,
                    zeroline: false,
                    tickfont: {
                        size: 8,
                        color: '#9d4edd'
                    },
                    overlaying: 'y',
                    side: 'right',
                    range: [yMinPres, yMaxPres],
                    fixedrange: true,
                    position: 0.96,
                    anchor: 'free'
                },

                // Eje Y4: Radiación (derecha posición 3)
                yaxis4: {
                    title: 'Radiación (W/m²)',
                    titlefont: { color: '#ffd60a', size: 9 },
                    showgrid: false,
                    zeroline: false,
                    tickfont: {
                        size: 8,
                        color: '#ffd60a'
                    },
                    overlaying: 'y',
                    side: 'right',
                    range: [yMinRad, yMaxRad],
                    fixedrange: true,
                    position: 1,
                    anchor: 'free'
                },

                // Leyenda horizontal abajo
                legend: {
                    orientation: 'h',
                    x: 0.5,
                    xanchor: 'center',
                    y: -0.20,
                    yanchor: 'top',
                    font: {
                        size: 7.5,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.85)',
                    bordercolor: 'rgba(157, 78, 221, 0.3)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 20,
                    tracegroupgap: 3
                },

                hovermode: 'x unified',
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(157, 78, 221, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                }
            };

            // ===== CONFIGURACIÓN (SOLO DOWNLOAD) =====
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'resetScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d',
                    'hoverClosestCartesian',
                    'hoverCompareCartesian',
                    'toggleSpikelines'
                ],
                modeBarButtonsToAdd: [],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `tendencia_anual_l2_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA CON NOTIFICACIÓN PERSONALIZADA =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chartTendenciaL2 .modebar-btn[data-title*="Download"]');

                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chartTendenciaL2')) {
                    downloadBtn.setAttribute('data-custom-handler-chartTendenciaL2', 'true');

                    downloadBtn.addEventListener('click', async function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();

                        // Agrandar leyenda temporalmente
                        const layoutParaDescarga = {
                            'legend.font.size': 12,
                            'legend.itemwidth': 40
                        };

                        await Plotly.relayout(chartDiv, layoutParaDescarga);

                        setTimeout(async () => {
                            await Plotly.downloadImage(chartDiv, {
                                format: 'png',
                                filename: `tendencia_anual_l2_${estacion}_${anio}_${mes}`,
                                height: 800,
                                width: 1600,
                                scale: 2
                            });

                            // Restaurar leyenda compacta
                            const layoutOriginal = {
                                'legend.font.size': 7.5,
                                'legend.itemwidth': 20
                            };

                            await Plotly.relayout(chartDiv, layoutOriginal);
                            showCustomDownloadNotification(`tendencia_anual_l2_${estacion}_${anio}_${mes}.png`);
                        }, 300);
                    });
                }
            });

            charts['chartTendenciaL2'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Tendencia Anual L2 creada con Plotly');

        } catch (error) {
            console.error('❌ Error en Tendencia Anual L2:', error);
            mostrarMensajeError('chartTendenciaL2', 'Error al cargar tendencia anual: ' + error.message);
        }
    }
    // ================================================================
    // GRÁFICA L2-2: CLIMOGRAMA INTEGRAL - TEMPERATURA Y VIENTO
    // Combina barras (temperatura promedio) + línea (velocidad del viento)
    // ================================================================
    async function loadChartClimogramaL2() {
        const estacion = document.getElementById('filterEstacion').value;
        const anio = document.getElementById('filterAnio').value;
        const mes = document.getElementById('filterMes').value;

        if (!estacion || !anio || !mes) {
            console.log('⚠️ Faltan filtros para Climograma L2');
            return;
        }

        try {
            console.log('🚀 Cargando Climograma Integral L2 con Plotly...');

            const mesParam = (mes === 'all') ? 'null' : mes;
            const url = `../controller/CDashboard.php?action=get_chart_l2_climograma&id_estacion=${estacion}&anio=${anio}&mes=${mesParam}`;

            console.log('📡 URL:', url);

            const response = await fetch(url);
            const result = await response.json();

            console.log('📦 Respuesta servidor:', result);

            // ✅ VALIDACIÓN CORREGIDA: Verificar datos en arrays como L0/L1
            if (!result.success || !result.data.labels || result.data.labels.length === 0) {
                console.log('⚠️ Sin datos para Climograma L2');
                mostrarMensajeSinDatos('chartClimogramaL2');
                return;
            }

            const datos = result.data;
            console.log('✅ Datos recibidos:', datos);

            // Remover overlay de espera
            removeWaitingOverlay('chartClimogramaL2');

            // Destruir gráfica Plotly anterior si existe
            if (charts['chartClimogramaL2']) {
                try {
                    Plotly.purge('chartClimogramaL2');
                } catch (e) {
                    console.log('No hay gráfica Plotly anterior');
                }
                delete charts['chartClimogramaL2'];
            }

            const chartDiv = document.getElementById('chartClimogramaL2');
            if (!chartDiv) {
                console.error('❌ No se encontró el elemento #chartClimogramaL2');
                return;
            }

            // ===== CALCULAR RANGOS PARA 2 EJES =====

            // Eje Y1: Temperatura (°C)
            const tempMax = Math.max(...datos.temperatura_promedio);
            const tempMin = Math.min(...datos.temperatura_promedio);
            const rangoTemp = tempMax - tempMin;
            const yMinTemp = tempMin - rangoTemp * 0.1;
            const yMaxTemp = tempMax + rangoTemp * 0.1;

            // Eje Y2: Viento (m/s)
            const vientoMax = Math.max(...datos.viento_promedio);
            const vientoMin = Math.min(...datos.viento_promedio);
            const rangoViento = vientoMax - vientoMin;
            const yMinViento = Math.max(0, vientoMin - rangoViento * 0.1);
            const yMaxViento = vientoMax + rangoViento * 0.1;

            // ===== COLORES DINÁMICOS PARA BARRAS DE TEMPERATURA =====
            const coloresTemperatura = datos.temperatura_promedio.map(temp => {
                if (temp < 5) return 'rgba(0, 150, 255, 0.8)';      // Muy frío - Azul
                if (temp < 10) return 'rgba(0, 255, 136, 0.8)';     // Frío - Verde
                if (temp < 15) return 'rgba(255, 214, 10, 0.8)';    // Templado - Amarillo
                if (temp < 20) return 'rgba(255, 149, 0, 0.8)';     // Cálido - Naranja
                return 'rgba(255, 7, 58, 0.8)';                     // Caliente - Rojo
            });

            // ===== CONFIGURACIÓN DE TRACES =====

            // Trace 1: Barras - Temperatura Promedio (Eje Y1)
            const traceTemperatura = {
                name: 'Temperatura Promedio (°C)',
                x: datos.labels,
                y: datos.temperatura_promedio,
                type: 'bar',
                marker: {
                    color: coloresTemperatura,
                    line: {
                        color: '#ffffff',
                        width: 1
                    }
                },
                hovertemplate: '<b>%{x}</b><br>Temperatura: %{y:.2f} °C<extra></extra>',
                yaxis: 'y'
            };

            // Trace 2: Línea - Velocidad del Viento (Eje Y2)
            const traceViento = {
                name: 'Velocidad del Viento (m/s)',
                x: datos.labels,
                y: datos.viento_promedio,
                type: 'scatter',
                mode: 'lines+markers',
                line: {
                    color: 'rgb(0, 255, 255)',
                    width: 3,
                    shape: 'spline'
                },
                marker: {
                    size: 8,
                    color: '#00ffff',
                    line: {
                        color: '#ffffff',
                        width: 2
                    },
                    symbol: 'diamond'
                },
                hovertemplate: '<b>%{x}</b><br>Viento: %{y:.2f} m/s<extra></extra>',
                yaxis: 'y2'
            };

            const data = [traceTemperatura, traceViento];

            // ===== LAYOUT CON ESTILO CLIMOGRAMA CLÁSICO =====
            const layout = {
                plot_bgcolor: 'rgba(0, 0, 0, 0.2)',
                paper_bgcolor: 'transparent',
                font: {
                    family: 'Inter, Segoe UI, sans-serif',
                    size: 11,
                    color: 'rgba(255, 255, 255, 0.8)'
                },
                margin: {
                    l: 60,
                    r: 80,
                    t: 40,
                    b: 70
                },

                // Eje X
                xaxis: {
                    title: '',
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.05)',
                    zeroline: false,
                    tickangle: -45,
                    tickfont: {
                        size: 9,
                        color: 'rgba(255, 255, 255, 0.6)'
                    },
                    fixedrange: true
                },

                // Eje Y1: Temperatura (izquierda)
                yaxis: {
                    title: 'Temperatura (°C)',
                    titlefont: { color: '#ff9500' },
                    showgrid: true,
                    gridcolor: 'rgba(255, 255, 255, 0.08)',
                    zeroline: true,
                    zerolinecolor: 'rgba(255, 255, 255, 0.1)',
                    tickfont: {
                        size: 10,
                        color: '#ff9500'
                    },
                    range: [yMinTemp, yMaxTemp],
                    fixedrange: true,
                    autorange: false,
                    side: 'left'
                },

                // Eje Y2: Viento (derecha)
                yaxis2: {
                    title: 'Velocidad del Viento (m/s)',
                    titlefont: { color: '#00ffff' },
                    showgrid: false,
                    zeroline: false,
                    tickfont: {
                        size: 10,
                        color: '#00ffff'
                    },
                    overlaying: 'y',
                    side: 'right',
                    range: [yMinViento, yMaxViento],
                    fixedrange: true
                },

                // Leyenda horizontal abajo
                legend: {
                    orientation: 'h',
                    x: 0.5,
                    xanchor: 'center',
                    y: -0.20,
                    yanchor: 'top',
                    font: {
                        size: 7.5,
                        color: '#ffffff'
                    },
                    bgcolor: 'rgba(0, 0, 0, 0.85)',
                    bordercolor: 'rgba(0, 255, 255, 0.3)',
                    borderwidth: 1,
                    itemsizing: 'constant',
                    itemwidth: 20,
                    tracegroupgap: 3
                },

                hovermode: 'x unified',
                hoverlabel: {
                    bgcolor: 'rgba(10, 14, 26, 0.95)',
                    bordercolor: 'rgba(0, 255, 255, 0.5)',
                    font: {
                        size: 11,
                        color: '#ffffff'
                    }
                }
            };

            // ===== CONFIGURACIÓN (SOLO DOWNLOAD) =====
            const config = {
                responsive: true,
                displayModeBar: true,
                displaylogo: false,
                notifyOnLogging: false,
                queueLength: 0,
                modeBarButtonsToRemove: [
                    'lasso2d',
                    'select2d',
                    'autoScale2d',
                    'resetScale2d',
                    'zoom2d',
                    'pan2d',
                    'zoomIn2d',
                    'zoomOut2d',
                    'hoverClosestCartesian',
                    'hoverCompareCartesian',
                    'toggleSpikelines'
                ],
                modeBarButtonsToAdd: [],
                toImageButtonOptions: {
                    format: 'png',
                    filename: `climograma_l2_${estacion}_${anio}_${mes}`,
                    height: 800,
                    width: 1600,
                    scale: 2
                },
                plotGlPixelRatio: 2
            };

            // ===== CREAR GRÁFICA =====
            await Plotly.newPlot(chartDiv, data, layout, config);

            // ===== MANEJADOR DE DESCARGA CON NOTIFICACIÓN PERSONALIZADA =====
            chartDiv.on('plotly_afterplot', function () {
                hideAllPlotlyNotifications();

                const downloadBtn = document.querySelector('#chartClimogramaL2 .modebar-btn[data-title*="Download"]');

                if (downloadBtn && !downloadBtn.hasAttribute('data-custom-handler-chartClimogramaL2')) {
                    downloadBtn.setAttribute('data-custom-handler-chartClimogramaL2', 'true');

                    downloadBtn.addEventListener('click', async function (e) {
                        e.stopPropagation();
                        hideAllPlotlyNotifications();

                        // Agrandar leyenda temporalmente
                        const layoutParaDescarga = {
                            'legend.font.size': 12,
                            'legend.itemwidth': 40
                        };

                        await Plotly.relayout(chartDiv, layoutParaDescarga);

                        setTimeout(async () => {
                            await Plotly.downloadImage(chartDiv, {
                                format: 'png',
                                filename: `climograma_l2_${estacion}_${anio}_${mes}`,
                                height: 800,
                                width: 1600,
                                scale: 2
                            });

                            // Restaurar leyenda compacta
                            const layoutOriginal = {
                                'legend.font.size': 7.5,
                                'legend.itemwidth': 20
                            };

                            await Plotly.relayout(chartDiv, layoutOriginal);
                            showCustomDownloadNotification(`climograma_l2_${estacion}_${anio}_${mes}.png`);
                        }, 300);
                    });
                }
            });

            charts['chartClimogramaL2'] = chartDiv;

            setTimeout(() => {
                hideAllPlotlyNotifications();
            }, 100);

            console.log('✅ Gráfica Climograma Integral L2 creada con Plotly');

        } catch (error) {
            console.error('❌ Error en Climograma L2:', error);
            mostrarMensajeError('chartClimogramaL2', 'Error al cargar climograma: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA L2: ANÁLISIS DE TENDENCIAS (COMPARATIVO)
    // ================================================================
    async function loadChartAnalisisL2(estacion, anio) {
        // Fallback args
        if (!estacion) estacion = selectedStation;
        if (!anio) anio = document.getElementById('anio')?.value;

        const chartId = 'chartAnalisisL2';
        const chartDiv = document.getElementById(chartId);

        try {
            // Force Clear Overlay
            if (chartDiv) {
                const wrapper = chartDiv.closest('.chart-canvas-wrapper') || chartDiv.parentElement;
                if (wrapper) {
                    const overlay = wrapper.querySelector('.waiting-data-overlay');
                    if (overlay) overlay.remove();
                }
            }

            const url = `../controller/CDashboard.php?action=get_chart_l2_analisis_tendencias&id_estacion=${estacion}&anio=${anio}`;
            const response = await fetch(url);
            const result = await response.json();

            if (!result.exito || !result.datos || (result.datos.actual && result.datos.actual.every(v => v === 0))) {
                mostrarMensajeSinDatos(chartId);
                return;
            }

            const data = result.datos;

            const traceActual = {
                x: data.labels,
                y: data.actual,
                name: `Año ${anio}`,
                type: 'scatter',
                mode: 'lines+markers',
                line: { color: '#00d2ff', width: 3 }
            };

            const traceHistorico = {
                x: data.labels,
                y: data.historico,
                name: 'Promedio Histórico',
                type: 'scatter',
                mode: 'lines',
                line: { color: 'rgba(255, 255, 255, 0.5)', width: 2, dash: 'dot' }
            };

            const layout = {
                title: { text: 'Comparativa Temperatura (°C)', font: { color: '#fff' } },
                paper_bgcolor: 'transparent',
                plot_bgcolor: 'rgba(0,0,0,0.2)',
                xaxis: { color: '#fff' },
                yaxis: { title: 'Temperatura (°C)', color: '#fff' },
                legend: { x: 0.5, xanchor: 'center', orientation: 'h', font: { color: '#fff' } },
                margin: { t: 40, l: 50, r: 20, b: 40 }
            };

            Plotly.newPlot(chartDiv, [traceActual, traceHistorico], layout, { responsive: true, displayModeBar: false });
            charts[chartId] = chartDiv;

        } catch (error) {
            console.error(error);
            mostrarMensajeError(chartId, 'Error al cargar análisis: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA L2: DISTRIBUCIÓN ESTACIONAL
    // ================================================================
    // ================================================================
    // GRÁFICA L2: DISTRIBUCIÓN ESTACIONAL
    // ================================================================
    async function loadChartDistribucionL2(estacion) {
        if (!estacion) estacion = selectedStation;
        if (!estacion) estacion = document.getElementById('estacionSelect')?.value;

        const chartId = 'chartDistribucionL2';
        const chartDiv = document.getElementById(chartId);

        try {
            if (chartDiv) {
                const wrapper = chartDiv.closest('.chart-canvas-wrapper') || chartDiv.parentElement;
                if (wrapper) {
                    const overlay = wrapper.querySelector('.waiting-data-overlay');
                    if (overlay) overlay.remove();
                }
            }

            const url = `../controller/CDashboard.php?action=get_chart_l2_distribucion_estacional&id_estacion=${estacion}`;
            const response = await fetch(url);
            const result = await response.json();

            if (!result.exito || !result.datos) {
                mostrarMensajeSinDatos(chartId);
                return;
            }

            const data = result.datos;
            const traceTemp = {
                x: data.labels,
                y: data.temperatura,
                name: 'Temperatura',
                type: 'bar',
                marker: { color: '#ff7675' }
            };

            const tracePrecip = {
                x: data.labels,
                y: data.precipitacion,
                name: 'Precipitación',
                type: 'bar',
                marker: { color: '#74b9ff' },
                yaxis: 'y2'
            };

            const layout = {
                paper_bgcolor: 'transparent',
                plot_bgcolor: 'rgba(0,0,0,0.2)',
                xaxis: { color: '#fff' },
                yaxis: { title: 'Temperatura (°C)', color: '#ff7675' },
                yaxis2: { title: 'Precipitación (mm)', color: '#74b9ff', overlaying: 'y', side: 'right' },
                legend: { x: 0.5, xanchor: 'center', orientation: 'h', font: { color: '#fff' } },
                margin: { t: 30, l: 50, r: 50, b: 40 }
            };

            Plotly.newPlot(chartDiv, [traceTemp, tracePrecip], layout, { responsive: true, displayModeBar: false });
            charts[chartId] = chartDiv;

        } catch (error) {
            console.error(error);
            mostrarMensajeError(chartId, 'Error: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA L2: ANÁLISIS DE PRECIPITACIONES
    // ================================================================
    async function loadChartPrecipitacionesL2(estacion, anio) {
        if (!estacion) estacion = selectedStation;
        if (!anio) anio = document.getElementById('anio')?.value;

        const chartId = 'chartPrecipitacionesL2';
        const chartDiv = document.getElementById(chartId);

        try {
            if (chartDiv) {
                const wrapper = chartDiv.closest('.chart-canvas-wrapper') || chartDiv.parentElement;
                if (wrapper) {
                    const overlay = wrapper.querySelector('.waiting-data-overlay');
                    if (overlay) overlay.remove();
                }
            }

            const url = `../controller/CDashboard.php?action=get_chart_l2_precipitaciones&id_estacion=${estacion}&anio=${anio}`;
            const response = await fetch(url);
            const result = await response.json();

            if (!result.exito || !result.datos) {
                mostrarMensajeSinDatos(chartId);
                return;
            }

            const data = result.datos;
            const traceLluvia = {
                x: data.labels,
                y: data.precipitacion,
                name: 'Lluvia Total (mm)',
                type: 'bar',
                marker: { color: '#0984e3' }
            };

            const traceDias = {
                x: data.labels,
                y: data.dias_lluvia,
                name: 'Días Lluvia',
                type: 'scatter',
                mode: 'lines+markers',
                yaxis: 'y2',
                line: { color: '#55efc4', width: 2 }
            };

            const layout = {
                paper_bgcolor: 'transparent',
                plot_bgcolor: 'rgba(0,0,0,0.2)',
                xaxis: { color: '#fff' },
                yaxis: { title: 'Lluvia (mm)', color: '#fff' },
                yaxis2: { title: 'Días', color: '#55efc4', overlaying: 'y', side: 'right' },
                legend: { x: 0.5, xanchor: 'center', orientation: 'h', font: { color: '#fff' } },
                margin: { t: 30, l: 50, r: 50, b: 40 }
            };

            Plotly.newPlot(chartDiv, [traceLluvia, traceDias], layout, { responsive: true, displayModeBar: false });
            charts[chartId] = chartDiv;

        } catch (error) {
            console.error(error);
            mostrarMensajeError(chartId, 'Error: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA L2: ÍNDICE DE ARIDEZ
    // ================================================================
    async function loadChartAridezL2(estacion, anio) {
        if (!estacion) estacion = selectedStation;
        if (!anio) anio = document.getElementById('anio')?.value;

        const chartId = 'chartAridezL2';
        const chartDiv = document.getElementById(chartId);

        try {
            if (chartDiv) {
                const wrapper = chartDiv.closest('.chart-canvas-wrapper') || chartDiv.parentElement;
                if (wrapper) {
                    const overlay = wrapper.querySelector('.waiting-data-overlay');
                    if (overlay) overlay.remove();
                }
            }

            const url = `../controller/CDashboard.php?action=get_chart_l2_indice_aridez&id_estacion=${estacion}&anio=${anio}`;
            const response = await fetch(url);
            const result = await response.json();

            if (!result.exito || !result.datos) {
                mostrarMensajeSinDatos(chartId);
                return;
            }

            const data = result.datos;
            const traceAridez = {
                x: data.labels,
                y: data.indice,
                name: 'Índice de Martonne',
                type: 'bar',
                marker: {
                    color: data.indice.map(v => v < 10 ? '#fab1a0' : (v < 20 ? '#ffeaa7' : '#55efc4'))
                }
            };

            const layout = {
                paper_bgcolor: 'transparent',
                plot_bgcolor: 'rgba(0,0,0,0.2)',
                xaxis: { color: '#fff' },
                yaxis: { title: 'Índice', color: '#fff' },
                shapes: [
                    { type: 'line', y0: 10, y1: 10, x0: 0, x1: 1, xref: 'paper', line: { color: 'red', width: 2, dash: 'dot' } },
                    { type: 'line', y0: 20, y1: 20, x0: 0, x1: 1, xref: 'paper', line: { color: 'yellow', width: 2, dash: 'dot' } }
                ],
                margin: { t: 30, l: 50, r: 20, b: 40 }
            };

            Plotly.newPlot(chartDiv, [traceAridez], layout, { responsive: true, displayModeBar: false });
            charts[chartId] = chartDiv;

        } catch (error) {
            console.error(error);
            mostrarMensajeError(chartId, 'Error: ' + error.message);
        }
    }

    // ================================================================
    // GRÁFICA L2: ANÁLISIS DE HELADAS
    // ================================================================
    async function loadChartHeladasL2(estacion, anio) {
        if (!estacion) estacion = selectedStation;
        if (!anio) anio = document.getElementById('anio')?.value;

        const chartId = 'chartHeladasL2';
        const chartDiv = document.getElementById(chartId);

        try {
            if (chartDiv) {
                const wrapper = chartDiv.closest('.chart-canvas-wrapper') || chartDiv.parentElement;
                if (wrapper) {
                    const overlay = wrapper.querySelector('.waiting-data-overlay');
                    if (overlay) overlay.remove();
                }
            }

            const url = `../controller/CDashboard.php?action=get_chart_l2_analisis_heladas&id_estacion=${estacion}&anio=${anio}`;
            const response = await fetch(url);
            const result = await response.json();

            if (!result.exito || !result.datos) {
                mostrarMensajeSinDatos(chartId);
                return;
            }

            const data = result.datos;
            const traceDias = {
                x: data.labels,
                y: data.dias_helada,
                name: 'Días con Helada',
                type: 'bar',
                marker: { color: '#74b9ff' }
            };

            const traceMin = {
                x: data.labels,
                y: data.temp_minima,
                name: 'Min Absoluta (°C)',
                type: 'scatter',
                mode: 'markers',
                yaxis: 'y2',
                marker: { color: '#ff7675', size: 8 }
            };

            const layout = {
                paper_bgcolor: 'transparent',
                plot_bgcolor: 'rgba(0,0,0,0.2)',
                xaxis: { color: '#fff' },
                yaxis: { title: 'Días Helada', color: '#fff' },
                yaxis2: { title: 'Temp Min (°C)', color: '#ff7675', overlaying: 'y', side: 'right' },
                legend: { x: 0.5, xanchor: 'center', orientation: 'h', font: { color: '#fff' } },
                margin: { t: 30, l: 50, r: 50, b: 40 }
            };

            Plotly.newPlot(chartDiv, [traceDias, traceMin], layout, { responsive: true, displayModeBar: false });
            charts[chartId] = chartDiv;

        } catch (error) {
            console.error(error);
            mostrarMensajeError(chartId, 'Error: ' + error.message);
        }
    }

    function mostrarMensajeSinDatos(chartId) {
        console.log('📢 Mostrando mensaje "Sin Datos" en', chartId);

        // Destruir gráfica Plotly si existe
        if (charts[chartId]) {
            try {
                Plotly.purge(chartId);
            } catch (e) {
                console.log('No hay gráfica Plotly para destruir');
            }
            delete charts[chartId];
        }

        const chartDiv = document.getElementById(chartId);
        if (chartDiv) {
            const wrapper = chartDiv.closest('.chart-canvas-wrapper');
            if (wrapper) {
                // Remover overlay anterior
                removeWaitingOverlay(chartId);

                // Limpiar el div
                chartDiv.innerHTML = '';

                // Agregar nuevo overlay
                const overlay = document.createElement('div');
                overlay.className = 'waiting-data-overlay';
                overlay.innerHTML = `
                <div class="waiting-data-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <p>Sin Datos</p>
                    <p class="subtitle">No hay registros para los filtros seleccionados</p>
                </div>
            `;
                wrapper.appendChild(overlay);
            }
        }
    }

    function mostrarMensajeError(chartId, mensaje) {
        console.error('⚠️ Mostrando error en', chartId, ':', mensaje);

        // Destruir gráfica Plotly si existe
        if (charts[chartId]) {
            try {
                Plotly.purge(chartId);
            } catch (e) {
                console.log('No hay gráfica Plotly para destruir');
            }
            delete charts[chartId];
        }

        const chartDiv = document.getElementById(chartId);
        if (chartDiv) {
            const wrapper = chartDiv.closest('.chart-canvas-wrapper');
            if (wrapper) {
                // Remover overlay anterior
                removeWaitingOverlay(chartId);

                // Limpiar el div
                chartDiv.innerHTML = '';

                // Agregar nuevo overlay de error
                const overlay = document.createElement('div');
                overlay.className = 'waiting-data-overlay';
                overlay.innerHTML = `
                <div class="waiting-data-message">
                    <i class="fas fa-exclamation-triangle" style="color: #ff073a;"></i>
                    <p style="color: #ff073a;">Error</p>
                    <p class="subtitle">${mensaje}</p>
                </div>
            `;
                wrapper.appendChild(overlay);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        console.log('🚀 Inicializando Dashboard Meteorológico...');

        // Cargar datos iniciales desde la BD
        loadStationsFromDatabase();
        loadGeneralStats();
        showWaitingDataInCharts();

        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.addedNodes.length) {
                    mutation.addedNodes.forEach(function (node) {
                        if (node.nodeType === 1) { // Elemento HTML
                            const text = node.textContent || '';
                            if (text.toLowerCase().includes('snapshot') ||
                                text.toLowerCase().includes('taking')) {
                                hideAllPlotlyNotifications();
                            }
                        }
                    });
                }
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        window.addEventListener('beforeunload', () => {
            stopCarousel();
            observer.disconnect();
        });
    });

    // ===== FUNCIONES PARA D  ESCARGA PDF =====
    function openDownloadModal() {
        const modal = document.getElementById('downloadModal');
        document.getElementById('downloadForm').reset();
        clearFormErrors();
        document.getElementById('btnSubmitDownload').disabled = true;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function closeDownloadModal() {
        const modal = document.getElementById('downloadModal');
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    function clearFormErrors() {
        document.querySelectorAll('.form-error').forEach(error => error.classList.remove('active'));
        document.querySelectorAll('.form-input').forEach(input => input.classList.remove('error'));
    }
    function showFieldError(errorId, show) {
        const errorElement = document.getElementById(errorId);
        const inputId = errorId.replace('error', '').charAt(0).toLowerCase() + errorId.replace('error', '').slice(1);
        const inputElement = document.getElementById(inputId === 'nombre' ? 'nombreCompleto' : inputId);
        if (show) {
            errorElement.classList.add('active');
            inputElement.classList.add('error');
        } else {
            errorElement.classList.remove('active');
            inputElement.classList.remove('error');
        }
    }
    function validateForm() {
        const nombre = document.getElementById('nombreCompleto').value.trim();
        const institucion = document.getElementById('institucion').value.trim();
        const cedula = document.getElementById('cedula').value.trim();
        const motivo = document.getElementById('motivo').value.trim();
        const terms = document.getElementById('acceptTerms').checked;
        const btnSubmit = document.getElementById('btnSubmitDownload');
        const nombreValido = nombre.length >= 3 && /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/.test(nombre);
        const institucionValida = institucion.length >= 2 && /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]+$/.test(institucion);
        const cedulaValida = cedula.length === 10 && /^[0-9]{10}$/.test(cedula);
        const motivoValido = motivo.length >= 5;
        if (nombreValido && institucionValida && cedulaValida && motivoValido && terms) {
            btnSubmit.disabled = false;
        } else {
            btnSubmit.disabled = true;
        }
    }
    async function generatePDF() {
        const nombre = document.getElementById('nombreCompleto').value.trim();
        const institucion = document.getElementById('institucion').value.trim();
        const cedula = document.getElementById('cedula').value.trim();
        const motivo = document.getElementById('motivo').value.trim();
        // Validación de datos
        if (nombre.length < 3) {
            alert('❌ ERROR: Por favor, ingrese un nombre válido');
            return;
        }

        closeDownloadModal();

        // Mostrar notificación de progreso
        const progressNotification = document.createElement('div');
        progressNotification.className = 'custom-download-notification';
        progressNotification.innerHTML = `
            <div class="custom-download-notification-content">
                <i class="fas fa-spinner fa-spin custom-download-notification-icon"></i>
                <div class="custom-download-notification-text">
                    Generando PDF...
                </div>
                <div class="custom-download-notification-subtext">
                    Por favor espere, esto puede tardar unos segundos
                </div>
            </div>
        `;
        document.body.appendChild(progressNotification);

        try {
            // Verificar que las librerías estén cargadas
            if (typeof html2canvas === 'undefined') {
                throw new Error('html2canvas no está cargado');
            }

            if (typeof window.jspdf === 'undefined') {
                throw new Error('jsPDF no está cargado');
            }

            // Obtener el contenedor principal del dashboard
            const dashboardContainer = document.querySelector('.dashboard-container');
            if (!dashboardContainer) {
                throw new Error('No se encontró el contenedor del dashboard');
            }

            console.log('📸 Capturando contenido del dashboard...');

            // Esperar un momento para que las gráficas de Plotly terminen de renderizar
            await new Promise(resolve => setTimeout(resolve, 500));

            // Capturar con html2canvas
            const canvas = await html2canvas(dashboardContainer, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#0a0e1a',
                windowWidth: dashboardContainer.scrollWidth,
                windowHeight: dashboardContainer.scrollHeight,
                onclone: (clonedDoc) => {
                    // Asegurar que las gráficas de Plotly se capturen correctamente
                    const plotlyDivs = clonedDoc.querySelectorAll('[id*="chart"]');
                    plotlyDivs.forEach(div => {
                        if (div.style) {
                            div.style.opacity = '1';
                        }
                    });
                }
            });

            console.log('✅ Contenido capturado, generando PDF...');

            // Convertir canvas a imagen
            const imgData = canvas.toDataURL('image/png');

            // Crear PDF con jsPDF
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('p', 'mm', 'a4');

            const pageWidth = 210; // A4 width in mm
            const pageHeight = 297; // A4 height in mm
            const imgWidth = pageWidth;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            let heightLeft = imgHeight;
            let position = 0;

            // Agregar primera página
            pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;

            // Agregar páginas adicionales si es necesario
            while (heightLeft > 0) {
                position = heightLeft - imgHeight;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }

            // Generar nombre de archivo
            const estacionNombre = selectedStation || 'Dashboard';
            const nivel = currentLevel || 'L0';
            const fecha = new Date().toISOString().split('T')[0];
            const hora = new Date().toTimeString().split(' ')[0].replace(/:/g, '-');
            const filename = `Reporte_Dashboard_${nivel}_${estacionNombre}_${fecha}_${hora}.pdf`;

            console.log('💾 Guardando PDF:', filename);

            // Descargar PDF
            pdf.save(filename);

            // Registrar descarga en consola (opcional: enviar a backend)
            console.log('📝 Datos de descarga:', {
                nombre,
                institucion,
                cedula,
                motivo,
                estacion: estacionNombre,
                nivel,
                fecha,
                hora
            });

            // Remover notificación de progreso
            progressNotification.remove();

            // Mostrar mensaje de éxito simple
            alert(`✅ PDF descargado exitosamente:\n\n${filename}\n\nRevisa tu carpeta de descargas.`);

            console.log('✅ PDF generado exitosamente');

        } catch (error) {
            // Remover notificación de progreso
            if (progressNotification && progressNotification.parentNode) {
                progressNotification.remove();
            }

            console.error('❌ Error al generar PDF:', error);
            console.error('Detalles del error:', error.message, error.stack);

            alert(`❌ ERROR: No se pudo completar la descarga. Por favor, intente nuevamente.\n\nDetalles técnicos: ${error.message}`);
        }
    }
    // Event listeners para validación en tiempo real
    document.addEventListener('DOMContentLoaded', function () {
        const nombreInput = document.getElementById('nombreCompleto');
        if (nombreInput) {
            nombreInput.addEventListener('input', function (e) {
                const valor = e.target.value;
                const soloLetras = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]*$/;
                if (!soloLetras.test(valor)) {
                    e.target.value = valor.slice(0, -1);
                    showFieldError('errorNombre', true);
                } else {
                    showFieldError('errorNombre', false);
                }
                validateForm();
            });
        }
        const institucionInput = document.getElementById('institucion');
        if (institucionInput) {
            institucionInput.addEventListener('input', function (e) {
                const valor = e.target.value;
                const alphanumeric = /^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s]*$/;
                if (!alphanumeric.test(valor)) {
                    e.target.value = valor.slice(0, -1);
                    showFieldError('errorInstitucion', true);
                } else {
                    showFieldError('errorInstitucion', false);
                }
                validateForm();
            });
        }
        const cedulaInput = document.getElementById('cedula');
        if (cedulaInput) {
            cedulaInput.addEventListener('input', function (e) {
                const valor = e.target.value;
                const soloNumeros = /^[0-9]*$/;
                if (!soloNumeros.test(valor)) {
                    e.target.value = valor.slice(0, -1);
                }
                if (valor.length > 0 && valor.length !== 10) {
                    showFieldError('errorCedula', true);
                } else {
                    showFieldError('errorCedula', false);
                }
                validateForm();
            });
        }
        const motivoInput = document.getElementById('motivo');
        if (motivoInput) {
            motivoInput.addEventListener('input', validateForm);
        }
        const termsCheckbox = document.getElementById('acceptTerms');
        if (termsCheckbox) {
            termsCheckbox.addEventListener('change', validateForm);
        }
    });



</script>


<?php include('../views/Administrador/footerAdmin.php'); ?>