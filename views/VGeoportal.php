<?php
$titulo_pagina = 'Geoportal Interactivo Ecuador - Sistema Meteorológico ESPOCH';
$pagina_activa = 'geoportal';
$ruta_base = '../';
include('includes/header.php');
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
    .geoportal-page {
        min-height: 100vh;
        position: relative;
        padding-bottom: 0;
    }
    main {
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }

    .geoportal-background {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('../public/img/ecuador.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        background-attachment: fixed;
        z-index: -1;
    }

    .geoportal-background::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(10, 14, 26, 0.92) 0%, rgba(18, 24, 43, 0.88) 100%);
        z-index: 1;
    }
    

    .geoportal-container {
        position: relative;
        z-index: 2;
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem 0 2rem;
    }

    .geoportal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-cyan);
        margin: 0 0 0.8rem 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    .geoportal-subtitle {
        font-size: 1rem;
        color: var(--text-secondary);
        margin: 0;
        line-height: 1.5;
       }

    .map-container {
        background: var(--bg-secondary);
        border: 1px solid var(--border-secondary);
        border-radius: 15px;
        height: 600px;
        position: relative;
        overflow: hidden;
    }

    #ecuador-map {
        width: 100%;
        height: 100%;
        border-radius: 15px;
    }

    /* ===== LEAFLET OVERRIDES ===== */
    .leaflet-container {
        background: var(--bg-tertiary) !important;
        border-radius: 15px !important;
    }

    .leaflet-control-zoom {
        background: var(--bg-primary) !important;
        border: 1px solid var(--border-primary) !important;
        border-radius: 10px !important;
        box-shadow: var(--shadow-glow) !important;
    }

    .leaflet-control-zoom a {
        background: var(--bg-card) !important;
        border: 1px solid var(--border-secondary) !important;
        color: var(--primary-cyan) !important;
        font-weight: bold !important;
        transition: all 0.3s ease !important;
    }

    .leaflet-control-zoom a:hover {
        background: var(--primary-cyan) !important;
        color: #0a0e1a !important;
    }

    /* ===== MARCADORES COMPLETAMENTE ESTÁTICOS SIN ANIMACIONES ===== */
    .station-marker {
    border-radius: 50% !important;
    border: 2px solid !important;
    cursor: pointer !important;
    position: relative !important;
    /* ELIMINAR COMPLETAMENTE TODAS LAS ANIMACIONES Y TRANSICIONES */
    transform: none !important;
    transition: none !important;
    animation: none !important;
    -webkit-transition: none !important;
    -webkit-transform: none !important;
    -moz-transition: none !important;
    -moz-transform: none !important;
    -ms-transition: none !important;
    -ms-transform: none !important;
    -o-transition: none !important;
    -o-transform: none !important;
    will-change: auto !important;
    }

    /* SIN HOVER - Completamente estático */
    .station-marker:hover {
        transform: none !important;
        transition: none !important;
        animation: none !important;
    }

    /* Punto central más pequeño */
    .station-marker::after {
        content: '' !important;
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        width: 4px !important;
        height: 4px !important;
        background: rgba(255, 255, 255, 0.9) !important;
        border-radius: 50% !important;
        box-shadow: 0 0 2px rgba(255, 255, 255, 0.5) !important;
    }

    /* Colores únicos para cada estación */
    .marker-espoch { 
    background: var(--primary-cyan) !important; 
    border-color: var(--primary-cyan) !important; 
    }
    .marker-alao { 
    background: var(--primary-blue) !important; 
    border-color: var(--primary-blue) !important; 
    }
    .marker-atillo { 
        background: var(--accent-green) !important; 
        border-color: var(--accent-green) !important; 
    }
    .marker-cumanda { 
        background: var(--accent-orange) !important; 
        border-color: var(--accent-orange) !important; 
    }
    .marker-matus { 
        background: var(--accent-purple) !important; 
        border-color: var(--accent-purple) !important; 
    }
    .marker-multitud { 
        background: var(--accent-yellow) !important; 
        border-color: var(--accent-yellow) !important; 
    }
    .marker-quimiag { 
        background: #00ff40 !important; 
        border-color: #00ff40 !important; 
    }
    .marker-sanjuan { 
        background: #ff4000 !important; 
        border-color: #ff4000 !important; 
    }
    .marker-tixan { 
        background: var(--accent-pink) !important; 
        border-color: var(--accent-pink) !important; 
    }
    .marker-tunshi { 
        background: #ff40ff !important; 
        border-color: #ff40ff !important; 
    }
    .marker-urbina { 
        background: var(--accent-red) !important; 
        border-color: var(--accent-red) !important; 
    }

    /* ===== LEYENDA DEL MAPA ===== */
    .map-legend {
    position: absolute;
    bottom: 30px;
    right: 20px;
    background: rgba(10, 14, 26, 0.95);
    border: 2px solid var(--border-primary);
    border-radius: 10px;
    padding: 0.8rem 1rem;
    box-shadow: 0 8px 32px rgba(0, 255, 255, 0.3),
                0 0 20px rgba(0, 0, 0, 0.5);
    z-index: 1000;
    min-width: 280px;  /* ✅ Más ancho para 2 columnas */
    backdrop-filter: blur(15px);
}

