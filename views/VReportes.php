<?php
$titulo_pagina = 'Reportes de Estaciones - Sistema Meteorológico ESPOCH';
$pagina_activa = 'reportes';
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
    max-width: 550px;
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
    max-width: 500px;
    margin: 0 auto;
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

.station-select:focus ~ .select-icon {
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

    /* ===== DISEÑO: IMAGEN - INFO - IMAGEN ===== */
    .station-header {
        display: grid;
        grid-template-columns: 1fr 380px 1fr;
        gap: 2rem;
        margin-bottom: 3rem;
        align-items: center;
    }

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

    /* ===== SECCIONES DE COMPONENTES Y SENSORES ===== */
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
        to { transform: rotate(360deg); }
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
    }
</style>

<div class="reportes-page">
    <div class="reportes-background"></div>
    
    <div class="reportes-container">
        <!-- FILTROS DE ESTACIONES -->
        <div class="station-filters fade-in" id="stationFilters">
            <h3 class="filters-title">
                <i class="fas fa-filter"></i>
                Seleccionar Estación
            </h3>
            <div class="station-select-wrapper">
                <select id="stationSelect" class="station-select">
                    <option value="">Cargando estaciones...</option>
                </select>
                <i class="fas fa-chevron-down select-icon"></i>
            </div>
        </div>

        <div class="reportes-subtitle fade-in">
            Información detallada de componentes y sensores por estación
        </div>

        <!-- CONTENIDO DE LA ESTACIÓN -->
        <div class="station-content fade-in" id="stationContent">
            <div class="loading-content">
                <p>Sin Estaciones Disponibles</p>
            </div>
        </div>
    </div>
</div>

<!-- Font Awesome para iconos -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
    // Variables globales
    let estacionesData = []; // Se cargará dinámicamente desde la base de datos
    let selectedStationId = null;
    let isLoading = false;

    console.log('📊 Reportes Público cargado - Modo de solo lectura');

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
    selectElement.addEventListener('change', function() {
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
    function renderStationContent(data) {
        const contentContainer = document.getElementById('stationContent');
        const estacion = data.estacion;
        const componentes = data.componentes || [];
        const sensores = data.sensores || [];
        const imagenes = data.imagenes || {};
        
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
                <div class="station-info-center">
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
                            </div>
                        `).join('')}
                    </div>
                ` : `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No hay componentes registrados para esta estación</p>
                    </div>
                `}
            </div>

            <!-- SENSORES -->
            <div class="components-sensors-section">
                <h4 class="section-title">
                    <i class="fas fa-sensor-on"></i>
                    Sensores de la Estación
                    <span style="margin-left: auto; font-size: 0.85rem; opacity: 0.8;">(${sensores.length})</span>
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
                            </div>
                        `).join('')}
                    </div>
                ` : `
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No hay sensores registrados para esta estación</p>
                    </div>
                `}
            </div>
        `;
    }

    // ===== FUNCIONES DE UTILIDAD =====
    function showNoStationsMessage() {
        selectedStationId = null;
        estacionesData = [];
        
        const container = document.getElementById('stationButtons');
        container.innerHTML = `
            <div class="loading-content" style="padding: 2rem; color: var(--text-muted);">
                <i class="fas fa-info-circle" style="color: var(--accent-orange); font-size: 2rem; margin-bottom: 1rem;"></i>
                <p>No hay estaciones registradas en la base de datos</p>
                <p style="font-size: 0.8rem; margin-top: 0.5rem;">Contacte al administrador para más información</p>
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

    // ===== INICIALIZACIÓN =====
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Iniciando reportes de estaciones (solo lectura)...');
        
        // Cargar estaciones desde la base de datos
        loadStationsFromDatabase();
        
        console.log('✅ Reportes Público listo - Modo de solo lectura activado');
    });
</script>

<?php
include('includes/footer.php');
?>