<?php
$titulo_pagina = 'Tiempo Real L2 - Sistema Meteorológico Profesional - ESPOCH';
$pagina_activa = 'tiemporeal';
$ruta_base = '../';
include('includes/header.php');
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
        grid-template-columns: 280px 1fr;
        gap: 1.2rem;
        padding: 1.2rem;
        min-height: 100vh;
        max-width: 1600px;
        margin: 0 auto;
    }

    /* ===== PANEL LATERAL DE FILTROS ===== */
    .sidebar-filters {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 15px;
        padding: 1.2rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-glow);
        height: fit-content;
        position: sticky;
        top: 1.2rem;
        max-height: calc(100vh - 2.4rem);
        overflow-y: auto;
        font-size: 0.8rem;
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

    /* ===== SELECTOR DE NIVEL (SOLO L2) ===== */
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
        gap: 0.5rem;
        grid-template-columns: 1fr;
    }

    .level-btn {
        padding: 0.7rem 0.5rem;
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 10px;
        color: var(--text-secondary);
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        justify-content: center;
    }

    .level-btn:hover {
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.1);
        transform: translateY(-2px);
    }

    .level-btn.active {
        background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 4px 20px rgba(0, 255, 136, 0.3);
    }

    .level-btn i {
        font-size: 1rem;
        color: white;
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

    /* ===== BOTONES DE ACCIÓN ===== */
    .filter-actions {
        display: grid;
        grid-template-columns: 1fr;
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

    .btn-refresh {
        background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(0, 255, 136, 0.3);
    }

    .btn-refresh:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 255, 136, 0.4);
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
        gap: 1.5rem;
    }

    /* ===== HEADER ===== */
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
        font-size: 1rem;
        font-weight: 700;
        background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 0.3rem;
    }

    .header-info p {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin: 0;
        max-width: 400px;
    }

    .header-stats {
        display: flex;
        gap: 2rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--accent-green);
        margin-bottom: 0.3rem;
    }

    .stat-label {
        font-size: 0.85rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* ===== BARRA DE REGISTROS ===== */
    .records-bar {
        background: var(--bg-secondary);
        border: 1px solid var(--border-primary);
        border-radius: 10px;
        padding: 1rem 1.5rem;
        backdrop-filter: blur(15px);
        box-shadow: var(--shadow-card);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .records-info {
        display: flex;
        gap: 2rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .record-item {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .record-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--accent-green);
        margin-bottom: 0.2rem;
    }

    .record-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .record-status {
        padding: 0.4rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .status-active {
        background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));
        color: #ffffff;
    }

    .pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 0.8;
        }
        50% {
            opacity: 1;
        }
    }

    /* ===== MAPA E INFORMACIÓN ===== */
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
        height: 320px;
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
        color: var(--accent-green);
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

    .info-card {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 12px;
        padding: 1.2rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-card);
        height: 320px;
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
        color: var(--accent-green);
    }

    .info-title {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--accent-green);
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

    /* ===== GRÁFICAS ===== */
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
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .chart-card {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 12px;
        padding: 1.2rem;
        backdrop-filter: blur(20px);
        transition: all 0.3s ease;
        box-shadow: var(--shadow-card);
    }

    .chart-card:hover {
        border-color: rgba(0, 255, 136, 0.5);
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 255, 136, 0.2);
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
        height: 280px;
        margin-bottom: 0.8rem;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        padding: 0.8rem;
    }

    /* ===== COLORES L2 ===== */
    .level-L2 .chart-level-badge {
        background: linear-gradient(135deg, var(--accent-green), var(--accent-purple));
        color: #ffffff;
    }

    /* ===== CONTENEDORES LARGOS ===== */
    .chart-large-container {
        margin-top: 2rem;
        padding: 0 0.5rem;
    }

    .chart-card-large {
        background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.9));
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        transition: all 0.3s ease;
    }

    .chart-card-large:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.4);
        border-color: rgba(255, 255, 255, 0.25);
    }

    .chart-card-large.level-L2 {
        border-left: 3px solid #00ff88;
    }

    .chart-large-wrapper {
        height: 400px;
        position: relative;
        background: rgba(0, 0, 0, 0.2);
        border-radius: 12px;
        overflow: hidden;
    }

    .chart-large-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: #ffffff;
        margin: 0 0 1.5rem 0;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .chart-large-title i {
        font-size: 1.2rem;
        opacity: 0.8;
    }

    /* ===== RESPONSIVO ===== */
    @media (max-width: 1400px) {
        .dashboard-container {
            grid-template-columns: 220px 1fr;
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
    }

    @media (max-width: 768px) {
        .dashboard-container {
            padding: 1rem;
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

        .records-bar {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .records-info {
            flex-direction: column;
            gap: 1rem;
        }

        .info-item {
            grid-template-columns: 1fr;
            gap: 0.4rem;
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
        background: var(--accent-green);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--accent-purple);
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

            <!-- Nivel de Datos (Solo L2) -->
            <div class="level-selector">
                <label class="level-selector-label">Nivel de Datos</label>
                <div class="level-buttons">
                    <button class="level-btn active" data-level="L2">
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
                    <select class="filter-input" id="filterEstacion">
                        <option value="">Cargando estaciones...</option>
                    </select>
                </div>
            </div>

            <!-- Información del Nivel -->
            <div class="filter-section">
                <div class="filter-section-title">
                    <i class="fas fa-info-circle"></i>
                    Información del Nivel
                </div>
                <div class="filter-group">
                    <div style="font-size: 0.7rem; color: var(--text-muted); line-height: 1.4;">
                        <strong style="color: var(--accent-green);">L2 - Datos Horarios</strong><br>
                        • Temperatura promedio<br>
                        • Humedad promedio<br>
                        • Presión atmosférica<br>
                        • Radiación solar<br>
                        • Velocidad del viento<br>
                        <br>
                        <strong style="color: var(--accent-green);">Actualización:</strong> Cada hora
                    </div>
                </div>
            </div>

            <!-- Botones de Acción -->
            <div class="filter-actions">
                <button class="btn-filter btn-refresh" onclick="refreshData()">
                    <i class="fas fa-sync-alt"></i>
                    Actualizar Datos
                </button>
            </div>

            <!-- Botones de Descarga -->
            <div class="download-actions">
                <button class="btn-download btn-download-pdf" onclick="downloadPDF()">
                    <i class="fas fa-file-pdf"></i>
                    PDF
                </button>
                <button class="btn-download btn-download-csv" onclick="downloadCSV()">
                    <i class="fas fa-file-csv"></i>
                    CSV
                </button>
            </div>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-content">
            
            <!-- Header del Dashboard -->
            <div class="dashboard-header">
                <div class="header-info">
                    <h1>Tiempo Real L2 - Análisis Climatológico</h1>
                    <p>Monitoreo de datos meteorológicos por hora</p>
                </div>
                <div class="header-stats">
                    <div class="stat-item">
                        <div class="stat-value" id="totalRegistros">---</div>
                        <div class="stat-label">Registros/Hora</div>
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

            <!-- Barra de Registros -->
            <div class="records-bar">
                <div class="records-info">
                    <div class="record-item">
                        <div class="record-value" id="datosHoy">---</div>
                        <div class="record-label">Datos de Hoy</div>
                    </div>
                    <div class="record-item">
                        <div class="record-value" id="promedioTemp">---</div>
                        <div class="record-label">Temp. Promedio</div>
                    </div>
                    <div class="record-item">
                        <div class="record-value" id="promedioHum">---</div>
                        <div class="record-label">Hum. Promedio</div>
                    </div>
                    <div class="record-item">
                        <div class="record-value" id="promedioViento">---</div>
                        <div class="record-label">Viento Promedio</div>
                    </div>
                </div>
                <div class="record-status status-active">
                    <i class="fas fa-circle pulse"></i>
                    Tiempo Real Activo
                </div>
            </div>
            
            <!-- Contenedor de Mapa e Información -->
            <div class="map-info-section">
                <!-- Mapa del Chimborazo (SIN IMAGEN - CONTENEDOR VACÍO) -->
                <div class="map-section">
                    <div class="section-header">
                        <i class="fas fa-mountain"></i>
                        <h3 class="section-title">Mapa de Estaciones</h3>
                    </div>
                    
                    <!-- Contenedor vacío para el mapa (se llenará desde BD) -->
                    <div class="map-image-container" id="mapContainer">
                        <!-- Aquí se cargará el mapa desde la base de datos -->
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
                            <span class="detail-label">Código Iner:</span>
                            <span class="detail-value" id="infoCodigoIner">---</span>
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

            <!-- Contenedor de Gráficos L2 -->
            <div class="charts-container">
                <div class="charts-section level-L2" id="chartsL2">
                    <div class="section-header">
                        <i class="fas fa-chart-line"></i>
                        <h3 class="section-title">Nivel L2 - Análisis Climatológico en Tiempo Real (1 hora)</h3>
                    </div>

                    <!-- Fila 1 -->
                    <div class="charts-row">
                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Tendencia Temporal - Temperatura</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <canvas id="chartTemperaturaL2"></canvas>
                            </div>
                        </div>

                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Tendencia Temporal - Humedad</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <canvas id="chartHumedadL2"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 2 -->
                    <div class="charts-row">
                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Tendencia Temporal - Presión</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <canvas id="chartPresionL2"></canvas>
                            </div>
                        </div>

                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Tendencia Temporal - Radiación Solar</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <canvas id="chartRadiacionL2"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 3 -->
                    <div class="charts-row">
                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Tendencia Temporal - Velocidad del Viento</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <canvas id="chartVientoL2"></canvas>
                            </div>
                        </div>

                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Comparación Multivariable</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <canvas id="chartMultivariableL2"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 4 -->
                    <div class="charts-row">
                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Promedio Diario - Todas Variables</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <canvas id="chartPromedioL2"></canvas>
                            </div>
                        </div>

                        <div class="chart-card level-L2">
                            <div class="chart-header">
                                <h4 class="chart-title">Distribución de Valores</h4>
                                <div class="chart-level-badge">L2</div>
                            </div>
                            <div class="chart-canvas-wrapper">
                                <canvas id="chartDistribucionL2"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Contenedores Largos L2 -->
                    <div class="chart-large-container">
                        <!-- Contenedor Largo 1 -->
                        <div class="chart-card-large level-L2">
                            <div class="chart-large-title">
                                <i class="fas fa-chart-line"></i>
                                <span>Línea de Tiempo Integral - Análisis L2 24H</span>
                            </div>
                            <div class="chart-large-wrapper">
                                <canvas id="chartLineaTiempoL2"></canvas>
                            </div>
                        </div>

                        <!-- Contenedor Largo 2 -->
                        <div class="chart-card-large level-L2">
                            <div class="chart-large-title">
                                <i class="fas fa-exchange-alt"></i>
                                <span>Comparación Multiestación - Variables L2</span>
                            </div>
                            <div class="chart-large-wrapper">
                                <canvas id="chartComparacionL2"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // ===== VARIABLES GLOBALES =====
    let charts = {};
    let currentStation = '';

    // ===== FUNCIONES PARA CONECTAR CON LA BASE DE DATOS =====
    function loadStationsFromDatabase() {
        // Aquí harás la consulta AJAX para cargar las estaciones desde la BD
        console.log('Cargando estaciones desde BD...');
    }

    function loadStationData(stationId) {
        // Aquí harás la consulta AJAX para cargar los datos de la estación desde la BD
        console.log('Cargando datos de estación desde BD...', stationId);
    }

    function loadRealtimeChartsFromDatabase() {
        // Aquí cargarás los datos de las gráficas en tiempo real desde la BD
        console.log('Cargando gráficas en tiempo real desde BD...');
    }

    function updateStationInfo(stationData) {
        // Actualizar información de la estación con datos de la BD
        document.getElementById('infoCodigo').textContent = stationData.codigo || '---';
        document.getElementById('infoComunidad').textContent = stationData.comunidad || '---';
        document.getElementById('infoAltura').textContent = stationData.altura || '---';
        document.getElementById('infoCodigoIner').textContent = stationData.codigo_iner || '---';
        document.getElementById('infoEstado').textContent = stationData.estado || '---';
        document.getElementById('infoFechaInsta').textContent = stationData.fecha_instalacion || '---';
    }

    function updateStationMap(mapData) {
        // Actualizar mapa con datos de la BD
        const mapContainer = document.getElementById('mapContainer');
        // Aquí cargarás el mapa desde la BD
        console.log('Actualizando mapa desde BD...', mapData);
    }

    function refreshData() {
        showNotification('Actualizando datos...', 'info');
        // Aquí harás las consultas a la BD para actualizar los datos
        loadStationsFromDatabase();
        loadRealtimeChartsFromDatabase();
        setTimeout(() => {
            showNotification('Datos actualizados correctamente', 'success');
        }, 1000);
    }

    function downloadPDF() {
        showNotification('Preparando descarga PDF...', 'info');
        // Aquí implementarás la generación del PDF
    }

    function downloadCSV() {
        showNotification('Preparando descarga CSV...', 'info');
        // Aquí implementarás la generación del CSV
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 10000;
            background: ${type === 'success' ? 'linear-gradient(135deg, #00ff88, #00ffff)' : 
                        type === 'error' ? 'linear-gradient(135deg, #ff073a, #ff9500)' :
                        'linear-gradient(135deg, #0096ff, #9d4edd)'};
            color: ${type === 'success' || type === 'error' ? '#0a0e1a' : '#ffffff'}; 
            padding: 1rem 1.5rem; border-radius: 10px;
            font-weight: 600; font-size: 0.9rem; 
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            transform: translateX(400px); transition: transform 0.3s ease;
        `;
        notification.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`;
        
        document.body.appendChild(notification);
        setTimeout(() => notification.style.transform = 'translateX(0)', 100);
        setTimeout(() => {
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => document.body.removeChild(notification), 300);
        }, 4000);
    }

    // ===== INICIALIZACIÓN =====
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Inicializando Tiempo Real L2...');
        
        // Cargar datos iniciales desde la BD
        loadStationsFromDatabase();
        loadRealtimeChartsFromDatabase();
        
        // Mostrar notificación de carga
        showNotification('Dashboard cargado - Conecte con la base de datos', 'info');
    });
</script>

<?php include('includes/footer.php'); ?>