.legend-title {
    color: var(--primary-cyan);
    font-size: 0.75rem;
    font-weight: 700;
    margin: 0 0 0.6rem 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: center;
    text-shadow: 0 0 10px rgba(0, 255, 255, 0.5);
}

.legend-items {
    display: grid;
    grid-template-columns: 1fr 1fr;  /* ✅ 2 columnas */
    gap: 0.4rem;
    max-height: 200px;  /* ✅ Menos altura */
    overflow-y: auto;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.6rem;  /* ✅ Más pequeño */
    color: var(--text-secondary);
    padding: 0.25rem 0.3rem;
    border-radius: 5px;
    transition: all 0.2s ease;
    cursor: pointer;
    white-space: nowrap;  /* ✅ No rompe el texto */
    overflow: hidden;
    text-overflow: ellipsis;  /* ✅ ... si es muy largo */
}

.legend-item:hover {
    background: rgba(0, 255, 255, 0.1);
    color: var(--text-primary);
}

.legend-color {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    border: 2px solid;
    flex-shrink: 0;
    box-shadow: 0 0 5px currentColor;
}

    /* ===== MODALES ===== */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(8px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
    }

    .modal-overlay.active {
        opacity: 1;
        visibility: visible;
    }

    .modal {
        background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
        border: 1px solid var(--border-primary);
        border-radius: 20px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5), 
                    0 0 30px rgba(0, 255, 255, 0.2);
        max-width: 900px;
        width: 95%;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
        animation: modalSlideIn 0.4s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.8) translateY(-50px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid var(--border-secondary);
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        background: linear-gradient(135deg, var(--bg-primary) 0%, var(--bg-secondary) 100%);
        border-radius: 20px 20px 0 0;
        z-index: 10;
    }

    .modal-title {
        color: var(--primary-cyan);
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .modal-close {
        background: var(--accent-red);
        border: none;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        color: white;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
    }

    .modal-close:hover {
        background: #ff4757;
        transform: scale(1.1);
    }

    .modal-body {
        padding: 2rem;
    }

    /* ===== INFORMACIÓN DE ESTACIÓN CON MEJOR DISTRIBUCIÓN ===== */
    .station-info-container {
    display: grid;
    grid-template-columns: 1fr 420px;  /* ✅ Espacio generoso */
    gap: 2.5rem;
    margin-bottom: 2rem;
    align-items: start;
    }

    .station-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.8rem;
        height: fit-content;  /* ✅ AGREGADO: Solo ocupa el espacio necesario */
    }

    .station-image {
    width: 100%;
    height: 300px;  /* ✅ Altura equilibrada */
    background: linear-gradient(135deg, var(--bg-card) 0%, rgba(26, 34, 54, 0.5) 100%);
    border: 1px solid var(--border-primary);
    border-radius: 12px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    box-shadow: 0 0 20px rgba(0, 255, 255, 0.2),
                inset 0 0 20px rgba(0, 255, 255, 0.05);
    padding: 10px;  /* ✅ Espacio interno para que no toque los bordes */
    }


    .station-image::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0, 255, 255, 0.08) 0%, transparent 50%);
    z-index: 1;
    pointer-events: none;
    }

    .station-image img {
        max-width: 95%;  /* ✅ No toca los bordes */
        max-height: 95%;  /* ✅ No toca los bordes */
        width: auto;
        height: auto;
        object-fit: contain;  /* ✅ CRÍTICO: Muestra imagen completa */
        object-position: center;
        border-radius: 8px;
        filter: brightness(1.08) contrast(1.15) saturate(1.25);
        transition: all 0.4s ease;
        position: relative;
        z-index: 2;
    }

    .station-image:hover img {
    filter: brightness(1.12) contrast(1.2) saturate(1.3);
    transform: scale(1.03);
    }
    .info-item {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        padding: 0.8rem;
        border-left: 3px solid var(--primary-cyan);
    }

    .info-label {
        color: var(--text-muted);
        font-size: 0.7rem;
        font-weight: 600;
        margin: 0 0 0.3rem 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        color: var(--text-primary);
        font-size: 0.85rem;
        font-weight: 600;
        margin: 0;
    }

    .status-active {
        color: var(--accent-green);
    }

    .status-offline {
        color: var(--accent-red);
    }

    /* ===== SECCIONES DE COMPONENTES Y SENSORES ===== */
    .components-sensors-section {
        margin-top: 2rem;
    }

    .section-title {
        color: var(--primary-cyan);
        font-size: 1rem;
        font-weight: 700;
        margin: 0 0 1rem 0;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border-secondary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .simple-list {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 0.5rem;
        margin-bottom: 1.5rem;
    }

    .simple-list-item {
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 6px;
        padding: 0.6rem 0.8rem;
        border-left: 2px solid var(--accent-green);
        color: var(--text-primary);
        font-size: 0.8rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .simple-list-item:hover {
        border-left-color: var(--primary-cyan);
        transform: translateX(2px);
        background: var(--bg-secondary);
    }

    /* ===== LOADING SPINNER ===== */
    .loading-spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid var(--border-primary);
        border-radius: 50%;
        border-top-color: var(--primary-cyan);
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* ===== BOTÓN CONSULTAR ===== */
    .consult-button {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        color: #0a0e1a;
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        font-size: 1rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s ease;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 20px rgba(0, 255, 255, 0.3);
        text-decoration: none;
        margin-top: 1.5rem;
    }

    .consult-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 30px rgba(0, 255, 255, 0.4);
        color: #0a0e1a;
        text-decoration: none;
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

    .map-container-wrapper {
        margin-bottom: 0;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 1024px) {
        .geoportal-container {
            padding: 0 1.5rem 0 1.5rem;
        }
        
        .map-container {
            height: 450px;
        }
        
        .legend-items {
            grid-template-columns: 1fr;
        }
        
        .modal {
            max-width: 90%;
        }
    }

    @media (max-width: 768px) {
        .geoportal-container {
            padding: 0 0.5rem 0rem 0.5rem;
            max-width: 100%;
        }
    
        .map-container {
            height: 350px;
        }
        .map-container-wrapper {
            margin-bottom: 0;
            padding: 1rem;
        }
        
        .geoportal-header {
            padding: 1.5rem;
        }
        
        .modal {
            width: 95%;
            max-height: 90vh;
        }

        .station-info {
            grid-template-columns: 1fr;
        }
        
        .map-legend {
            bottom: 10px;
            right: 10px;
            padding: 1rem;
            min-width: 180px;
        }
    }
</style>

<div class="geoportal-page">
    <div class="geoportal-background"></div>
    
    <div class="geoportal-container">
        <div class="map-container-wrapper fade-in">
            <div class="map-container">
                <div id="ecuador-map"></div>
                
                <!-- LEYENDA DEL MAPA -->
                <div class="map-legend">
                    <h4 class="legend-title">Estaciones Meteorológicas Ecuador</h4>
                    <div class="legend-items" id="legendItems">
                        <!-- Los items de leyenda se generarán dinámicamente desde JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- MODAL DE ERROR/ADVERTENCIA -->
<div class="modal-overlay" id="errorModal">
    <div class="modal" style="max-width: 500px;">
        <div class="modal-header">
            <h3 class="modal-title" style="color: var(--accent-orange);">
                <i class="fas fa-exclamation-triangle"></i>
                <span id="errorModalTitle">Advertencia</span>
            </h3>
            <button class="modal-close" onclick="closeErrorModal()">×</button>
        </div>
        <div class="modal-body" style="text-align: center; padding: 2rem;">
            <i class="fas fa-database" style="font-size: 3rem; color: var(--accent-orange); margin-bottom: 1rem;"></i>
            <p id="errorModalMessage" style="font-size: 1rem; color: var(--text-secondary); line-height: 1.6;">
                Mensaje de error
            </p>
        </div>
    </div>
</div>
</div>

<!-- MODAL DE INFORMACIÓN DE ESTACIÓN -->
<div class="modal-overlay" id="stationModal">
    <div class="modal">
        <div class="modal-header">
            <h3 class="modal-title">
                <i class="fas fa-broadcast-tower"></i>
                <span id="modalStationName">Estación Meteorológica</span>
            </h3>
            <button class="modal-close" onclick="closeStationModal()">×</button>
        </div>
        <div class="modal-body">
            <!-- CONTENEDOR DE INFORMACIÓN CON IMAGEN -->
            <div class="station-info-container">
                <div>
                    <div class="station-info">
                        <div class="info-item">
                            <p class="info-label">📍 Ubicación</p>
                            <p class="info-value" id="modalLocation">-</p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">🏔️ Elevación</p>
                            <p class="info-value" id="modalElevation">-</p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">🏷️ Código</p>
                            <p class="info-value" id="modalCode">-</p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">⚡ Estado</p>
                            <p class="info-value" id="modalStatus">-</p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">🌐 Coordenadas</p>
                            <p class="info-value" id="modalCoords">-</p>
                        </div>
                        <div class="info-item">
                            <p class="info-label">📅 Instalación</p>
                            <p class="info-value" id="modalInstalacion">-</p>
                        </div>
                    </div>
                </div>
                
                <!-- IMAGEN DE LA ESTACIÓN -->
                <div class="station-image">
                    <img id="stationImage" src="../public/img/default.png" alt="Imagen de la estación" onerror="this.src='../public/img/default.png'">
                </div>
            </div>

            <!-- SECCIÓN DE COMPONENTES -->
            <div class="components-sensors-section" id="componentsSection" style="display: none;">
                <h4 class="section-title">
                    <i class="fas fa-cog"></i>
                    Componentes de la Estación
                </h4>
                <div class="simple-list" id="componentsList">
                    <!-- Los componentes se cargarán dinámicamente -->
                </div>
            </div>

            <!-- SECCIÓN DE SENSORES -->
            <div class="components-sensors-section" id="sensorsSection" style="display: none;">
                <h4 class="section-title">
                    <i class="fas fa-chart-line"></i>
                    Sensores de la Estación
                </h4>
                <div class="simple-list" id="sensorsList">
                    <!-- Los sensores se cargarán dinámicamente -->
                </div>
            </div>
            
            <!-- BOTÓN PARA REDIRIGIR A DASHBOARD -->
            <a href="../controller/Cusercontroller.php?opcion=3" class="consult-button">
                <i class="fas fa-search"></i>
                Consultar Datos Meteorológicos
            </a>
        </div>
    </div>
</div>

<!-- LEAFLET CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

<!-- LEAFLET JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>

<!-- Font Awesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
    // Variables globales
    let map;
    let markers = {};
    let selectedStation = null;
    let allStations = [];
    
    // Array de colores disponibles para las estaciones (los mismos del diseño original)
    const colorPalette = [
        'var(--primary-cyan)',
        'var(--primary-blue)',
        'var(--accent-green)',
        'var(--accent-orange)',
        'var(--accent-red)',
        'var(--accent-purple)',
        'var(--accent-yellow)',
        'var(--accent-pink)',
        '#00ff40',  // Verde lima
        '#ff4000',  // Rojo anaranjado
        '#ff40ff',  // Magenta
        '#ffff00',  // Amarillo puro
        '#00ffff',  // Cian puro
        '#ff8000',  // Naranja
        '#8000ff',  // Violeta
        '#ff0080',  // Rosa
        '#00ff80',  // Verde azulado
        '#8000ff',  // Púrpura
        '#ff8000',  // Naranja oscuro
        '#ff0080'   // Rosa intenso
    ];

    // ===== GENERAR COLOR DINÁMICO PARA ESTACIÓN =====
    function getStationColor(index) {
        return colorPalette[index % colorPalette.length];
    }

    // ===== OBTENER CLASE CSS PARA MARCADOR (basada en ID) =====
    function getMarkerClass(stationId) {
        const classes = {
            1: 'espoch',
            2: 'alao',
            3: 'atillo',
            4: 'cumanda',
            5: 'matus',
            6: 'multitud',
            7: 'quimiag',
            8: 'sanjuan',
            9: 'tixan',
            10: 'tunshi',
            11: 'urbina'
        };
        return classes[stationId] || 'espoch';
    }

    // ===== CARGAR ESTACIONES DESDE LA BASE DE DATOS =====
    async function loadStationsFromDatabase() {
        console.log('🔄 Cargando estaciones desde la base de datos...');
        console.log('🌐 URL de la petición:', '../controller/CInformacionEstaciones.php?action=get_all_stations');
        
        try {
            const response = await fetch('../controller/CInformacionEstaciones.php?action=get_all_stations');
            
            console.log('📡 Respuesta recibida:');
            console.log('  - Status:', response.status);
            console.log('  - Status Text:', response.statusText);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                throw new Error(`Respuesta no es JSON. Content-Type: ${contentType}`);
            }
            
            const data = await response.json();
            
            console.log('📋 Datos completos recibidos:', data);
            console.log('✅ Success:', data.success);
            console.log('📊 Total estaciones:', data.total || (data.data ? data.data.length : 'N/A'));
            
            if (data.error) {
                console.log('❌ Error en respuesta:', data.error);
                throw new Error(data.error);
            }
            
            if (data.success && data.data && Array.isArray(data.data) && data.data.length > 0) {
                console.log('✅ Estaciones cargadas exitosamente:', data.data.length);
                console.log('🔍 Primera estación:', data.data[0]);
                
                // Procesar las estaciones para el mapa manteniendo compatibilidad con el diseño original
                allStations = data.data.map((station, index) => {
                    console.log(`📍 Procesando estación ${index + 1}:`, station);
                    
                    return {
                        id: station.id,
                        codigo: station.codigo,
                        name: station.nombre,
                        coords: station.coords,
                        location: station.location,
                        status: station.status,
                        elevation: station.elevation,
                        color: getStationColor(index),
                        imagePath: station.ruta_fotografia || '../public/img/chimborazo.jpg',
                        tag: station.tag || station.codigo,
                        provincia: station.provincia,
                        canton: station.canton,
                        ruta_fotografia: station.ruta_fotografia,
                        markerClass: getMarkerClass(station.id)
                    };
                });
                
                console.log('🗺️ Estaciones procesadas para el mapa:', allStations.length);
                console.log('🎨 Paleta de colores generada:', allStations.map(s => s.color));
                
                return allStations;
                
            } else {
                console.log('❌ No se recibieron datos válidos');
                throw new Error(data.error || `No se encontraron estaciones en la base de datos. Datos recibidos: ${JSON.stringify(data)}`);
            }
            
        } catch (error) {
            console.error('❌ Error al cargar estaciones:', error);
            
            // ❌ ELIMINAR ESTO:
            // alert('Error al cargar las estaciones meteorológicas: ' + error.message);
            
            // ✅ USAR ESTO:
            showErrorModal(
                'Error al cargar estaciones',
                'No se pudieron cargar las estaciones meteorológicas desde la base de datos. Por favor, verifique la conexión o contacte al administrador.'
            );
            
            throw error;
        }
    }

    // ===== GENERAR CÓDIGO DE IMAGEN PARA ESTACIÓN =====
    function generateImageCode(station) {
        // Si hay ruta_fotografia en la BD, usarla
        if (station.ruta_fotografia && station.ruta_fotografia.trim() !== '') {
            return station.ruta_fotografia;
        }
        
        // Si no, generar basado en el código de la estación
        const cleanCode = station.codigo.replace(/[^a-zA-Z0-9]/g, '');
        return `E_${cleanCode.substring(0, 10)}`;
    }

    // ===== INICIALIZACIÓN DEL MAPA =====
    async function initMap() {
        console.log('🗺️ Inicializando mapa de Ecuador...');
        
        try {
            // Cargar estaciones desde la base de datos
            const stations = await loadStationsFromDatabase();
            
            if (stations.length === 0) {
                // ❌ ELIMINAR ESTO:
                // throw new Error('No hay estaciones disponibles para mostrar');
                
                // ✅ USAR ESTO:
                showErrorModal(
                    'Sin Datos',
                    'No hay estaciones meteorológicas registradas en la base de datos. Por favor, registre al menos una estación para visualizar el mapa.'
                );
                throw new Error('No hay estaciones disponibles');
            }
            
            // Crear mapa centrado en Ecuador (cambiado de Chimborazo)
            map = L.map('ecuador-map', {
                center: [-1.8312, -78.1834], // Centro de Ecuador
                zoom: 6,
                minZoom: 4,
                maxZoom: 16,
                maxBounds: [[-5.0, -92.0], [2.0, -75.0]], // Límites de Ecuador
                maxBoundsViscosity: 1.0
            });

            // Tile Layer con estilo oscuro
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 18
            }).addTo(map);

            // Añadir estaciones al mapa
            addStationsToMap(stations);
            generateLegend(stations);
            
            console.log('✅ Mapa inicializado correctamente con', stations.length, 'estaciones');
            
        } catch (error) {
    console.error('❌ Error al inicializar mapa:', error);
    
    // ❌ ELIMINAR ESTO:
    // alert('Error al inicializar el mapa: ' + error.message);
    
    // ✅ USAR ESTO:
    if (!document.getElementById('errorModal').classList.contains('active')) {
        showErrorModal(
            'Error del Sistema',
            'Ocurrió un error al inicializar el geoportal. Por favor, recargue la página o contacte al administrador.'
        );
    }
}
    }

    // ===== AÑADIR ESTACIONES AL MAPA - CÍRCULOS PEQUEÑOS Y ESTÁTICOS =====
    function addStationsToMap(stations) {
        console.log('📍 Añadiendo estaciones al mapa...');
        
        stations.forEach(station => {
            // Validar coordenadas
            if (!station.coords || station.coords.length !== 2) {
                console.error(`❌ Coordenadas inválidas para ${station.name}:`, station.coords);
                return;
            }

            // Crear marcador PEQUEÑO y COMPLETAMENTE ESTÁTICO
            const marker = L.circleMarker(station.coords, {
                radius: 6,  // Reducido de 10 a 6
                fillColor: station.color,
                color: station.color,
                weight: 2,  // Reducido de 3 a 2
                opacity: 1,
                fillOpacity: 0.85,
                className: `station-marker marker-${station.markerClass}`,
                // DESACTIVAR TODAS LAS INTERACCIONES VISUALES
                interactive: true,
                bubblingMouseEvents: false
            });

            // Evento click PURO - sin propagación
            marker.on('click', function(e) {
                console.log('👆 Click en estación:', station.name);
                L.DomEvent.stopPropagation(e);
                L.DomEvent.preventDefault(e);
                showStationModal(station.id);
            });

            // DESACTIVAR EVENTOS DE MOUSE QUE CAUSAN "BAILE"
            marker.on('mouseover', function(e) {
                L.DomEvent.stopPropagation(e);
            });
            
            marker.on('mouseout', function(e) {
                L.DomEvent.stopPropagation(e);
            });

            marker.addTo(map);
            markers[station.id] = marker;
            console.log(`✅ Marcador añadido para ${station.name}`);
        });
        
        console.log(`🎯 Total de marcadores añadidos: ${Object.keys(markers).length}`);
    }

    // ===== GENERAR LEYENDA DINÁMICAMENTE =====
    function generateLegend(stations) {
        const legendItems = document.getElementById('legendItems');
        legendItems.innerHTML = '';
        
        console.log('📋 Generando leyenda...');
        
        stations.forEach(station => {
            const legendItem = document.createElement('div');
            legendItem.className = 'legend-item';
            legendItem.innerHTML = `
                <div class="legend-color" style="background: ${station.color}; border-color: ${station.color};"></div>
                <span>${station.name}</span>
            `;
            legendItems.appendChild(legendItem);
        });
        
        console.log('✅ Leyenda generada');
    }

    // ===== MODAL DE INFORMACIÓN DE ESTACIÓN =====
    function showStationModal(stationId) {
        const station = allStations.find(s => s.id == stationId);
        if (!station) return;
        
        selectedStation = stationId;
        
        // Llenar información básica del modal
        document.getElementById('modalStationName').textContent = station.name;
        document.getElementById('modalLocation').textContent = station.location;
        document.getElementById('modalElevation').textContent = station.elevation;
        document.getElementById('modalCode').textContent = station.codigo;
        
        // Formatear coordenadas
        const coords = station.coords;
        document.getElementById('modalCoords').textContent = 
            `${Math.abs(coords[0]).toFixed(4)}°S, ${Math.abs(coords[1]).toFixed(4)}°W`;
        
        const statusElement = document.getElementById('modalStatus');
        statusElement.textContent = station.status === 'active' ? '🟢 Activa' : '🔴 Inactiva';
        statusElement.className = `info-value ${station.status === 'active' ? 'status-active' : 'status-offline'}`;
        
        // Cambiar imagen de la estación
        updateStationImage(station);
        
        // Mostrar fecha de instalación como "Consultando desde base de datos"
        document.getElementById('modalInstalacion').textContent = 'Consultando desde base de datos...';
        
        // Cargar componentes y sensores mediante AJAX
        loadStationDetails(stationId);
        
        // Mostrar modal
        document.getElementById('stationModal').classList.add('active');
    }

    // ===== CARGAR DETALLES DE LA ESTACIÓN (COMPONENTES Y SENSORES) =====
    function loadStationDetails(stationId) {
        const componentsSection = document.getElementById('componentsSection');
        const sensorsSection = document.getElementById('sensorsSection');
        const componentsList = document.getElementById('componentsList');
        const sensorsList = document.getElementById('sensorsList');
        
        // Mostrar mensaje de carga
        componentsList.innerHTML = '<div class="simple-list-item"><span class="loading-spinner"></span> Cargando componentes...</div>';
        sensorsList.innerHTML = '<div class="simple-list-item"><span class="loading-spinner"></span> Cargando sensores...</div>';
        
        componentsSection.style.display = 'block';
        sensorsSection.style.display = 'block';
        
        // Realizar petición AJAX al controlador
        fetch(`../controller/CInformacionEstaciones.php?id_estacion=${stationId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la respuesta del servidor');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Renderizar componentes
                    renderSimpleComponents(data.data.componentes, componentsList);
                    // Renderizar sensores
                    renderSimpleSensors(data.data.sensores, sensorsList);
                    
                    // Actualizar fecha de instalación si está disponible
                    if (data.data.estacion && data.data.estacion.instalacion) {
                        document.getElementById('modalInstalacion').textContent = data.data.estacion.instalacion;
                    }
                } else {
                    throw new Error(data.error || 'Error desconocido');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Mostrar error en ambos listados
                componentsList.innerHTML = '<div class="simple-list-item">Error al cargar componentes</div>';
                sensorsList.innerHTML = '<div class="simple-list-item">Error al cargar sensores</div>';
            });
    }

    // ===== RENDERIZAR COMPONENTES (SOLO NOMBRES) =====
    function renderSimpleComponents(componentes, container) {
        if (!componentes || componentes.length === 0) {
            container.innerHTML = '<div class="simple-list-item">No hay componentes registrados</div>';
            return;
        }

        container.innerHTML = '';
        componentes.forEach(comp => {
            const listItem = document.createElement('div');
            listItem.className = 'simple-list-item';
            listItem.textContent = comp.tipo;
            container.appendChild(listItem);
        });
    }

    // ===== RENDERIZAR SENSORES (SOLO NOMBRES) =====
    function renderSimpleSensors(sensores, container) {
        if (!sensores || sensores.length === 0) {
            container.innerHTML = '<div class="simple-list-item">No hay sensores registrados</div>';
            return;
        }

        container.innerHTML = '';
        sensores.forEach(sensor => {
            const listItem = document.createElement('div');
            listItem.className = 'simple-list-item';
            listItem.textContent = sensor.tipo;
            container.appendChild(listItem);
        });
    }

    // ===== ACTUALIZAR IMAGEN DE LA ESTACIÓN =====
    function updateStationImage(station) {
    const imageElement = document.getElementById('stationImage');
    
    // ✅ CORRECCIÓN: Usar directamente imagePath que viene de ruta_fotografia
    if (station.imagePath && station.imagePath.trim() !== '') {
        // Si la ruta NO empieza con ../ agregarla
        const rutaCompleta = station.imagePath.startsWith('../') 
            ? station.imagePath 
            : `../${station.imagePath}`;
        
        console.log('🖼️ Cargando imagen:', rutaCompleta);
        imageElement.src = rutaCompleta;
    } else {
        console.log('⚠️ Sin imagen, usando default');
        imageElement.src = '../public/img/default.png';
    }
    
    // Fallback si la imagen no carga
    imageElement.onerror = function() {
        console.log('❌ Error al cargar imagen, usando default');
        this.src = '../public/img/default.png';
    };
}


    function closeStationModal() {
        document.getElementById('stationModal').classList.remove('active');
        selectedStation = null;
    }
    // ===== MODAL DE ERROR =====
function showErrorModal(title, message) {
    document.getElementById('errorModalTitle').textContent = title;
    document.getElementById('errorModalMessage').textContent = message;
    document.getElementById('errorModal').classList.add('active');
}

function closeErrorModal() {
    document.getElementById('errorModal').classList.remove('active');
}

    // ===== CERRAR MODAL AL HACER CLICK FUERA =====
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal-overlay')) {
            event.target.classList.remove('active');
            selectedStation = null;
        }
    });

    // ===== CERRAR MODAL CON ESCAPE =====
    // ===== CERRAR MODAL DE ERROR CON ESCAPE O CLICK FUERA =====
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeStationModal();
        closeErrorModal(); // ✅ AGREGAR ESTO
    }
});

    // ===== INICIALIZACIÓN =====
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Iniciando geoportal Ecuador...');
        console.log('📊 Cargando estaciones desde base de datos...');
        
        initMap();
    });
</script>


<?php
include('includes/footer.php');
?>