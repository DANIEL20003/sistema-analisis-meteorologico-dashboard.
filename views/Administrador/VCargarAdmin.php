<?php
$titulo_pagina = 'Cargar Datos - Sistema de Análisis Meteorológico - ESPOCH';
$pagina_activa = 'cargar';

include('headerAdmin.php');
?>

<style>
    /* ===== VARIABLES DE COLORES PROFESIONALES (COPIADAS DE VDASHBOARD) ===== */
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

    /* ===== FONDO FIJO CHIMBORAZO (COPIADO DE VDASHBOARD) ===== */
    .cargar-page-wrapper {
        position: relative;
        min-height: auto;
    }

    .cargar-fixed-background {
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

    .cargar-fixed-background::before {
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
    .cargar-container {
        position: relative;
        z-index: 3;
        max-width: 1400px;
        margin: 0 auto;
        padding: 1rem;
        min-height: auto;
    }

    /* ===== HEADER DE LA PÁGINA ===== */
    .cargar-header {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 15px;
        padding: 2rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-glow);
        margin-bottom: 2rem;
        text-align: center;
    }

    .cargar-title {
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

    .cargar-subtitle {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin: 0;
    }

    /* ===== FORMULARIO DE CARGA ===== */
    .cargar-form {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 15px;
        padding: 1.5rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-card);
        margin-bottom: 1rem;
    }

    /* ===== TÍTULO PRINCIPAL DEL FORMULARIO ===== */
    .form-main-header {
        text-align: center;
        margin-bottom: 2rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .form-main-title {
        font-size: 1.26rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-transform: uppercase;
        letter-spacing: 1.35px;
        margin: 0;
        position: relative;
        display: inline-block;
    }

    .form-main-title::after {
        content: '';
        position: absolute;
        bottom: -10px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-cyan), var(--primary-blue));
        border-radius: 2px;
    }

    /* ===== LAYOUT COMPACTO CON FILTROS A LA IZQUIERDA ===== */
    .main-layout {
        display: grid;
        grid-template-columns: 400px 1fr;
        gap: 1.5rem;
        min-height: auto;
    }

    .filters-column {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .content-column {
        display: flex;
        flex-direction: column;
        min-height: auto;
    }

    .form-section {
        margin-bottom: 1rem;
    }

    .form-section-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .form-section-title i {
        color: var(--primary-cyan);
        font-size: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
    }

    .form-label {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.3rem;
    }

    .form-label .required {
        color: var(--accent-red);
        margin-left: 0.2rem;
    }

    .form-input,
    .form-select {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        padding: 0.8rem;
        color: var(--text-primary);
        font-size: 0.8rem;
        transition: all 0.3s ease;
        font-family: inherit;
    }

    .form-input:focus,
    .form-select:focus {
        outline: none;
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.1);
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
    }

    .form-input option,
    .form-select option {
        background: var(--bg-secondary);
        color: var(--text-primary);
        padding: 0.5rem;
    }

    /* ===== SELECTOR DE NIVEL (ESTILO VDASHBOARD) ===== */
    .level-selector-container {
        margin-bottom: 0;
    }

    .level-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.3rem;
    }

    .level-btn {
        padding: 0.6rem 0.3rem;
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.75rem;
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

    /* ===== SELECTOR DE ARCHIVO ===== */
    .file-upload-section {
        background: var(--bg-secondary);
        border: 1px solid var(--border-primary);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .file-type-selector {
        display: grid;
        grid-template-columns: 1fr;
        /* Solo un botón */
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .file-type-btn {
        padding: 0.8rem;
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .file-type-btn:hover {
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.1);
    }

    .file-type-btn.active {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        color: #0a0e1a;
        border-color: transparent;
    }

    .file-type-btn.csv-only.active {
        background: linear-gradient(135deg, var(--accent-green), var(--primary-cyan));
    }

    /* ===== ZONA DE ARRASTRE ===== */
    .dropzone {
        background: var(--bg-card);
        border: 2px dashed var(--border-primary);
        border-radius: 12px;
        padding: 1.5rem;
        /* Reducido de 2rem */
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        min-height: 140px;
        /* Reducido de 180px */
        max-height: 140px;
        /* NUEVO - Altura fija */
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .dropzone.file-loaded {
        background: rgba(0, 255, 136, 0.1);
        border: 2px solid var(--accent-green);
        cursor: default;
        min-height: 140px;
        /* Mismo tamaño */
        max-height: 140px;
        /* NUEVO */
    }

    .dropzone-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
    }


    .dropzone.file-loaded .dropzone-content {
        pointer-events: none;
        opacity: 0.5;
    }

    .dropzone.file-loaded:hover {
        border-color: var(--accent-green);
        background: rgba(0, 255, 136, 0.1);
        transform: none;
    }

    .dropzone.disabled {
        background: rgba(255, 255, 255, 0.03);
        border: 2px dashed var(--border-secondary);
        cursor: not-allowed;
        opacity: 0.5;
        min-height: 140px;
        /* Reducido y consistente */
        max-height: 140px;
        /* NUEVO */
        padding: 1rem;
        /* Reducido */
    }

    .dropzone.disabled:hover {
        border-color: var(--border-secondary);
        background: rgba(255, 255, 255, 0.03);
        transform: none;
    }

    .dropzone:hover {
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.05);
        transform: translateY(-2px);
    }

    .dropzone.dragover {
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.1);
        transform: scale(1.02);
    }

    .dropzone-icon {
        font-size: 1.8rem;
        /* Reducido de 2rem */
        color: var(--primary-cyan);
        margin-bottom: 0.5rem;
        /* Reducido de 0.8rem */
    }

    .dropzone-text {
        font-size: 0.85rem;
        /* Reducido de 0.9rem */
        color: var(--text-primary);
        margin-bottom: 0.3rem;
        /* Reducido de 0.5rem */
        font-weight: 600;
    }

    .dropzone-subtext {
        font-size: 0.7rem;
        /* Reducido de 0.75rem */
        color: var(--text-muted);
    }

    /* ===== ESTADO DE ARCHIVO CARGADO ===== */
    .dropzone.file-loaded {
        cursor: not-allowed !important;
        pointer-events: none;
    }

    .dropzone.file-loaded:hover {
        border-color: var(--accent-green);
        background: rgba(0, 255, 136, 0.1);
        transform: none;
    }

    .file-info {
        background: rgba(0, 255, 255, 0.1);
        border: 1px solid var(--primary-cyan);
        border-radius: 8px;
        padding: 0.8rem;
        /* Reducido de 1rem */
        margin: 0;
        /* Sin margen extra */
        text-align: left;
        width: 100%;
        max-height: 140px;
        /* NUEVO - Mismo tamaño que dropzone */
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .file-details {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.8rem;
    }

    .file-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-primary);
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 70%;
    }

    .file-size {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .remove-file-btn {
        background: var(--accent-red);
        border: none;
        border-radius: 6px;
        color: white;
        padding: 0.4rem 0.8rem;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.3rem;
        pointer-events: auto;
        position: relative;
        z-index: 10;
    }

    .remove-file-btn:hover {
        background: #ff1a47;
        transform: translateY(-1px);
    }

    /* ===== BARRA DE PROGRESO ===== */
    .progress-container {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 6px;
        height: 6px;
        margin: 0.8rem 0;
        overflow: hidden;
    }

    .progress-bar {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        height: 100%;
        border-radius: 6px;
        transition: width 0.3s ease;
        width: 0%;
    }

    .progress-bar.complete {
        background: linear-gradient(135deg, var(--accent-green), var(--primary-cyan));
    }

    .progress-text {
        font-size: 0.75rem;
        color: var(--text-muted);
        text-align: center;
        margin-top: 0.3rem;
    }

    /* ===== INFORMACIÓN DE VALIDACIÓN ===== */
    .validation-info {
        background: rgba(0, 255, 255, 0.05);
        border: 1px solid var(--border-primary);
        border-radius: 10px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .validation-title {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--primary-cyan);
        margin-bottom: 0.8rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .validation-rules {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.8rem;
    }

    .validation-rule {
        font-size: 0.7rem;
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .validation-rule i {
        color: var(--accent-green);
        font-size: 0.6rem;
    }

    /* ===== BOTONES DE ACCIÓN ===== */
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 1.2rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-secondary);
    }

    .btn-form {
        padding: 1rem 2rem;
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
        gap: 0.5rem;
        min-width: 150px;
        justify-content: center;
    }

    .btn-submit {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        color: #0a0e1a;
        box-shadow: 0 4px 15px rgba(0, 255, 255, 0.3);
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 255, 255, 0.4);
    }

    .btn-submit:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
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

    /* ===== TÍTULO DE ACCIONES ===== */
    .actions-header {
        text-align: center;
        margin-top: 1.5rem;
        margin-bottom: 0.8rem;
        /* Reducido para menos espacio */
        padding-top: 1.5rem;
        border-top: 1px solid var(--border-secondary);
    }

    .actions-title {
        font-size: 1rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 0;
    }

    /* ===== BOTONES DE ACCIÓN ===== */
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 0.8rem;
        /* Reducido de 2rem */
        padding-top: 0;
        /* Quitado el padding-top */
        border-top: none;
        /* Quitado el borde superior */
    }

    /* ===== MENSAJES DE ESTADO ===== */
    .status-message {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        display: none;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-message.success {
        background: rgba(0, 255, 136, 0.1);
        border: 1px solid var(--accent-green);
        color: var(--accent-green);
    }

    .status-message.error {
        background: rgba(255, 7, 58, 0.1);
        border: 1px solid var(--accent-red);
        color: var(--accent-red);
    }

    .status-message.warning {
        background: rgba(255, 149, 0, 0.1);
        border: 1px solid var(--accent-orange);
        color: var(--accent-orange);
    }

    .status-message.info {
        background: rgba(0, 255, 255, 0.1);
        border: 1px solid var(--primary-cyan);
        color: var(--primary-cyan);
    }

    /* ===== MODAL DE CONFIRMACIÓN ===== */
    .confirmation-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(10, 14, 26, 0.97);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 100000;
        backdrop-filter: blur(12px);
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

    .confirmation-content {
        background: var(--bg-primary);
        border: 2px solid var(--border-primary);
        border-radius: 15px;
        padding: 1.8rem 2rem;
        max-width: 750px;
        width: 90%;
        text-align: left;
        box-shadow: 0 20px 60px rgba(0, 255, 255, 0.3);
        animation: slideUp 0.4s ease;
        position: relative;
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

    .confirmation-content::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-cyan), var(--primary-blue), var(--accent-green));
        animation: shimmer 2s linear infinite;
    }

    @keyframes shimmer {
        0% {
            transform: translateX(-100%);
        }

        100% {
            transform: translateX(100%);
        }
    }

    .confirmation-icon {
        width: 45px;
        height: 45px;
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: #0a0e1a;
        box-shadow: 0 4px 15px rgba(0, 255, 255, 0.4);
    }

    .confirmation-title {
        font-size: 1.1rem;
        font-weight: 600;
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        margin-bottom: 1.2rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding-right: 60px;
    }

    .confirmation-details {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 12px;
        padding: 1.2rem;
        margin: 1rem 0;
        text-align: left;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .confirmation-detail-row {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 0.8rem;
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.03);
    }

    .confirmation-detail-label {
        font-size: 0.7rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.4rem;
        margin-bottom: 0.5rem;
    }

    .confirmation-detail-label i {
        color: var(--primary-cyan);
        font-size: 0.85rem;
    }

    .confirmation-detail-value {
        font-size: 0.85rem;
        color: var(--text-primary);
        font-weight: 400;
        text-align: left;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 100%;
    }

    .confirmation-warning {
        background: rgba(255, 149, 0, 0.1);
        border: 1px solid var(--accent-orange);
        border-radius: 10px;
        padding: 0.9rem;
        margin: 1rem 0;
        font-size: 0.75rem;
        color: var(--accent-orange);
        display: flex;
        align-items: center;
        gap: 0.7rem;
        font-weight: 400;
    }

    .confirmation-warning i {
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .confirmation-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 1.5rem;
    }

    .confirmation-btn {
        padding: 0.85rem 2rem;
        border: none;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 140px;
        justify-content: center;
    }

    .confirmation-btn-accept {
        background: linear-gradient(135deg, var(--accent-green), var(--primary-cyan));
        color: #0a0e1a;
        box-shadow: 0 6px 20px rgba(0, 255, 136, 0.4);
    }

    .confirmation-btn-accept:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 255, 136, 0.5);
    }

    .confirmation-btn-cancel {
        background: var(--bg-card);
        color: var(--text-primary);
        border: 2px solid var(--accent-red);
    }

    .confirmation-btn-cancel:hover {
        background: rgba(255, 7, 58, 0.2);
        border-color: var(--accent-red);
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 7, 58, 0.3);
    }

    /* ===== MODAL DE PROGRESO ===== */
    .loading-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(10, 14, 26, 0.95);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 100000;
        backdrop-filter: blur(10px);
    }

    .loading-content {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 15px;
        padding: 2rem;
        max-width: 500px;
        width: 90%;
        text-align: center;
        box-shadow: var(--shadow-glow);
    }

    .loading-spinner {
        width: 60px;
        height: 60px;
        border: 4px solid var(--bg-card);
        border-top: 4px solid var(--primary-cyan);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin: 0 auto 1.5rem;
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .loading-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 1rem;
    }

    .loading-progress-container {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
        height: 8px;
        margin: 1rem 0;
        overflow: hidden;
    }

    .loading-progress-bar {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        height: 100%;
        border-radius: 8px;
        transition: width 0.3s ease;
        width: 0%;
    }

    .loading-progress-text {
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin-bottom: 0.5rem;
    }

    .loading-stats {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-top: 1.5rem;
    }

    .loading-stat {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
    }

    .loading-stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-cyan);
        display: block;
    }

    .loading-stat-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .loading-close-btn {
        background: var(--accent-red);
        border: none;
        border-radius: 8px;
        color: white;
        padding: 0.8rem 1.5rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 1rem;
    }

    .loading-close-btn:hover {
        background: #ff1a47;
        transform: translateY(-2px);
    }

    .loading-close-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }

    /* ===== SISTEMA DE NOTIFICACIONES FLOTANTES ===== */
    .notification-container {
        position: fixed;
        top: 140px;
        right: 20px;
        z-index: 100001;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        max-width: 400px;
        pointer-events: none;
    }

    .notification-toast {
        background: var(--bg-primary);
        border: 2px solid var(--border-primary);
        border-radius: 12px;
        padding: 1rem 1.2rem;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(20px);
        display: flex;
        align-items: flex-start;
        gap: 0.8rem;
        opacity: 0;
        transform: translateX(400px);
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        pointer-events: auto;
        position: relative;
        overflow: hidden;
    }

    .notification-toast::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-cyan), var(--primary-blue));
    }

    .notification-toast.show {
        opacity: 1;
        transform: translateX(0);
    }

    .notification-toast.hide {
        opacity: 0;
        transform: translateX(400px);
    }

    .notification-toast.success::before {
        background: linear-gradient(90deg, var(--accent-green), var(--primary-cyan));
    }

    .notification-toast.error::before {
        background: linear-gradient(90deg, var(--accent-red), var(--accent-orange));
    }

    .notification-icon {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .notification-toast.success .notification-icon {
        background: rgba(0, 255, 136, 0.2);
        color: var(--accent-green);
    }

    .notification-toast.error .notification-icon {
        background: rgba(255, 7, 58, 0.2);
        color: var(--accent-red);
    }

    .notification-content {
        flex: 1;
    }

    .notification-title {
        font-size: 0.75rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.2rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .notification-message {
        font-size: 0.7rem;
        color: var(--text-secondary);
        line-height: 1.3;
        word-wrap: break-word;
    }

    .notification-close {
        width: 20px;
        height: 20px;
        border: none;
        background: var(--bg-card);
        border-radius: 50%;
        color: var(--text-muted);
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        flex-shrink: 0;
    }

    .notification-close:hover {
        background: var(--accent-red);
        color: white;
        transform: rotate(90deg);
    }

    .notification-progress {
        position: absolute;
        bottom: 0;
        left: 0;
        height: 2px;
        background: rgba(255, 255, 255, 0.3);
        width: 100%;
        animation: notificationProgress 5s linear;
    }

    @keyframes notificationProgress {
        from {
            width: 100%;
        }

        to {
            width: 0%;
        }
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .main-layout {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .filters-column {
            order: 2;
        }

        .content-column {
            order: 1;
        }
    }

    @media (max-width: 768px) {
        .cargar-container {
            padding: 0.8rem;
        }

        .actions-header {
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            padding-top: 1rem;
        }

        .form-main-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
        }

        .form-main-title {
            font-size: 0.99rem;
            letter-spacing: 0.9px;
        }

        .form-actions {
            margin-top: 1rem;
            padding-top: 0.8rem;
        }

        .actions-title {
            font-size: 0.9rem;
        }

        .form-actions {
            flex-direction: column;
            margin-top: 0.5rem;
        }

        .cargar-form {
            padding: 1rem;
        }

        .level-buttons {
            grid-template-columns: 1fr;
        }

        .file-type-selector {
            grid-template-columns: 1fr;
        }

        .validation-rules {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-form {
            width: 100%;
        }

        .file-info {
            max-height: 120px;
        }

        .dropzone {
            padding: 1rem;
            min-height: 120px;
            /* Reducido */
            max-height: 120px;
            /* NUEVO */
        }

        .dropzone-icon {
            font-size: 1.5rem;
            margin-bottom: 0.4rem;
        }

        .confirmation-content {
            padding: 2rem 1.5rem;
        }

        .confirmation-actions {
            flex-direction: column;
        }

        .dropzone.file-loaded,
        .dropzone.disabled {
            min-height: 120px;
            max-height: 120px;
        }

        .confirmation-btn {
            width: 100%;
        }
    }

    /* ===== ANIMACIONES ===== */
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

    .form-section {
        animation: fadeInUp 0.6s ease-out;
    }

    /* ===== SCROLLBAR PERSONALIZADO (COPIADO DE VDASHBOARD) ===== */
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

    .file-type-btn.xlsx-only.active {
        background: linear-gradient(135deg, #10b981, #059669);
    }
</style>

<!-- Fondo fijo del Chimborazo -->
<div class="cargar-fixed-background"></div>

<div class="cargar-page-wrapper">
    <!-- Contenedor de notificaciones FUERA del cargar-container -->
    <div id="notificationContainer" class="notification-container"></div>

    <div class="cargar-container">
        <!-- Formulario de carga -->
        <form class="cargar-form" id="cargarForm" enctype="multipart/form-data">

            <!-- Mensajes de estado -->
            <div id="statusMessage" class="status-message"></div>

            <!-- Título principal del formulario -->
            <div class="form-main-header">
                <h2 class="form-main-title">Cargar Datos</h2>
            </div>

            <!-- Layout compacto con filtros a la izquierda -->
            <div class="main-layout">

                <!-- Columna de filtros (izquierda) -->
                <div class="filters-column">

                    <!-- Selección de Estación -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Estación Meteorológica
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="estacion">
                                Seleccionar <span class="required">*</span>
                            </label>
                            <select class="form-select" id="estacion" name="estacion" required>
                                <option value="">Cargando estaciones...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Selector de Nivel -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-layer-group"></i>
                            Nivel de Datos
                        </div>
                        <div class="level-selector-container">
                            <div class="level-buttons" id="nivelSelector">
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
                            <input type="hidden" id="nivel" name="nivel" value="L0" required>
                        </div>
                    </div>

                    <!-- Selector de Tipo de Archivo -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-file-alt"></i>
                            Tipo de Archivo
                        </div>

                        <div class="file-type-selector">
                            <button type="button" class="file-type-btn csv-only active" data-tipo="csv" id="btnCSV">
                                <i class="fas fa-file-csv"></i>
                                <span>CSV</span>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Columna de contenido (derecha) -->
                <div class="content-column">

                    <!-- Zona de arrastre -->
                    <div class="form-section">
                        <div class="dropzone" id="dropzone">
                            <div class="dropzone-content" id="dropzoneContent">
                                <div class="dropzone-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>

                                <div class="dropzone-text">Haz clic aquí para seleccionar tu archivo </div>
                                <div> - </div>
                                <div class="dropzone-subtext" id="dropzoneSubtext">
                                    Selecciona un archivo CSV
                                </div>

                                <!-- Barra de progreso (oculta por defecto) -->
                                <div class="progress-container" id="progressContainer" style="display: none;">
                                    <div class="progress-bar" id="progressBar"></div>
                                </div>
                                <div class="progress-text" id="progressText" style="display: none;">
                                    Subiendo archivo...
                                </div>
                            </div>

                            <!-- Información del archivo (oculta por defecto) -->
                            <div class="file-info" id="fileInfo" style="display: none;">
                                <div class="file-details">
                                    <div class="file-name" id="fileName">archivo.csv</div>
                                    <div class="file-size" id="fileSize">0 KB</div>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar complete" style="width: 100%;"></div>
                                </div>
                                <div class="progress-text">
                                    <i class="fas fa-check-circle"
                                        style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                                    Archivo listo para cargar
                                </div>
                                <button type="button" class="remove-file-btn" id="removeFileBtn">
                                    <i class="fas fa-trash"></i>
                                    Eliminar archivo
                                </button>
                            </div>

                            <input type="file" id="archivo" name="archivo" style="display: none;" accept=".csv">
                        </div>
                    </div>

                    <!-- Información de validación -->
                    <div class="form-section">
                        <div class="validation-info" id="validationInfo">
                            <div class="validation-title">
                                <i class="fas fa-info-circle"></i>
                                Requisitos del Archivo - Nivel L0
                            </div>
                            <div class="validation-rules" id="validationRules">
                                <!-- Se llenan dinámicamente según el nivel -->
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="submit" class="btn-form btn-submit" id="btnSubmit">
                    <i class="fas fa-upload"></i>
                    Cargar Datos
                </button>
                <button type="button" class="btn-form btn-reset" id="btnReset">
                    <i class="fas fa-undo"></i>
                    Limpiar
                </button>
            </div>
        </form>

        <!-- Modal de confirmación -->
        <div class="confirmation-modal" id="confirmationModal">
            <div class="confirmation-content">
                <div class="confirmation-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="confirmation-title">Confirmar Carga de Datos</div>

                <div class="confirmation-details">
                    <div class="confirmation-detail-row">
                        <div class="confirmation-detail-label">
                            <i class="fas fa-file"></i>
                            Archivo
                        </div>
                        <div class="confirmation-detail-value" id="confirmFileName">archivo.csv</div>
                    </div>
                    <div class="confirmation-detail-row">
                        <div class="confirmation-detail-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Estación
                        </div>
                        <div class="confirmation-detail-value" id="confirmStation">ESPOCH</div>
                    </div>
                    <div class="confirmation-detail-row">
                        <div class="confirmation-detail-label">
                            <i class="fas fa-layer-group"></i>
                            Nivel de Datos
                        </div>
                        <div class="confirmation-detail-value" id="confirmLevel">L0</div>
                    </div>
                    <div class="confirmation-detail-row">
                        <div class="confirmation-detail-label">
                            <i class="fas fa-database"></i>
                            Tamaño
                        </div>
                        <div class="confirmation-detail-value" id="confirmFileSize">0 KB</div>
                    </div>
                </div>

                <div class="confirmation-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>¡Atención!</strong> Esta acción cargará los datos en la base de datos. Asegúrate de que
                        la información sea correcta.
                    </div>
                </div>

                <div class="confirmation-actions">
                    <button class="confirmation-btn confirmation-btn-accept" id="confirmAcceptBtn">
                        <i class="fas fa-check-circle"></i>
                        Aceptar
                    </button>
                    <button class="confirmation-btn confirmation-btn-cancel" id="confirmCancelBtn">
                        <i class="fas fa-times-circle"></i>
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de carga con progreso -->
        <div class="loading-modal" id="loadingModal">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-title" id="loadingTitle">Procesando archivo...</div>
                <div class="loading-progress-text" id="loadingProgressText">Iniciando carga de datos</div>
                <div class="loading-progress-container">
                    <div class="loading-progress-bar" id="loadingProgressBar"></div>
                </div>
                <div class="loading-stats" id="loadingStats" style="display: none;">
                    <div class="loading-stat">
                        <span class="loading-stat-value" id="totalRows">0</span>
                        <div class="loading-stat-label">Total Filas</div>
                    </div>
                    <div class="loading-stat">
                        <span class="loading-stat-value" id="processedRows">0</span>
                        <div class="loading-stat-label">Procesadas</div>
                    </div>
                </div>
                <button class="loading-close-btn" id="loadingCloseBtn" style="display: none;">Cerrar</button>
            </div>
        </div>

        <!-- TERCER FORMULARIO - CARGAR RESÚMENES CLIMATOLÓGICOS -->
        <form class="cargar-form" id="cargarFormResumenes" enctype="multipart/form-data" style="margin-top: 2rem;">

            <!-- Mensajes de estado -->
            <div id="statusMessageResumenes" class="status-message"></div>

            <!-- Título principal del formulario -->
            <div class="form-main-header">
                <h2 class="form-main-title">Cargar Resúmenes Climatológicos</h2>
            </div>

            <!-- Layout compacto con filtros a la izquierda -->
            <div class="main-layout">

                <!-- Columna de filtros (izquierda) -->
                <div class="filters-column">

                    <!-- Selección de Estación -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Estación Meteorológica
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="estacionResumenes">
                                Seleccionar <span class="required">*</span>
                            </label>
                            <select class="form-select" id="estacionResumenes" name="estacion" required>
                                <option value="">Cargando estaciones...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Selector de Tipo de Archivo (SOLO XLSX) -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-file-excel"></i>
                            Tipo de Archivo
                        </div>

                        <div class="file-type-selector">
                            <button type="button" class="file-type-btn xlsx-only active" data-tipo="xlsx"
                                id="btnXLSXResumenes">
                                <i class="fas fa-file-excel"></i>
                                <span>XLSX</span>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Columna de contenido (derecha) -->
                <div class="content-column">

                    <!-- Zona de arrastre -->
                    <div class="form-section">
                        <div class="dropzone" id="dropzoneResumenes">
                            <div class="dropzone-content" id="dropzoneContentResumenes">
                                <div class="dropzone-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>

                                <div class="dropzone-text">Haz clic aquí para seleccionar tu archivo </div>
                                <div> - </div>
                                <div class="dropzone-subtext" id="dropzoneSubtextResumenes">
                                    Selecciona un archivo XLSX
                                </div>

                                <!-- Barra de progreso (oculta por defecto) -->
                                <div class="progress-container" id="progressContainerResumenes" style="display: none;">
                                    <div class="progress-bar" id="progressBarResumenes"></div>
                                </div>
                                <div class="progress-text" id="progressTextResumenes" style="display: none;">
                                    Subiendo archivo...
                                </div>
                            </div>

                            <!-- Información del archivo (oculta por defecto) -->
                            <div class="file-info" id="fileInfoResumenes" style="display: none;">
                                <div class="file-details">
                                    <div class="file-name" id="fileNameResumenes">archivo.xlsx</div>
                                    <div class="file-size" id="fileSizeResumenes">0 KB</div>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar complete" style="width: 100%;"></div>
                                </div>
                                <div class="progress-text">
                                    <i class="fas fa-check-circle"
                                        style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                                    Archivo listo para cargar
                                </div>
                                <button type="button" class="remove-file-btn" id="removeFileBtnResumenes">
                                    <i class="fas fa-trash"></i>
                                    Eliminar archivo
                                </button>
                            </div>

                            <input type="file" id="archivoResumenes" name="archivo" style="display: none;"
                                accept=".xlsx">
                        </div>
                    </div>

                    <!-- Información de validación -->
                    <div class="form-section">
                        <div class="validation-info" id="validationInfoResumenes">
                            <div class="validation-title">
                                <i class="fas fa-info-circle"></i>
                                Requisitos del Archivo XLSX
                            </div>
                            <div class="validation-rules" id="validationRulesResumenes">
                                <div class="validation-rule">
                                    <i class="fas fa-file-excel"></i>
                                    <span>Solo archivos Excel (.xlsx)</span>
                                </div>
                                <div class="validation-rule">
                                    <i class="fas fa-table"></i>
                                    <span>Datos de resúmenes climatológicos</span>
                                </div>
                                <div class="validation-rule">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Información mensual/anual</span>
                                </div>
                                <div class="validation-rule">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Estadísticas climatológicas</span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="submit" class="btn-form btn-submit" id="btnSubmitResumenes">
                    <i class="fas fa-upload"></i>
                    Cargar Resúmenes
                </button>
                <button type="button" class="btn-form btn-reset" id="btnResetResumenes">
                    <i class="fas fa-undo"></i>
                    Limpiar
                </button>
            </div>
        </form>

        <!-- Modal de confirmación RESÚMENES -->
        <div class="confirmation-modal" id="confirmationModalResumenes">
            <div class="confirmation-content">
                <div class="confirmation-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="confirmation-title">Confirmar Carga de Resúmenes Climatológicos</div>

                <div class="confirmation-details">
                    <div class="confirmation-detail-row">
                        <div class="confirmation-detail-label">
                            <i class="fas fa-file"></i>
                            Archivo
                        </div>
                        <div class="confirmation-detail-value" id="confirmFileNameResumenes">archivo.xlsx</div>
                    </div>
                    <div class="confirmation-detail-row">
                        <div class="confirmation-detail-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Estación
                        </div>
                        <div class="confirmation-detail-value" id="confirmStationResumenes">ESPOCH</div>
                    </div>
                    <div class="confirmation-detail-row">
                        <div class="confirmation-detail-label">
                            <i class="fas fa-file-excel"></i>
                            Tipo
                        </div>
                        <div class="confirmation-detail-value" id="confirmTypeResumenes">XLSX</div>
                    </div>
                    <div class="confirmation-detail-row">
                        <div class="confirmation-detail-label">
                            <i class="fas fa-database"></i>
                            Tamaño
                        </div>
                        <div class="confirmation-detail-value" id="confirmFileSizeResumenes">0 KB</div>
                    </div>
                </div>

                <div class="confirmation-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>¡Atención!</strong> Esta acción cargará los resúmenes climatológicos en la base de
                        datos.
                        Asegúrate de que la información sea correcta.
                    </div>
                </div>

                <div class="confirmation-actions">
                    <button class="confirmation-btn confirmation-btn-accept" id="confirmAcceptBtnResumenes">
                        <i class="fas fa-check-circle"></i>
                        Aceptar
                    </button>
                    <button class="confirmation-btn confirmation-btn-cancel" id="confirmCancelBtnResumenes">
                        <i class="fas fa-times-circle"></i>
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de carga con progreso RESÚMENES -->
        <div class="loading-modal" id="loadingModalResumenes">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-title" id="loadingTitleResumenes">Procesando archivo...</div>
                <div class="loading-progress-text" id="loadingProgressTextResumenes">Iniciando carga de datos</div>
                <div class="loading-progress-container">
                    <div class="loading-progress-bar" id="loadingProgressBarResumenes"></div>
                </div>
                <div class="loading-stats" id="loadingStatsResumenes" style="display: none;">
                    <div class="loading-stat">
                        <span class="loading-stat-value" id="totalRowsResumenes">0</span>
                        <div class="loading-stat-label">Total Filas</div>
                    </div>
                    <div class="loading-stat">
                        <span class="loading-stat-value" id="processedRowsResumenes">0</span>
                        <div class="loading-stat-label">Procesadas</div>
                    </div>
                </div>
                <button class="loading-close-btn" id="loadingCloseBtnResumenes" style="display: none;">Cerrar</button>
            </div>
        </div>
        <!-- SEGUNDO FORMULARIO - CARGAR TORRES -->
        <form class="cargar-form" id="cargarFormTorres" enctype="multipart/form-data" style="margin-top: 2rem;">

            <!-- Mensajes de estado -->
            <div id="statusMessageTorres" class="status-message"></div>

            <!-- Título principal del formulario -->
            <div class="form-main-header">
                <h2 class="form-main-title">Cargar Torres</h2>
            </div>

            <!-- Layout compacto con filtros a la izquierda -->
            <div class="main-layout">

                <!-- Columna de filtros (izquierda) -->
                <div class="filters-column">

                    <!-- Selección de Estación -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-map-marker-alt"></i>
                            Estación Meteorológica
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="estacionTorres">
                                Seleccionar <span class="required">*</span>
                            </label>
                            <select class="form-select" id="estacionTorres" name="estacion" required>
                                <option value="">Cargando estaciones...</option>
                            </select>
                        </div>
                    </div>

                    <!-- Selector de Nivel -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-layer-group"></i>
                            Nivel de Datos
                        </div>
                        <div class="level-selector-container">
                            <div class="level-buttons" id="nivelSelectorTorres">
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
                            <input type="hidden" id="nivelTorres" name="nivel" value="L0" required>
                        </div>
                    </div>

                    <!-- Selector de Tipo de Archivo -->
                    <div class="form-section">
                        <div class="form-section-title">
                            <i class="fas fa-file-alt"></i>
                            Tipo de Archivo
                        </div>

                        <div class="file-type-selector">
                            <button type="button" class="file-type-btn csv-only active" data-tipo="csv"
                                id="btnCSVTorres">
                                <i class="fas fa-file-csv"></i>
                                <span>CSV</span>
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Columna de contenido (derecha) -->
                <div class="content-column">

                    <!-- Zona de arrastre -->
                    <div class="form-section">
                        <div class="dropzone" id="dropzoneTorres">
                            <div class="dropzone-content" id="dropzoneContentTorres">
                                <div class="dropzone-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>

                                <div class="dropzone-text">Haz clic aquí para seleccionar tu archivo </div>
                                <div> - </div>
                                <div class="dropzone-subtext" id="dropzoneSubtextTorres">
                                    Selecciona un archivo CSV
                                </div>

                                <!-- Barra de progreso (oculta por defecto) -->
                                <div class="progress-container" id="progressContainerTorres" style="display: none;">
                                    <div class="progress-bar" id="progressBarTorres"></div>
                                </div>
                                <div class="progress-text" id="progressTextTorres" style="display: none;">
                                    Subiendo archivo...
                                </div>
                            </div>

                            <!-- Información del archivo (oculta por defecto) -->
                            <div class="file-info" id="fileInfoTorres" style="display: none;">
                                <div class="file-details">
                                    <div class="file-name" id="fileNameTorres">archivo.csv</div>
                                    <div class="file-size" id="fileSizeTorres">0 KB</div>
                                </div>
                                <div class="progress-container">
                                    <div class="progress-bar complete" style="width: 100%;"></div>
                                </div>
                                <div class="progress-text">
                                    <i class="fas fa-check-circle"
                                        style="color: var(--accent-green); margin-right: 0.5rem;"></i>
                                    Archivo listo para cargar
                                </div>
                                <button type="button" class="remove-file-btn" id="removeFileBtnTorres">
                                    <i class="fas fa-trash"></i>
                                    Eliminar archivo
                                </button>
                            </div>

                            <input type="file" id="archivoTorres" name="archivo" style="display: none;" accept=".csv">
                        </div>
                    </div>

                    <!-- Información de validación -->
                    <div class="form-section">
                        <div class="validation-info" id="validationInfoTorres">
                            <div class="validation-title">
                                <i class="fas fa-info-circle"></i>
                                Requisitos del Archivo - Nivel L0
                            </div>
                            <div class="validation-rules" id="validationRulesTorres">
                                <!-- Se llenan dinámicamente según el nivel -->
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Botones de acción -->
            <div class="form-actions">
                <button type="submit" class="btn-form btn-submit" id="btnSubmitTorres">
                    <i class="fas fa-upload"></i>
                    Cargar Torres
                </button>
                <button type="button" class="btn-form btn-reset" id="btnResetTorres">
                    <i class="fas fa-undo"></i>
                    Limpiar
                </button>
            </div>
        </form>

        <!-- Modal de confirmación TORRES -->
        <div class="confirmation-modal" id="confirmationModalTorres">
            <div class="confirmation-content">
                <div class="confirmation-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div class="confirmation-title">Confirmar Carga de Torres</div>

                <div class="confirmation-details">
                    <div class="confirmation-detail-row">
                        <div class="confirmation-detail-label">
                            <i class="fas fa-file"></i>
                            Archivo
                        </div>
                        <div class="confirmation-detail-value" id="confirmFileNameTorres">archivo.csv</div>
                    </div>
                    <div class="confirmation-detail-row">
                        <div class="confirmation-detail-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Estación
                        </div>
                        <div class="confirmation-detail-value" id="confirmStationTorres">ESPOCH</div>
                    </div>
                    <div class="confirmation-detail-row">
                        <div class="confirmation-detail-label">
                            <i class="fas fa-layer-group"></i>
                            Nivel de Datos
                        </div>
                        <div class="confirmation-detail-value" id="confirmLevelTorres">L0</div>
                    </div>
                    <div class="confirmation-detail-row">
                        <div class="confirmation-detail-label">
                            <i class="fas fa-database"></i>
                            Tamaño
                        </div>
                        <div class="confirmation-detail-value" id="confirmFileSizeTorres">0 KB</div>
                    </div>
                </div>

                <div class="confirmation-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div>
                        <strong>¡Atención!</strong> Esta acción cargará los datos de torres en la base de datos.
                        Asegúrate de que
                        la información sea correcta.
                    </div>
                </div>

                <div class="confirmation-actions">
                    <button class="confirmation-btn confirmation-btn-accept" id="confirmAcceptBtnTorres">
                        <i class="fas fa-check-circle"></i>
                        Aceptar
                    </button>
                    <button class="confirmation-btn confirmation-btn-cancel" id="confirmCancelBtnTorres">
                        <i class="fas fa-times-circle"></i>
                        Cancelar
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de carga con progreso TORRES -->
        <div class="loading-modal" id="loadingModalTorres">
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <div class="loading-title" id="loadingTitleTorres">Procesando archivo...</div>
                <div class="loading-progress-text" id="loadingProgressTextTorres">Iniciando carga de datos</div>
                <div class="loading-progress-container">
                    <div class="loading-progress-bar" id="loadingProgressBarTorres"></div>
                </div>
                <div class="loading-stats" id="loadingStatsTorres" style="display: none;">
                    <div class="loading-stat">
                        <span class="loading-stat-value" id="totalRowsTorres">0</span>
                        <div class="loading-stat-label">Total Filas</div>
                    </div>
                    <div class="loading-stat">
                        <span class="loading-stat-value" id="processedRowsTorres">0</span>
                        <div class="loading-stat-label">Procesadas</div>
                    </div>
                </div>
                <button class="loading-close-btn" id="loadingCloseBtnTorres" style="display: none;">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // ========================================
    // VARIABLES GLOBALES
    // ========================================
    let currentNivel = 'L0';
    let currentFileType = 'csv';
    let estaciones = {};

    // Estaciones por defecto (fallback)
    const estacionesDefault = {

    };

    // Reglas de validación por nivel
    const validationRulesByLevel = {
        L0: [
            { icon: 'fas fa-wind', text: 'Datos de viento sin procesar' },
            { icon: 'fas fa-table', text: 'Columnas: fecha, hora, dirección, velocidad' },
            { icon: 'fas fa-file-csv', text: 'Solo archivos CSV' },
            { icon: 'fas fa-clock', text: 'Datos cada 10 minutos' }
        ],
        L1: [
            { icon: 'fas fa-cloud-sun', text: 'Datos meteorológicos procesados' },
            { icon: 'fas fa-table', text: 'Temperatura, humedad, presión, etc.' },
            { icon: 'fas fa-file-csv', text: 'Solo archivos CSV' },
            { icon: 'fas fa-chart-line', text: 'Datos validados y calibrados' }
        ],
        L2: [
            { icon: 'fas fa-chart-line', text: 'Promedios horarios y análisis' },
            { icon: 'fas fa-calculator', text: 'Estadísticas calculadas' },
            { icon: 'fas fa-file', text: 'CSV o TXT' },
            { icon: 'fas fa-database', text: 'Datos agregados y analizados' }
        ]
    };

    // ========================================
    // INICIALIZACIÓN
    // ========================================
    document.addEventListener('DOMContentLoaded', function () {
        initializeForm();
        loadEstaciones();
        setupEventListeners();
        updateValidationRules();
    });
    // ========================================
    // SISTEMA DE NOTIFICACIONES
    // ========================================
    function showNotification(message, type = 'info', duration = 5000) {
        const container = document.getElementById('notificationContainer');

        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        const titles = {
            success: 'Éxito',
            error: 'Error',
            warning: 'Advertencia',
            info: 'Información'
        };

        const notification = document.createElement('div');
        notification.className = `notification-toast ${type}`;
        notification.innerHTML = `
            <div class="notification-icon">
                <i class="${icons[type]}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-title">${titles[type]}</div>
                <div class="notification-message">${message}</div>
            </div>
            <button class="notification-close">
                <i class="fas fa-times"></i>
            </button>
            <div class="notification-progress"></div>
        `;

        container.appendChild(notification);

        setTimeout(() => {
            notification.classList.add('show');
        }, 100);

        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            removeNotification(notification);
        });

        setTimeout(() => {
            removeNotification(notification);
        }, duration);

        return notification;
    }

    function removeNotification(notification) {
        notification.classList.remove('show');
        notification.classList.add('hide');

        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 400);
    }

    function cleanFormCompletely() {
        // Resetear el formulario
        document.getElementById('cargarForm').reset();

        // Resetear el select de estación
        document.getElementById('estacion').selectedIndex = 0;

        // Limpiar el input file (forma simple y efectiva)
        const fileInput = document.getElementById('archivo');
        fileInput.value = '';

        // Limpiar UI del dropzone
        const dropzone = document.getElementById('dropzone');
        const dropzoneContent = document.getElementById('dropzoneContent');
        const fileInfo = document.getElementById('fileInfo');
        const progressContainer = document.getElementById('progressContainer');
        const progressText = document.getElementById('progressText');
        const progressBar = document.getElementById('progressBar');

        // Restaurar estado visual
        dropzoneContent.style.display = 'flex';
        progressContainer.style.display = 'none';
        progressText.style.display = 'none';
        fileInfo.style.display = 'none';
        progressBar.style.width = '0%';

        dropzone.classList.remove('file-selected', 'dragover', 'file-loaded', 'disabled');

        // Resetear botón de submit
        const btnSubmit = document.getElementById('btnSubmit');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Datos';

        hideStatus();
    }

    // ========================================
    // CARGA DE ESTACIONES
    // ========================================
    async function loadEstaciones() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_estaciones');

            const response = await fetch('../controller/CLoadData.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success && data.data && data.data.estaciones) {
                populateEstacionesSelect(data.data.estaciones);
                estaciones = {};
                data.data.estaciones.forEach(est => {
                    estaciones[est.id_estacion] = `${est.codigo} - ${est.nombre}`;
                });

                if (data.data.warning) {
                    console.warn(data.data.warning);
                }
            } else {
                populateEstacionesSelectFromDefault();
                estaciones = { ...estacionesDefault };
            }

        } catch (error) {
            console.error('Error al cargar estaciones:', error);
            populateEstacionesSelectFromDefault();
            estaciones = { ...estacionesDefault };
            showStatus('Usando estaciones por defecto', 'info');
        }
    }

    function populateEstacionesSelect(estacionesData) {
        const select = document.getElementById('estacion');

        while (select.children.length > 1) {
            select.removeChild(select.lastChild);
        }

        select.children[0].textContent = 'Seleccione una estación';

        estacionesData.forEach(est => {
            const option = document.createElement('option');
            option.value = est.id_estacion;
            option.textContent = est.nombre;  // ← SOLO EL NOMBRE
            select.appendChild(option);
        });
    }

    function populateEstacionesSelectFromDefault() {
        const select = document.getElementById('estacion');

        while (select.children.length > 1) {
            select.removeChild(select.lastChild);
        }

        select.children[0].textContent = 'Seleccione una estación';

        Object.keys(estacionesDefault).forEach(id => {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = estacionesDefault[id];  // Ya depende de cómo esté definido estacionesDefault
            select.appendChild(option);
        });
    }

    // ========================================
    // INICIALIZACIÓN DE FORMULARIO
    // ========================================
    function initializeForm() {
        setNivel('L0');
        setFileType('csv');
    }

    // ========================================
    // EVENT LISTENERS
    // ========================================
    function setupEventListeners() {
        // Selectores de nivel
        document.querySelectorAll('.level-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const nivel = this.dataset.nivel;
                setNivel(nivel);
            });
        });

        // Dropzone
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('archivo');

        dropzone.addEventListener('click', (e) => {
            // NO interferir si se hace click en el botón de eliminar
            if (e.target.closest('.remove-file-btn')) {
                return;
            }

            // Verificar si ya hay un archivo cargado
            const fileInfo = document.getElementById('fileInfo');
            if (fileInfo.style.display === 'block') {
                return;
            }

            if (!dropzone.classList.contains('disabled')) {
                fileInput.click();
            }
        });

        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();

            // Verificar si ya hay un archivo cargado
            const fileInfo = document.getElementById('fileInfo');
            if (fileInfo.style.display === 'block') {
                return;
            }

            if (!dropzone.classList.contains('disabled')) {
                dropzone.classList.add('dragover');
            }
        });

        dropzone.addEventListener('dragleave', () => {
            dropzone.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzone.classList.remove('dragover');

            // Verificar si ya hay un archivo cargado
            const fileInfo = document.getElementById('fileInfo');
            if (fileInfo.style.display === 'block') {
                showNotification('Ya hay un archivo cargado. Por favor elimínalo primero.', 'warning', 4000);
                return;
            }

            if (!dropzone.classList.contains('disabled')) {
                // Validar que sea solo un archivo
                if (e.dataTransfer.files.length > 1) {
                    showNotification('Por favor arrastra solo UN archivo a la vez.', 'error', 4000);
                    return;
                }

                handleFileSelect(e.dataTransfer.files[0]);
            } else {
                showNotification('Esta combinación de archivo y nivel no está permitida.', 'error', 4000);
            }
        });

        fileInput.addEventListener('change', (e) => {
            // Verificar si ya hay un archivo cargado
            const fileInfo = document.getElementById('fileInfo');
            if (fileInfo.style.display === 'block') {
                showNotification('Ya hay un archivo cargado. Por favor elimínalo primero.', 'warning', 4000);
                fileInput.value = '';
                return;
            }

            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        document.getElementById('removeFileBtn').addEventListener('click', removeSelectedFile);
        document.getElementById('cargarForm').addEventListener('submit', handleFormSubmit);
        document.getElementById('btnReset').addEventListener('click', resetForm);
        document.getElementById('loadingCloseBtn').addEventListener('click', hideLoadingModal);

        // Botones del modal de confirmación
        document.getElementById('confirmAcceptBtn').addEventListener('click', handleConfirmAccept);
        document.getElementById('confirmCancelBtn').addEventListener('click', hideConfirmationModal);
    }

    // ========================================
    // GESTIÓN DE NIVEL
    // ========================================
    function setNivel(nivel) {
        currentNivel = nivel;

        document.querySelectorAll('.level-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-nivel="${nivel}"]`).classList.add('active');

        document.getElementById('nivel').value = nivel;

        updateValidationRules();
        checkFileTypeCompatibility();

        // Limpiar archivo seleccionado al cambiar de nivel
        removeSelectedFile();
    }

    // ========================================
    // GESTIÓN DE TIPO DE ARCHIVO
    // ========================================
    function setFileType(tipo) {
        // Siempre CSV, función mantenida por compatibilidad
        currentFileType = 'csv';
        updateDropzoneText();
    }

    // ========================================
    // ACTUALIZACIÓN DE REGLAS DE VALIDACIÓN
    // ========================================
    function updateValidationRules() {
        const rulesContainer = document.getElementById('validationRules');
        const validationTitle = document.querySelector('.validation-title');
        const rules = validationRulesByLevel[currentNivel];

        validationTitle.innerHTML = `
            <i class="fas fa-info-circle"></i>
            Requisitos del Archivo - Nivel ${currentNivel}
        `;

        rulesContainer.innerHTML = rules.map(rule => `
            <div class="validation-rule">
                <i class="${rule.icon}"></i>
                <span>${rule.text}</span>
            </div>
        `).join('');
    }

    function updateDropzoneText() {
        const dropzoneSubtext = document.getElementById('dropzoneSubtext');
        const fileInput = document.getElementById('archivo');

        dropzoneSubtext.textContent = 'Selecciona un archivo CSV';
        fileInput.accept = '.csv';
    }

    // ========================================
    // VERIFICACIÓN DE COMPATIBILIDAD
    // ========================================
    function checkFileTypeCompatibility() {
        // Solo CSV, siempre compatible
        const btnSubmit = document.getElementById('btnSubmit');
        const dropzone = document.getElementById('dropzone');
        const dropzoneContent = document.getElementById('dropzoneContent');

        dropzone.classList.remove('disabled');
        dropzoneContent.style.pointerEvents = 'auto';
        btnSubmit.disabled = false;
        hideStatus();
    }

    // ========================================
    // MANEJO DE ARCHIVOS
    // ========================================
    function handleFileSelect(file) {
        if (!file) return;

        // Validar que sea solo un archivo
        const fileInput = document.getElementById('archivo');
        if (fileInput.files.length > 1) {
            showStatus('Por favor seleccione solo UN archivo a la vez.', 'error');
            fileInput.value = '';
            return;
        }

        const isValid = validateFileType(file);
        if (!isValid) {
            showStatus('Tipo de archivo no válido. Por favor seleccione un archivo válido según el nivel seleccionado.', 'error');
            fileInput.value = '';
            return;
        }

        showProgressBar();
        simulateFileLoad(file);
    }

    function showProgressBar() {
        const dropzoneContent = document.getElementById('dropzoneContent');
        const progressContainer = document.getElementById('progressContainer');
        const progressText = document.getElementById('progressText');
        const progressBar = document.getElementById('progressBar');

        dropzoneContent.style.display = 'none';
        progressContainer.style.display = 'block';
        progressText.style.display = 'block';

        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 20;
            if (progress > 100) progress = 100;

            progressBar.style.width = progress + '%';
            progressText.textContent = `Procesando archivo... ${Math.round(progress)}%`;

            if (progress >= 100) {
                clearInterval(interval);
                setTimeout(showFileInfo, 500);
            }
        }, 200);
    }

    function showFileInfo() {
        const progressContainer = document.getElementById('progressContainer');
        const progressText = document.getElementById('progressText');
        const fileInfo = document.getElementById('fileInfo');
        const dropzone = document.getElementById('dropzone');

        progressContainer.style.display = 'none';
        progressText.style.display = 'none';
        fileInfo.style.display = 'block';
        dropzone.classList.add('file-loaded');

        hideStatus();
    }

    function removeSelectedFile() {
        const dropzoneContent = document.getElementById('dropzoneContent');
        const fileInfo = document.getElementById('fileInfo');
        const dropzone = document.getElementById('dropzone');
        const fileInput = document.getElementById('archivo');
        const progressContainer = document.getElementById('progressContainer');
        const progressText = document.getElementById('progressText');
        const progressBar = document.getElementById('progressBar');

        // Limpiar input file de forma simple
        fileInput.value = '';

        // Restaurar UI
        dropzoneContent.style.display = 'flex';
        fileInfo.style.display = 'none';
        progressContainer.style.display = 'none';
        progressText.style.display = 'none';
        progressBar.style.width = '0%';

        dropzone.classList.remove('file-loaded', 'file-selected', 'dragover');

        hideStatus();
    }

    function validateFileType(file) {
        const extension = file.name.split('.').pop().toLowerCase();

        if (extension !== 'csv') {
            showStatus('Solo se permiten archivos .CSV', 'error');
            return false;
        }
        return true;
    }

    function simulateFileLoad(file) {
        setTimeout(() => {
            updateFileInfo(file);
        }, 1500);
    }

    function updateFileInfo(file) {
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');

        fileName.textContent = file.name;
        fileSize.textContent = `${(file.size / 1024).toFixed(1)} KB`;

        showFileInfo();
    }

    // ========================================
    // ENVÍO DE FORMULARIO
    // ========================================
    async function handleFormSubmit(e) {
        e.preventDefault();

        const estacion = document.getElementById('estacion').value;
        const nivel = document.getElementById('nivel').value;
        const fileInput = document.getElementById('archivo');
        const archivo = fileInput.files[0];

        // VALIDACIÓN 1: Estación
        if (!estacion || estacion === '') {
            showNotification('Debe seleccionar una estación meteorológica', 'error', 4000);
            showStatus('Seleccione una estación meteorológica.', 'error');
            return;
        }

        // VALIDACIÓN 2: Archivo (CRÍTICA - AHORA SÍ NOTIFICA)
        if (!archivo) {
            showNotification('Debe seleccionar un archivo para cargar', 'error', 4000);
            showStatus('Debe seleccionar un archivo CSV o TXT.', 'error');
            return;
        }

        // VALIDACIÓN 3: Nivel
        if (!nivel) {
            showNotification('Debe seleccionar un nivel de datos', 'error', 4000);
            showStatus('Seleccione un nivel de datos.', 'error');
            return;
        }

        // VALIDACIÓN 5: Extensión del archivo (solo CSV)
        const extension = archivo.name.split('.').pop().toLowerCase();

        if (extension !== 'csv') {
            showNotification('Debe seleccionar un archivo .CSV', 'error', 4000);
            showStatus('Debe seleccionar un archivo .CSV', 'error');
            return;
        }

        // Si todas las validaciones pasan, mostrar modal de confirmación
        showConfirmationModal();
    }

    // ========================================
    // MODAL DE CONFIRMACIÓN
    // ========================================
    function showConfirmationModal() {
        const modal = document.getElementById('confirmationModal');
        const estacionId = document.getElementById('estacion').value;
        const estacionNombre = estaciones[estacionId] || 'Estación desconocida';
        const archivo = document.getElementById('archivo').files[0];
        const nivel = document.getElementById('nivel').value;

        // Actualizar información en el modal
        document.getElementById('confirmFileName').textContent = archivo.name;
        document.getElementById('confirmStation').textContent = estacionNombre;
        document.getElementById('confirmLevel').textContent = nivel;
        document.getElementById('confirmFileSize').textContent = `${(archivo.size / 1024).toFixed(1)} KB`;

        // Mostrar modal
        modal.style.display = 'flex';
    }

    function hideConfirmationModal() {
        const modal = document.getElementById('confirmationModal');
        modal.style.display = 'none';
    }

    function handleConfirmAccept() {
        // Ocultar modal de confirmación
        hideConfirmationModal();

        // Iniciar carga real
        startRealUpload();
    }

    // ========================================
    // CARGA REAL DE DATOS
    // ========================================
    async function startRealUpload() {
        const btnSubmit = document.getElementById('btnSubmit');
        const estacion = document.getElementById('estacion').value;
        const nivel = document.getElementById('nivel').value;
        const archivo = document.getElementById('archivo').files[0];

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';

        // Determinar el controlador según el nivel
        <?php
        // Calcular base del sitio independientemente de la carpeta de la vista.
        // dirname($_SERVER['SCRIPT_NAME'], 2) normalmente devuelve '/PracticasM'
        $siteBase = rtrim(dirname($_SERVER['SCRIPT_NAME'], 2), '/') . '/';
        ?>
        const controllerMap = {
            'L0': '<?php echo $siteBase; ?>controller/CLoadDataL0.php',
            'L1': '<?php echo $siteBase; ?>controller/CLoadDataL1.php',
            'L2': '<?php echo $siteBase; ?>controller/CLoadDataL2.php'
        };

        const controllerUrl = controllerMap[nivel];

        const formData = new FormData();
        formData.append('id_estacion', estacion);
        formData.append('archivo', archivo);
        formData.append('action', 'upload');
        formData.append('id_usuario', 1);

        showLoadingModal(`Cargando datos nivel ${nivel}...`);

        try {
            // Paso 1: Subir archivo
            const response = await fetch(controllerUrl, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await response.text();
                console.error('Respuesta no-JSON:', textResponse);
                throw new Error('El servidor devolvió una respuesta no válida.');
            }

            const data = await response.json();

            if (!data) {
                throw new Error('Respuesta vacía del servidor');
            }

            if (data.success === true) {
                if (data.warning) {
                    showNotification(data.warning, 'warning', 8000);
                }
                updateLoadingTitle(`Archivo ${nivel} analizado`);
                updateLoadingProgressText('Iniciando procesamiento de datos');
                updateLoadingProgress(25);

                setTimeout(() => {
                    startDataProcessing(data.data && data.data.id_carga, nivel, controllerUrl);
                }, 1000);

            } else {
                const errorMessage = data.message || 'Error desconocido en la carga del archivo';
                throw new Error(errorMessage);
            }

        } catch (error) {
            hideLoadingModal();
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Datos';

            const errorMsg = error.message || 'Error de conexión con el servidor';
            showNotification(errorMsg, 'error', 5000);
            console.error('Error:', error);
        }
    }

    async function startDataProcessing(idCarga, nivel, controllerUrl) {
        if (!idCarga) {
            throw new Error('ID de carga no válido');
        }

        updateLoadingTitle(`Procesando datos ${nivel}...`);
        updateLoadingProgressText('Validando y cargando en la base de datos');
        updateLoadingProgress(30);

        const processFormData = new FormData();
        processFormData.append('action', 'process');
        processFormData.append('id_carga', idCarga);
        processFormData.append('batch_size', 2000);

        try {
            const response = await fetch(controllerUrl, {
                method: 'POST',
                body: processFormData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await response.text();
                console.error('Respuesta no-JSON:', textResponse);
                throw new Error('El servidor devolvió una respuesta no válida durante el procesamiento.');
            }

            const data = await response.json();

            if (!data) {
                throw new Error('Respuesta vacía del servidor durante el procesamiento');
            }

            if (data.success === true) {
                if (data.data) {
                    showLoadingStats(data.data);
                }
                updateLoadingProgress(100);
                updateLoadingTitle(`¡Carga ${nivel} completada!`);
                updateLoadingProgressText('Datos cargados exitosamente');

                document.getElementById('loadingCloseBtn').style.display = 'block';

                const estacionNombre = estaciones[document.getElementById('estacion').value] || 'Estación seleccionada';
                const registros = data.data && data.data.registros_procesados ? data.data.registros_procesados : 0;

                showNotification(
                    `${registros} registros de ${estacionNombre} (${nivel}) han sido cargados correctamente.`,
                    'success',
                    5000
                );

                const btnSubmit = document.getElementById('btnSubmit');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Datos';

            } else {
                const errorMessage = data.message || 'Error desconocido en el procesamiento';
                throw new Error(errorMessage);
            }

        } catch (error) {
            updateLoadingTitle('Error en la carga');
            updateLoadingProgressText(error.message || 'Error desconocido');

            document.getElementById('loadingCloseBtn').style.display = 'block';

            const btnSubmit = document.getElementById('btnSubmit');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Datos';

            const errorMsg = error.message || 'Error de conexión durante el procesamiento';
            showNotification(errorMsg, 'error', 5000);
            console.error('Error:', error);
        }
    }

    // ========================================
    // MODAL DE CARGA
    // ========================================
    function showLoadingModal(title) {
        const modal = document.getElementById('loadingModal');
        const titleElement = document.getElementById('loadingTitle');
        const progressBar = document.getElementById('loadingProgressBar');
        const progressText = document.getElementById('loadingProgressText');
        const statsContainer = document.getElementById('loadingStats');
        const closeBtn = document.getElementById('loadingCloseBtn');

        titleElement.textContent = title;
        progressBar.style.width = '0%';
        progressText.textContent = 'Preparando...';
        statsContainer.style.display = 'none';
        closeBtn.style.display = 'none';

        modal.style.display = 'flex';
    }

    function hideLoadingModal() {
        document.getElementById('loadingModal').style.display = 'none';
        cleanFormCompletely();
    }

    function updateLoadingTitle(title) {
        document.getElementById('loadingTitle').textContent = title;
    }

    function updateLoadingProgressText(text) {
        document.getElementById('loadingProgressText').textContent = text;
    }

    function updateLoadingProgress(percent) {
        document.getElementById('loadingProgressBar').style.width = percent + '%';
    }

    function showLoadingStats(data) {
        if (!data) return;

        const statsContainer = document.getElementById('loadingStats');
        const totalRowsElement = document.getElementById('totalRows');
        const processedRowsElement = document.getElementById('processedRows');

        const registros = data.registros_procesados || 0;

        totalRowsElement.textContent = registros;
        processedRowsElement.textContent = registros;

        statsContainer.style.display = 'grid';
    }

    // ========================================
    // RESET Y UTILIDADES
    // ========================================
    function resetForm() {
        cleanFormCompletely();
        setNivel('L0');
        setFileType('csv');

        hideLoadingModal();
        hideConfirmationModal();

        showNotification('Formulario reiniciado correctamente', 'info', 3000);
    }

    function showStatus(message, type) {
        const statusMessage = document.getElementById('statusMessage');
        statusMessage.textContent = message;
        statusMessage.className = `status-message ${type}`;
        statusMessage.style.display = 'block';
    }

    function hideStatus() {
        document.getElementById('statusMessage').style.display = 'none';
    }
    // ========================================
    // FORMULARIO TORRES - VARIABLES GLOBALES
    // ========================================
    let currentNivelTorres = 'L0';
    let currentFileTypeTorres = 'csv';

    // ========================================
    // FORMULARIO TORRES - INICIALIZACIÓN
    // ========================================
    document.addEventListener('DOMContentLoaded', function () {
        initializeFormTorres();
        loadEstacionesTorres();
        setupEventListenersTorres();
        updateValidationRulesTorres();
    });

    function initializeFormTorres() {
        setNivelTorres('L0');
        setFileTypeTorres('csv');
    }

    // ========================================
    // FORMULARIO TORRES - EVENT LISTENERS
    // ========================================
    function setupEventListenersTorres() {
        // Selectores de nivel
        document.querySelectorAll('#nivelSelectorTorres .level-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const nivel = this.dataset.nivel;
                setNivelTorres(nivel);
            });
        });

        // Selectores de tipo de archivo
        document.getElementById('btnCSVTorres').addEventListener('click', () => setFileTypeTorres('csv'));


        // Dropzone
        const dropzoneTorres = document.getElementById('dropzoneTorres');
        const fileInputTorres = document.getElementById('archivoTorres');

        dropzoneTorres.addEventListener('click', (e) => {
            if (e.target.closest('.remove-file-btn')) return;
            const fileInfo = document.getElementById('fileInfoTorres');
            if (fileInfo.style.display === 'block') return;
            if (!dropzoneTorres.classList.contains('disabled')) {
                fileInputTorres.click();
            }
        });

        dropzoneTorres.addEventListener('dragover', (e) => {
            e.preventDefault();
            const fileInfo = document.getElementById('fileInfoTorres');
            if (fileInfo.style.display === 'block') return;
            if (!dropzoneTorres.classList.contains('disabled')) {
                dropzoneTorres.classList.add('dragover');
            }
        });

        dropzoneTorres.addEventListener('dragleave', () => {
            dropzoneTorres.classList.remove('dragover');
        });

        dropzoneTorres.addEventListener('drop', (e) => {
            e.preventDefault();
            dropzoneTorres.classList.remove('dragover');
            const fileInfo = document.getElementById('fileInfoTorres');
            if (fileInfo.style.display === 'block') {
                showNotification('Ya hay un archivo cargado. Por favor elimínalo primero.', 'warning', 4000);
                return;
            }
            if (!dropzoneTorres.classList.contains('disabled')) {
                if (e.dataTransfer.files.length > 1) {
                    showNotification('Por favor arrastra solo UN archivo a la vez.', 'error', 4000);
                    return;
                }
                handleFileSelectTorres(e.dataTransfer.files[0]);
            } else {
                showNotification('Esta combinación de archivo y nivel no está permitida.', 'error', 4000);
            }
        });

        fileInputTorres.addEventListener('change', (e) => {
            const fileInfo = document.getElementById('fileInfoTorres');
            if (fileInfo.style.display === 'block') {
                showNotification('Ya hay un archivo cargado. Por favor elimínalo primero.', 'warning', 4000);
                fileInputTorres.value = '';
                return;
            }
            if (e.target.files.length > 0) {
                handleFileSelectTorres(e.target.files[0]);
            }
        });

        document.getElementById('removeFileBtnTorres').addEventListener('click', removeSelectedFileTorres);
        document.getElementById('cargarFormTorres').addEventListener('submit', handleFormSubmitTorres);
        document.getElementById('btnResetTorres').addEventListener('click', resetFormTorres);
        document.getElementById('loadingCloseBtnTorres').addEventListener('click', hideLoadingModalTorres);
        document.getElementById('confirmAcceptBtnTorres').addEventListener('click', handleConfirmAcceptTorres);
        document.getElementById('confirmCancelBtnTorres').addEventListener('click', hideConfirmationModalTorres);
    }

    // ========================================
    // FORMULARIO TORRES - CARGA DE ESTACIONES
    // ========================================
    async function loadEstacionesTorres() {
        try {
            const formData = new FormData();
            formData.append('action', 'get_estaciones');

            const response = await fetch('../controller/CLoadData.php', {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success && data.data && data.data.estaciones) {
                populateEstacionesSelectTorres(data.data.estaciones);
            } else {
                populateEstacionesSelectFromDefaultTorres();
            }

        } catch (error) {
            console.error('Error al cargar estaciones:', error);
            populateEstacionesSelectFromDefaultTorres();
        }
    }

    function populateEstacionesSelectTorres(estacionesData) {
        const select = document.getElementById('estacionTorres');
        while (select.children.length > 1) {
            select.removeChild(select.lastChild);
        }
        select.children[0].textContent = 'Seleccione una estación';
        estacionesData.forEach(est => {
            const option = document.createElement('option');
            option.value = est.id_estacion;
            option.textContent = est.nombre;  // ← SOLO EL NOMBRE
            select.appendChild(option);
        });
    }

    function populateEstacionesSelectFromDefaultTorres() {
        const select = document.getElementById('estacionTorres');
        while (select.children.length > 1) {
            select.removeChild(select.lastChild);
        }
        select.children[0].textContent = 'Seleccione una estación';
        Object.keys(estacionesDefault).forEach(id => {
            const option = document.createElement('option');
            option.value = id;
            option.textContent = estacionesDefault[id];
            select.appendChild(option);
        });
    }

    // ========================================
    // FORMULARIO TORRES - GESTIÓN DE NIVEL
    // ========================================
    function setNivelTorres(nivel) {
        currentNivelTorres = nivel;
        document.querySelectorAll('#nivelSelectorTorres .level-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`#nivelSelectorTorres [data-nivel="${nivel}"]`).classList.add('active');
        document.getElementById('nivelTorres').value = nivel;
        updateValidationRulesTorres();
        checkFileTypeCompatibilityTorres();
        removeSelectedFileTorres();
    }

    // ========================================
    // FORMULARIO TORRES - GESTIÓN DE TIPO DE ARCHIVO
    // ========================================
    function setFileTypeTorres(tipo) {
        // Siempre CSV
        currentFileTypeTorres = 'csv';
        updateDropzoneTextTorres();
    }

    function updateDropzoneTextTorres() {
        const dropzoneSubtext = document.getElementById('dropzoneSubtextTorres');
        const fileInput = document.getElementById('archivoTorres');

        dropzoneSubtext.textContent = 'Selecciona un archivo CSV';
        fileInput.accept = '.csv';
    }

    // ========================================
    // FORMULARIO TORRES - VALIDACIÓN
    // ========================================
    function updateValidationRulesTorres() {
        const rulesContainer = document.getElementById('validationRulesTorres');
        const validationTitle = document.querySelector('#validationInfoTorres .validation-title');
        const rules = validationRulesByLevel[currentNivelTorres];
        validationTitle.innerHTML = `<i class="fas fa-info-circle"></i> Requisitos del Archivo - Nivel ${currentNivelTorres}`;
        rulesContainer.innerHTML = rules.map(rule => `
        <div class="validation-rule">
            <i class="${rule.icon}"></i>
            <span>${rule.text}</span>
        </div>
    `).join('');
    }

    function checkFileTypeCompatibilityTorres() {
        // Solo CSV, siempre compatible
        const btnSubmit = document.getElementById('btnSubmitTorres');
        const dropzone = document.getElementById('dropzoneTorres');
        const dropzoneContent = document.getElementById('dropzoneContentTorres');

        dropzone.classList.remove('disabled');
        dropzoneContent.style.pointerEvents = 'auto';
        btnSubmit.disabled = false;
        hideStatusTorres();
    }

    // ========================================
    // FORMULARIO TORRES - MANEJO DE ARCHIVOS
    // ========================================
    function handleFileSelectTorres(file) {
        if (!file) return;
        const fileInput = document.getElementById('archivoTorres');
        if (fileInput.files.length > 1) {
            showStatusTorres('Por favor seleccione solo UN archivo a la vez.', 'error');
            fileInput.value = '';
            return;
        }
        const isValid = validateFileTypeTorres(file);
        if (!isValid) {
            showStatusTorres('Tipo de archivo no válido. Por favor seleccione un archivo válido según el nivel seleccionado.', 'error');
            fileInput.value = '';
            return;
        }
        showProgressBarTorres();
        simulateFileLoadTorres(file);
    }

    function validateFileTypeTorres(file) {
        const extension = file.name.split('.').pop().toLowerCase();

        if (extension !== 'csv') {
            showStatusTorres('Solo se permiten archivos .CSV', 'error');
            return false;
        }
        return true;
    }

    function showProgressBarTorres() {
        const dropzoneContent = document.getElementById('dropzoneContentTorres');
        const progressContainer = document.getElementById('progressContainerTorres');
        const progressText = document.getElementById('progressTextTorres');
        const progressBar = document.getElementById('progressBarTorres');

        dropzoneContent.style.display = 'none';
        progressContainer.style.display = 'block';
        progressText.style.display = 'block';

        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 20;
            if (progress > 100) progress = 100;
            progressBar.style.width = progress + '%';
            progressText.textContent = `Procesando archivo... ${Math.round(progress)}%`;
            if (progress >= 100) {
                clearInterval(interval);
                setTimeout(showFileInfoTorres, 500);
            }
        }, 200);
    }

    function showFileInfoTorres() {
        const progressContainer = document.getElementById('progressContainerTorres');
        const progressText = document.getElementById('progressTextTorres');
        const fileInfo = document.getElementById('fileInfoTorres');
        const dropzone = document.getElementById('dropzoneTorres');

        progressContainer.style.display = 'none';
        progressText.style.display = 'none';
        fileInfo.style.display = 'block';
        dropzone.classList.add('file-loaded');
        hideStatusTorres();
    }

    function simulateFileLoadTorres(file) {
        setTimeout(() => {
            updateFileInfoTorres(file);
        }, 1500);
    }

    function updateFileInfoTorres(file) {
        const fileName = document.getElementById('fileNameTorres');
        const fileSize = document.getElementById('fileSizeTorres');
        fileName.textContent = file.name;
        fileSize.textContent = `${(file.size / 1024).toFixed(1)} KB`;
        showFileInfoTorres();
    }

    function removeSelectedFileTorres() {
        const dropzoneContent = document.getElementById('dropzoneContentTorres');
        const fileInfo = document.getElementById('fileInfoTorres');
        const dropzone = document.getElementById('dropzoneTorres');
        const fileInput = document.getElementById('archivoTorres');
        const progressContainer = document.getElementById('progressContainerTorres');
        const progressText = document.getElementById('progressTextTorres');
        const progressBar = document.getElementById('progressBarTorres');

        fileInput.value = '';
        dropzoneContent.style.display = 'flex';
        fileInfo.style.display = 'none';
        progressContainer.style.display = 'none';
        progressText.style.display = 'none';
        progressBar.style.width = '0%';
        dropzone.classList.remove('file-loaded', 'file-selected', 'dragover');
        hideStatusTorres();
    }

    // ========================================
    // FORMULARIO TORRES - ENVÍO
    // ========================================
    async function handleFormSubmitTorres(e) {
        e.preventDefault();
        const estacion = document.getElementById('estacionTorres').value;
        const nivel = document.getElementById('nivelTorres').value;
        const fileInput = document.getElementById('archivoTorres');
        const archivo = fileInput.files[0];

        if (!estacion || estacion === '') {
            showNotification('Debe seleccionar una estación meteorológica', 'error', 4000);
            showStatusTorres('Seleccione una estación meteorológica.', 'error');
            return;
        }

        if (!archivo) {
            showNotification('Debe seleccionar un archivo para cargar', 'error', 4000);
            showStatusTorres('Debe seleccionar un archivo CSV o TXT.', 'error');
            return;
        }

        if (!nivel) {
            showNotification('Debe seleccionar un nivel de datos', 'error', 4000);
            showStatusTorres('Seleccione un nivel de datos.', 'error');
            return;
        }


        const extension = archivo.name.split('.').pop().toLowerCase();
        if (extension !== 'csv') {
            showNotification('Debe seleccionar un archivo .CSV', 'error', 4000);
            showStatusTorres('Debe seleccionar un archivo .CSV', 'error');
            return;
        }

        showConfirmationModalTorres();
    }

    // ========================================
    // FORMULARIO TORRES - MODAL CONFIRMACIÓN
    // ========================================
    function showConfirmationModalTorres() {
        const modal = document.getElementById('confirmationModalTorres');
        const estacionId = document.getElementById('estacionTorres').value;
        const estacionNombre = estaciones[estacionId] || 'Estación desconocida';
        const archivo = document.getElementById('archivoTorres').files[0];
        const nivel = document.getElementById('nivelTorres').value;

        document.getElementById('confirmFileNameTorres').textContent = archivo.name;
        document.getElementById('confirmStationTorres').textContent = estacionNombre;
        document.getElementById('confirmLevelTorres').textContent = nivel;
        document.getElementById('confirmFileSizeTorres').textContent = `${(archivo.size / 1024).toFixed(1)} KB`;

        modal.style.display = 'flex';
    }

    function hideConfirmationModalTorres() {
        document.getElementById('confirmationModalTorres').style.display = 'none';
    }

    function handleConfirmAcceptTorres() {
        hideConfirmationModalTorres();
        startRealUploadTorres();
    }

    // ========================================
    // FORMULARIO TORRES - CARGA REAL
    // ========================================
    async function startRealUploadTorres() {
        const btnSubmit = document.getElementById('btnSubmitTorres');
        const estacion = document.getElementById('estacionTorres').value;
        const nivel = document.getElementById('nivelTorres').value;
        const archivo = document.getElementById('archivoTorres').files[0];

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';

        const controllerUrl = '../controller/CLoadTorres.php';

        const formData = new FormData();
        formData.append('id_estacion', estacion);
        formData.append('archivo', archivo);
        formData.append('action', 'upload');
        formData.append('nivel', nivel);

        showLoadingModalTorres(`Validando archivo de torres ${nivel}...`);

        try {
            const response = await fetch(controllerUrl, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await response.text();
                console.error('Respuesta no-JSON:', textResponse);
                throw new Error('El servidor devolvió una respuesta no válida.');
            }

            const data = await response.json();

            if (!data) {
                throw new Error('Respuesta vacía del servidor');
            }

            if (data.success === true) {
                if (data.warning) {
                    showNotification(data.warning, 'warning', 8000);
                }
                updateLoadingTitleTorres(`Archivo ${nivel} validado`);
                updateLoadingProgressTextTorres('Guardando archivo...');
                updateLoadingProgressTorres(50);

                setTimeout(() => {
                    guardarArchivoTorres(data.data, nivel, controllerUrl);
                }, 1000);

            } else {
                const errorMessage = data.message || 'Error desconocido en la validación';
                throw new Error(errorMessage);
            }

        } catch (error) {
            hideLoadingModalTorres();
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Torres';

            const errorMsg = error.message || 'Error de conexión con el servidor';
            showNotification(errorMsg, 'error', 5000);
            console.error('Error:', error);
        }
    }

    async function guardarArchivoTorres(data, nivel, controllerUrl) {
        if (!data) {
            throw new Error('No hay datos para guardar');
        }

        updateLoadingTitleTorres(`Guardando archivo ${nivel}...`);
        updateLoadingProgressTextTorres('Guardando archivo en el servidor');
        updateLoadingProgressTorres(75);

        const estacion = document.getElementById('estacionTorres').value;
        const tempPath = data.temp_path;

        const saveFormData = new FormData();
        saveFormData.append('action', 'process');
        saveFormData.append('id_estacion', estacion);
        saveFormData.append('nivel', nivel);
        saveFormData.append('temp_path', tempPath);

        try {
            const response = await fetch(controllerUrl, {
                method: 'POST',
                body: saveFormData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await response.text();
                console.error('Respuesta no-JSON:', textResponse);
                throw new Error('El servidor devolvió una respuesta no válida al guardar.');
            }

            const result = await response.json();

            if (!result) {
                throw new Error('Respuesta vacía del servidor al guardar');
            }

            if (result.success === true) {
                updateLoadingProgressTorres(100);
                updateLoadingTitleTorres(`¡Archivo Torres ${nivel} guardado!`);
                updateLoadingProgressTextTorres('Archivo guardado exitosamente');

                if (result.data) {
                    showLoadingStatsTorres(result.data);
                }

                document.getElementById('loadingCloseBtnTorres').style.display = 'block';

                const estacionNombre = estaciones[estacion] || 'Estación seleccionada';
                const registros = result.data && result.data.registros_procesados ? result.data.registros_procesados : 0;

                showNotification(
                    `${registros} registros de torres de ${estacionNombre} (${nivel}) han sido guardados correctamente.`,
                    'success',
                    5000
                );

                const btnSubmit = document.getElementById('btnSubmitTorres');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Torres';

            } else {
                const errorMessage = result.message || 'Error desconocido al guardar';
                throw new Error(errorMessage);
            }

        } catch (error) {
            updateLoadingTitleTorres('Error al guardar');
            updateLoadingProgressTextTorres(error.message || 'Error desconocido');

            document.getElementById('loadingCloseBtnTorres').style.display = 'block';

            const btnSubmit = document.getElementById('btnSubmitTorres');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Torres';

            const errorMsg = error.message || 'Error al guardar el archivo';
            showNotification(errorMsg, 'error', 5000);
            console.error('Error:', error);
        }
    }

    async function startDataProcessingTorres(idCarga, nivel, controllerUrl) {
        if (!idCarga) {
            throw new Error('ID de carga no válido');
        }

        updateLoadingTitleTorres(`Procesando torres ${nivel}...`);
        updateLoadingProgressTextTorres('Validando y cargando en la base de datos');
        updateLoadingProgressTorres(30);

        const processFormData = new FormData();
        processFormData.append('action', 'process');
        processFormData.append('id_carga', idCarga);
        processFormData.append('batch_size', 1000);

        try {
            const response = await fetch(controllerUrl, {
                method: 'POST',
                body: processFormData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const textResponse = await response.text();
                console.error('Respuesta no-JSON:', textResponse);
                throw new Error('El servidor devolvió una respuesta no válida durante el procesamiento.');
            }

            const data = await response.json();

            if (!data) {
                throw new Error('Respuesta vacía del servidor durante el procesamiento');
            }

            if (data.success === true) {
                if (data.data) {
                    showLoadingStatsTorres(data.data);
                }
                updateLoadingProgressTorres(100);
                updateLoadingTitleTorres(`¡Carga torres ${nivel} completada!`);
                updateLoadingProgressTextTorres('Datos cargados exitosamente');

                document.getElementById('loadingCloseBtnTorres').style.display = 'block';

                const estacionNombre = estaciones[document.getElementById('estacionTorres').value] || 'Estación seleccionada';
                const registros = data.data && data.data.registros_procesados ? data.data.registros_procesados : 0;

                showNotification(
                    `${registros} registros de torres de ${estacionNombre} (${nivel}) han sido cargados correctamente.`,
                    'success',
                    5000
                );

                const btnSubmit = document.getElementById('btnSubmitTorres');
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Torres';

            } else {
                const errorMessage = data.message || 'Error desconocido en el procesamiento';
                throw new Error(errorMessage);
            }

        } catch (error) {
            updateLoadingTitleTorres('Error en la carga');
            updateLoadingProgressTextTorres(error.message || 'Error desconocido');

            document.getElementById('loadingCloseBtnTorres').style.display = 'block';

            const btnSubmit = document.getElementById('btnSubmitTorres');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Torres';

            const errorMsg = error.message || 'Error de conexión durante el procesamiento';
            showNotification(errorMsg, 'error', 5000);
            console.error('Error:', error);
        }
    }

    // ========================================
    // FORMULARIO TORRES - MODAL DE CARGA
    // ========================================
    function showLoadingModalTorres(title) {
        const modal = document.getElementById('loadingModalTorres');
        const titleElement = document.getElementById('loadingTitleTorres');
        const progressBar = document.getElementById('loadingProgressBarTorres');
        const progressText = document.getElementById('loadingProgressTextTorres');
        const statsContainer = document.getElementById('loadingStatsTorres');
        const closeBtn = document.getElementById('loadingCloseBtnTorres');

        titleElement.textContent = title;
        progressBar.style.width = '0%';
        progressText.textContent = 'Preparando...';
        statsContainer.style.display = 'none';
        closeBtn.style.display = 'none';

        modal.style.display = 'flex';
    }

    function hideLoadingModalTorres() {
        document.getElementById('loadingModalTorres').style.display = 'none';
        cleanFormCompletelyTorres();
    }

    function updateLoadingTitleTorres(title) {
        document.getElementById('loadingTitleTorres').textContent = title;
    }

    function updateLoadingProgressTextTorres(text) {
        document.getElementById('loadingProgressTextTorres').textContent = text;
    }

    function updateLoadingProgressTorres(percent) {
        document.getElementById('loadingProgressBarTorres').style.width = percent + '%';
    }

    function showLoadingStatsTorres(data) {
        if (!data) return;

        const statsContainer = document.getElementById('loadingStatsTorres');
        const totalRowsElement = document.getElementById('totalRowsTorres');
        const processedRowsElement = document.getElementById('processedRowsTorres');

        const registros = data.registros_procesados || 0;

        totalRowsElement.textContent = registros;
        processedRowsElement.textContent = registros;

        statsContainer.style.display = 'grid';
    }

    // ========================================
    // FORMULARIO TORRES - UTILIDADES
    // ========================================
    function cleanFormCompletelyTorres() {
        document.getElementById('cargarFormTorres').reset();
        document.getElementById('estacionTorres').selectedIndex = 0;

        const fileInput = document.getElementById('archivoTorres');
        fileInput.value = '';

        const dropzone = document.getElementById('dropzoneTorres');
        const dropzoneContent = document.getElementById('dropzoneContentTorres');
        const fileInfo = document.getElementById('fileInfoTorres');
        const progressContainer = document.getElementById('progressContainerTorres');
        const progressText = document.getElementById('progressTextTorres');
        const progressBar = document.getElementById('progressBarTorres');

        dropzoneContent.style.display = 'flex';
        progressContainer.style.display = 'none';
        progressText.style.display = 'none';
        fileInfo.style.display = 'none';
        progressBar.style.width = '0%';

        dropzone.classList.remove('file-selected', 'dragover', 'file-loaded', 'disabled');

        const btnSubmit = document.getElementById('btnSubmitTorres');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Torres';

        hideStatusTorres();
    }

    function resetFormTorres() {
        cleanFormCompletelyTorres();
        setNivelTorres('L0');
        setFileTypeTorres('csv');

        hideLoadingModalTorres();
        hideConfirmationModalTorres();

        showNotification('Formulario de torres reiniciado correctamente', 'info', 3000);
    }

    function showStatusTorres(message, type) {
        const statusMessage = document.getElementById('statusMessageTorres');
        statusMessage.textContent = message;
        statusMessage.className = `status-message ${type}`;
        statusMessage.style.display = 'block';
    }

    function hideStatusTorres() {
        document.getElementById('statusMessageTorres').style.display = 'none';
    }
    // ========================================
// FORMULARIO RESÚMENES - VARIABLES GLOBALES
// ========================================
let currentFileTypeResumenes = 'xlsx';

// ========================================
// FORMULARIO RESÚMENES - INICIALIZACIÓN
// ========================================
document.addEventListener('DOMContentLoaded', function () {
    initializeFormResumenes();
    loadEstacionesResumenes();
    setupEventListenersResumenes();
});

function initializeFormResumenes() {
    setFileTypeResumenes('xlsx');
}

// ========================================
// FORMULARIO RESÚMENES - EVENT LISTENERS
// ========================================
function setupEventListenersResumenes() {
    // Selector de tipo de archivo (aunque solo hay XLSX, mantenemos la estructura)
    document.getElementById('btnXLSXResumenes').addEventListener('click', () => setFileTypeResumenes('xlsx'));

    // Dropzone
    const dropzoneResumenes = document.getElementById('dropzoneResumenes');
    const fileInputResumenes = document.getElementById('archivoResumenes');

    dropzoneResumenes.addEventListener('click', (e) => {
        if (e.target.closest('.remove-file-btn')) return;
        const fileInfo = document.getElementById('fileInfoResumenes');
        if (fileInfo.style.display === 'block') return;
        fileInputResumenes.click();
    });

    dropzoneResumenes.addEventListener('dragover', (e) => {
        e.preventDefault();
        const fileInfo = document.getElementById('fileInfoResumenes');
        if (fileInfo.style.display === 'block') return;
        dropzoneResumenes.classList.add('dragover');
    });

    dropzoneResumenes.addEventListener('dragleave', () => {
        dropzoneResumenes.classList.remove('dragover');
    });

    dropzoneResumenes.addEventListener('drop', (e) => {
        e.preventDefault();
        dropzoneResumenes.classList.remove('dragover');
        const fileInfo = document.getElementById('fileInfoResumenes');
        if (fileInfo.style.display === 'block') {
            showNotification('Ya hay un archivo cargado. Por favor elimínalo primero.', 'warning', 4000);
            return;
        }
        if (e.dataTransfer.files.length > 1) {
            showNotification('Por favor arrastra solo UN archivo a la vez.', 'error', 4000);
            return;
        }
        handleFileSelectResumenes(e.dataTransfer.files[0]);
    });

    fileInputResumenes.addEventListener('change', (e) => {
        const fileInfo = document.getElementById('fileInfoResumenes');
        if (fileInfo.style.display === 'block') {
            showNotification('Ya hay un archivo cargado. Por favor elimínalo primero.', 'warning', 4000);
            fileInputResumenes.value = '';
            return;
        }
        if (e.target.files.length > 0) {
            handleFileSelectResumenes(e.target.files[0]);
        }
    });

    document.getElementById('removeFileBtnResumenes').addEventListener('click', removeSelectedFileResumenes);
    document.getElementById('cargarFormResumenes').addEventListener('submit', handleFormSubmitResumenes);
    document.getElementById('btnResetResumenes').addEventListener('click', resetFormResumenes);
    document.getElementById('loadingCloseBtnResumenes').addEventListener('click', hideLoadingModalResumenes);
    document.getElementById('confirmAcceptBtnResumenes').addEventListener('click', handleConfirmAcceptResumenes);
    document.getElementById('confirmCancelBtnResumenes').addEventListener('click', hideConfirmationModalResumenes);
}

// ========================================
// FORMULARIO RESÚMENES - CARGA DE ESTACIONES
// ========================================
async function loadEstacionesResumenes() {
    try {
        const formData = new FormData();
        formData.append('action', 'get_estaciones');

        const response = await fetch('../controller/CLoadData.php', {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();

        if (data.success && data.data && data.data.estaciones) {
            populateEstacionesSelectResumenes(data.data.estaciones);
        } else {
            populateEstacionesSelectFromDefaultResumenes();
        }

    } catch (error) {
        console.error('Error al cargar estaciones:', error);
        populateEstacionesSelectFromDefaultResumenes();
    }
}

function populateEstacionesSelectResumenes(estacionesData) {
    const select = document.getElementById('estacionResumenes');
    while (select.children.length > 1) {
        select.removeChild(select.lastChild);
    }
    select.children[0].textContent = 'Seleccione una estación';
    estacionesData.forEach(est => {
        const option = document.createElement('option');
        option.value = est.id_estacion;
        option.textContent = est.nombre;
        select.appendChild(option);
    });
}

function populateEstacionesSelectFromDefaultResumenes() {
    const select = document.getElementById('estacionResumenes');
    while (select.children.length > 1) {
        select.removeChild(select.lastChild);
    }
    select.children[0].textContent = 'Seleccione una estación';
    Object.keys(estacionesDefault).forEach(id => {
        const option = document.createElement('option');
        option.value = id;
        option.textContent = estacionesDefault[id];
        select.appendChild(option);
    });
}

// ========================================
// FORMULARIO RESÚMENES - GESTIÓN DE TIPO DE ARCHIVO
// ========================================
function setFileTypeResumenes(tipo) {
    // Siempre XLSX
    currentFileTypeResumenes = 'xlsx';
    updateDropzoneTextResumenes();
}

function updateDropzoneTextResumenes() {
    const dropzoneSubtext = document.getElementById('dropzoneSubtextResumenes');
    const fileInput = document.getElementById('archivoResumenes');
    
    dropzoneSubtext.textContent = 'Selecciona un archivo XLSX';
    fileInput.accept = '.xlsx';
}

// ========================================
// FORMULARIO RESÚMENES - MANEJO DE ARCHIVOS
// ========================================
function handleFileSelectResumenes(file) {
    if (!file) return;
    const fileInput = document.getElementById('archivoResumenes');
    if (fileInput.files.length > 1) {
        showStatusResumenes('Por favor seleccione solo UN archivo a la vez.', 'error');
        fileInput.value = '';
        return;
    }
    const isValid = validateFileTypeResumenes(file);
    if (!isValid) {
        showStatusResumenes('Tipo de archivo no válido. Por favor seleccione un archivo XLSX.', 'error');
        fileInput.value = '';
        return;
    }
    showProgressBarResumenes();
    simulateFileLoadResumenes(file);
}

function validateFileTypeResumenes(file) {
    const extension = file.name.split('.').pop().toLowerCase();
    
    if (extension !== 'xlsx') {
        showStatusResumenes('Solo se permiten archivos .XLSX', 'error');
        return false;
    }
    return true;
}

function showProgressBarResumenes() {
    const dropzoneContent = document.getElementById('dropzoneContentResumenes');
    const progressContainer = document.getElementById('progressContainerResumenes');
    const progressText = document.getElementById('progressTextResumenes');
    const progressBar = document.getElementById('progressBarResumenes');

    dropzoneContent.style.display = 'none';
    progressContainer.style.display = 'block';
    progressText.style.display = 'block';

    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 100) progress = 100;
        progressBar.style.width = progress + '%';
        progressText.textContent = `Procesando archivo... ${Math.round(progress)}%`;
        if (progress >= 100) {
            clearInterval(interval);
            setTimeout(showFileInfoResumenes, 500);
        }
    }, 200);
}

function showFileInfoResumenes() {
    const progressContainer = document.getElementById('progressContainerResumenes');
    const progressText = document.getElementById('progressTextResumenes');
    const fileInfo = document.getElementById('fileInfoResumenes');
    const dropzone = document.getElementById('dropzoneResumenes');

    progressContainer.style.display = 'none';
    progressText.style.display = 'none';
    fileInfo.style.display = 'block';
    dropzone.classList.add('file-loaded');
    hideStatusResumenes();
}

function simulateFileLoadResumenes(file) {
    setTimeout(() => {
        updateFileInfoResumenes(file);
    }, 1500);
}

function updateFileInfoResumenes(file) {
    const fileName = document.getElementById('fileNameResumenes');
    const fileSize = document.getElementById('fileSizeResumenes');
    fileName.textContent = file.name;
    fileSize.textContent = `${(file.size / 1024).toFixed(1)} KB`;
    showFileInfoResumenes();
}

function removeSelectedFileResumenes() {
    const dropzoneContent = document.getElementById('dropzoneContentResumenes');
    const fileInfo = document.getElementById('fileInfoResumenes');
    const dropzone = document.getElementById('dropzoneResumenes');
    const fileInput = document.getElementById('archivoResumenes');
    const progressContainer = document.getElementById('progressContainerResumenes');
    const progressText = document.getElementById('progressTextResumenes');
    const progressBar = document.getElementById('progressBarResumenes');

    fileInput.value = '';
    dropzoneContent.style.display = 'flex';
    fileInfo.style.display = 'none';
    progressContainer.style.display = 'none';
    progressText.style.display = 'none';
    progressBar.style.width = '0%';
    dropzone.classList.remove('file-loaded', 'file-selected', 'dragover');
    hideStatusResumenes();
}

// ========================================
// FORMULARIO RESÚMENES - ENVÍO
// ========================================
async function handleFormSubmitResumenes(e) {
    e.preventDefault();
    const estacion = document.getElementById('estacionResumenes').value;
    const fileInput = document.getElementById('archivoResumenes');
    const archivo = fileInput.files[0];

    if (!estacion || estacion === '') {
        showNotification('Debe seleccionar una estación meteorológica', 'error', 4000);
        showStatusResumenes('Seleccione una estación meteorológica.', 'error');
        return;
    }

    if (!archivo) {
        showNotification('Debe seleccionar un archivo para cargar', 'error', 4000);
        showStatusResumenes('Debe seleccionar un archivo XLSX.', 'error');
        return;
    }

    const extension = archivo.name.split('.').pop().toLowerCase();
    if (extension !== 'xlsx') {
        showNotification('Debe seleccionar un archivo .XLSX', 'error', 4000);
        showStatusResumenes('Debe seleccionar un archivo .XLSX', 'error');
        return;
    }

    showConfirmationModalResumenes();
}

// ========================================
// FORMULARIO RESÚMENES - MODAL CONFIRMACIÓN
// ========================================
function showConfirmationModalResumenes() {
    const modal = document.getElementById('confirmationModalResumenes');
    const estacionId = document.getElementById('estacionResumenes').value;
    const estacionNombre = estaciones[estacionId] || 'Estación desconocida';
    const archivo = document.getElementById('archivoResumenes').files[0];

    document.getElementById('confirmFileNameResumenes').textContent = archivo.name;
    document.getElementById('confirmStationResumenes').textContent = estacionNombre;
    document.getElementById('confirmTypeResumenes').textContent = 'XLSX';
    document.getElementById('confirmFileSizeResumenes').textContent = `${(archivo.size / 1024).toFixed(1)} KB`;

    modal.style.display = 'flex';
}

function hideConfirmationModalResumenes() {
    document.getElementById('confirmationModalResumenes').style.display = 'none';
}

function handleConfirmAcceptResumenes() {
    hideConfirmationModalResumenes();
    startRealUploadResumenes();
}

// ========================================
// FORMULARIO RESÚMENES - CARGA REAL
// ========================================
async function startRealUploadResumenes() {
    const btnSubmit = document.getElementById('btnSubmitResumenes');
    const estacion = document.getElementById('estacionResumenes').value;
    const archivo = document.getElementById('archivoResumenes').files[0];

    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Cargando...';

    <?php
    $siteBase = rtrim(dirname($_SERVER['SCRIPT_NAME'], 2), '/') . '/';
    ?>
    const controllerUrl = '<?php echo $siteBase; ?>controller/CLoadDataResumenes.php';

    const formData = new FormData();
    formData.append('id_estacion', estacion);
    formData.append('archivo', archivo);
    formData.append('action', 'upload');

    showLoadingModalResumenes('Validando archivo XLSX...');

    try {
        const response = await fetch(controllerUrl, {
            method: 'POST',
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const textResponse = await response.text();
            console.error('Respuesta no-JSON:', textResponse);
            throw new Error('El servidor devolvió una respuesta no válida.');
        }

        const data = await response.json();

        if (!data) {
            throw new Error('Respuesta vacía del servidor');
        }

        if (data.success === true) {
            if (data.warning) {
                showNotification(data.warning, 'warning', 8000);
            }
            updateLoadingTitleResumenes('Archivo validado');
            updateLoadingProgressTextResumenes('Procesando datos...');
            updateLoadingProgressResumenes(50);

            setTimeout(() => {
                startDataProcessingResumenes(data.data && data.data.id_carga, controllerUrl);
            }, 1000);

        } else {
            const errorMessage = data.message || 'Error desconocido en la validación';
            throw new Error(errorMessage);
        }

    } catch (error) {
        hideLoadingModalResumenes();
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Resúmenes';

        const errorMsg = error.message || 'Error de conexión con el servidor';
        showNotification(errorMsg, 'error', 5000);
        console.error('Error:', error);
    }
}

async function startDataProcessingResumenes(idCarga, controllerUrl) {
    if (!idCarga) {
        throw new Error('ID de carga no válido');
    }

    updateLoadingTitleResumenes('Procesando resúmenes climatológicos...');
    updateLoadingProgressTextResumenes('Guardando en la base de datos');
    updateLoadingProgressResumenes(75);

    const processFormData = new FormData();
    processFormData.append('action', 'process');
    processFormData.append('id_carga', idCarga);

    try {
        const response = await fetch(controllerUrl, {
            method: 'POST',
            body: processFormData
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const textResponse = await response.text();
            console.error('Respuesta no-JSON:', textResponse);
            throw new Error('El servidor devolvió una respuesta no válida durante el procesamiento.');
        }

        const data = await response.json();

        if (!data) {
            throw new Error('Respuesta vacía del servidor durante el procesamiento');
        }

        if (data.success === true) {
            if (data.data) {
                showLoadingStatsResumenes(data.data);
            }
            updateLoadingProgressResumenes(100);
            updateLoadingTitleResumenes('¡Carga completada!');
            updateLoadingProgressTextResumenes('Resúmenes climatológicos cargados exitosamente');

            document.getElementById('loadingCloseBtnResumenes').style.display = 'block';

            const estacionNombre = estaciones[document.getElementById('estacionResumenes').value] || 'Estación seleccionada';
            const registros = data.data && data.data.registros_procesados ? data.data.registros_procesados : 0;

            showNotification(
                `${registros} registros de resúmenes climatológicos de ${estacionNombre} han sido cargados correctamente.`,
                'success',
                5000
            );

            const btnSubmit = document.getElementById('btnSubmitResumenes');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Resúmenes';

        } else {
            const errorMessage = data.message || 'Error desconocido en el procesamiento';
            throw new Error(errorMessage);
        }

    } catch (error) {
        updateLoadingTitleResumenes('Error en la carga');
        updateLoadingProgressTextResumenes(error.message || 'Error desconocido');

        document.getElementById('loadingCloseBtnResumenes').style.display = 'block';

        const btnSubmit = document.getElementById('btnSubmitResumenes');
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Resúmenes';

        const errorMsg = error.message || 'Error de conexión durante el procesamiento';
        showNotification(errorMsg, 'error', 5000);
        console.error('Error:', error);
    }
}

// ========================================
// FORMULARIO RESÚMENES - MODAL DE CARGA
// ========================================
function showLoadingModalResumenes(title) {
    const modal = document.getElementById('loadingModalResumenes');
    const titleElement = document.getElementById('loadingTitleResumenes');
    const progressBar = document.getElementById('loadingProgressBarResumenes');
    const progressText = document.getElementById('loadingProgressTextResumenes');
    const statsContainer = document.getElementById('loadingStatsResumenes');
    const closeBtn = document.getElementById('loadingCloseBtnResumenes');

    titleElement.textContent = title;
    progressBar.style.width = '0%';
    progressText.textContent = 'Preparando...';
    statsContainer.style.display = 'none';
    closeBtn.style.display = 'none';

    modal.style.display = 'flex';
}

function hideLoadingModalResumenes() {
    document.getElementById('loadingModalResumenes').style.display = 'none';
    cleanFormCompletelyResumenes();
}

function updateLoadingTitleResumenes(title) {
    document.getElementById('loadingTitleResumenes').textContent = title;
}

function updateLoadingProgressTextResumenes(text) {
    document.getElementById('loadingProgressTextResumenes').textContent = text;
}

function updateLoadingProgressResumenes(percent) {
    document.getElementById('loadingProgressBarResumenes').style.width = percent + '%';
}

function showLoadingStatsResumenes(data) {
    if (!data) return;

    const statsContainer = document.getElementById('loadingStatsResumenes');
    const totalRowsElement = document.getElementById('totalRowsResumenes');
    const processedRowsElement = document.getElementById('processedRowsResumenes');

    const registros = data.registros_procesados || 0;

    totalRowsElement.textContent = registros;
    processedRowsElement.textContent = registros;

    statsContainer.style.display = 'grid';
}

// ========================================
// FORMULARIO RESÚMENES - UTILIDADES
// ========================================
function cleanFormCompletelyResumenes() {
    document.getElementById('cargarFormResumenes').reset();
    document.getElementById('estacionResumenes').selectedIndex = 0;

    const fileInput = document.getElementById('archivoResumenes');
    fileInput.value = '';

    const dropzone = document.getElementById('dropzoneResumenes');
    const dropzoneContent = document.getElementById('dropzoneContentResumenes');
    const fileInfo = document.getElementById('fileInfoResumenes');
    const progressContainer = document.getElementById('progressContainerResumenes');
    const progressText = document.getElementById('progressTextResumenes');
    const progressBar = document.getElementById('progressBarResumenes');

    dropzoneContent.style.display = 'flex';
    progressContainer.style.display = 'none';
    progressText.style.display = 'none';
    fileInfo.style.display = 'none';
    progressBar.style.width = '0%';

    dropzone.classList.remove('file-selected', 'dragover', 'file-loaded', 'disabled');

    const btnSubmit = document.getElementById('btnSubmitResumenes');
    btnSubmit.disabled = false;
    btnSubmit.innerHTML = '<i class="fas fa-upload"></i> Cargar Resúmenes';

    hideStatusResumenes();
}

function resetFormResumenes() {
    cleanFormCompletelyResumenes();
    setFileTypeResumenes('xlsx');

    hideLoadingModalResumenes();
    hideConfirmationModalResumenes();

    showNotification('Formulario de resúmenes reiniciado correctamente', 'info', 3000);
}

function showStatusResumenes(message, type) {
    const statusMessage = document.getElementById('statusMessageResumenes');
    statusMessage.textContent = message;
    statusMessage.className = `status-message ${type}`;
    statusMessage.style.display = 'block';
}

function hideStatusResumenes() {
    document.getElementById('statusMessageResumenes').style.display = 'none';
}
</script>

<?php include('footerAdmin.php'); ?>