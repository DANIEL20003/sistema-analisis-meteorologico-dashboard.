<?php
$titulo_pagina = 'Mapas Meteorológicos - Sistema ESPOCH Chimborazo';
$pagina_activa = 'mapas';
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
    .mapas-page-wrapper {
        position: relative;
        min-height: 100vh;
    }

    .mapas-fixed-background {
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

    .mapas-fixed-background::before {
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
    .mapas-container {
        position: relative;
        z-index: 3;
        display: grid;
        grid-template-columns: 300px 1fr;
        gap: 1.5rem;
        padding: 1.5rem;
        min-height: 100vh;
        max-width: 1800px;
        margin: 0 auto;
    }

    /* ===== PANEL LATERAL DE FILTROS ===== */
    .sidebar-mapas {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 15px;
        padding: 1.5rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-glow);
        height: fit-content;
        position: sticky;
        top: 1.5rem;
        max-height: calc(100vh - 3rem);
        overflow-y: auto;
    }

    .sidebar-header {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .sidebar-title {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--primary-cyan);
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .sidebar-icon {
        color: var(--primary-cyan);
        font-size: 1.1rem;
    }

    /* ===== CONTROLES DE FILTROS ===== */
    .filter-controls {
        margin-bottom: 2rem;
    }

    .control-section {
        margin-bottom: 1.5rem;
    }

    .control-section-title {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .control-section-title i {
        color: var(--primary-cyan);
        font-size: 0.85rem;
    }

    .control-group {
        margin-bottom: 1rem;
    }

    .control-label {
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--text-secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
        display: block;
    }

    .control-input {
        width: 100%;
        background: var(--bg-card);
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        padding: 0.8rem;
        color: var(--text-primary);
        font-size: 0.75rem;
        transition: all 0.3s ease;
    }

    .control-input:focus {
        outline: none;
        border-color: var(--primary-cyan);
        background: rgba(0, 255, 255, 0.1);
        box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
    }

    .control-input option {
        background: var(--bg-secondary);
        color: var(--text-primary);
        padding: 0.5rem;
        font-size: 0.75rem;
    }

    .control-input:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .filter-toggle {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .btn-filter {
        padding: 0.6rem 0.5rem;
        border: 1px solid var(--border-secondary);
        border-radius: 8px;
        background: var(--bg-card);
        color: var(--text-secondary);
        font-size: 0.65rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-filter.active {
        background: linear-gradient(135deg, var(--primary-cyan), var(--primary-blue));
        color: #0a0e1a;
        border-color: transparent;
    }

    .btn-filter:not(.active):hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--primary-cyan);
    }


    /* ===== CONTENIDO PRINCIPAL ===== */
    .main-map-content {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* ===== GRID DE MAPAS ===== */
    .mapas-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
        margin-top: 1rem;
    }

    /* ===== CONTENEDOR DE MAPA INDIVIDUAL ===== */
    .map-container {
        background: var(--bg-primary);
        border: 1px solid var(--border-primary);
        border-radius: 12px;
        padding: 1.5rem;
        backdrop-filter: blur(20px);
        box-shadow: var(--shadow-card);
        position: relative;
        overflow: visible;
        /* ✅ CAMBIO CLAVE */
        height: auto;
        /* ✅ CAMBIO CLAVE */
        min-height: 650px;
        display: flex;
        flex-direction: column;
    }

    .map-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid var(--border-secondary);
    }

    .map-icon {
        color: var(--primary-cyan);
        font-size: 1.2rem;
    }

    .map-title {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--primary-cyan);
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .map-subtitle {
        font-size: 0.75rem;
        color: var(--text-secondary);
        margin: 0;
    }

    /* ===== VISOR DE MAPA ===== */
    .map-viewer {
        position: relative;
        width: 95%;
        margin: 0 auto;
        height: 480px;
        /* ✅ CAMBIO CLAVE */
        min-height: 400px;
        max-height: 520px;
        background: var(--bg-secondary);
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--border-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* ===== IMAGEN DE FONDO DEL MAPA (MANTENIDA) ===== */

    .map-background::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(0, 255, 255, 0.02) 0%, rgba(0, 150, 255, 0.02) 100%);
        z-index: 2;
    }

    .map-overlay {
        position: relative;
        z-index: 3;
        width: 100%;
        height: 100%;
        display: none;
    }

    /* ===== LEYENDA DEL MAPA ===== */
    .map-legend {
        background: var(--bg-tertiary);
        border: 1px solid var(--border-primary);
        border-radius: 8px;
        padding: 0.8rem;
        margin-top: 0.8rem;
        flex-shrink: 0;
        /* ✅ NUEVO */
        width: 95%;
        margin-left: auto;
        margin-right: auto;
    }

    .legend-title {
        font-size: 0.65rem;
        font-weight: 600;
        color: var(--primary-cyan);
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .legend-items {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.4rem;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.65rem;
        color: var(--text-secondary);
    }

    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        border: 1px solid var(--border-secondary);
        flex-shrink: 0;
    }

    .legend-color.hot {
        background: var(--accent-red);
    }

    .legend-color.warm {
        background: var(--accent-orange);
    }

    .legend-color.cool {
        background: var(--accent-yellow);
    }

    .legend-color.cold {
        background: var(--primary-blue);
    }

    .legend-color.dry {
        background: var(--accent-purple);
    }

    .legend-color.humid {
        background: var(--accent-green);
    }

    /* ===== RESPONSIVO ===== */
    @media (max-width: 1400px) {
        .mapas-container {
            grid-template-columns: 280px 1fr;
            gap: 1rem;
        }

        .mapas-grid {
            grid-template-columns: 1fr;
        }

        .map-container {
            height: 800px;
        }
    }

    @media (max-width: 1200px) {
        .mapas-container {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .sidebar-mapas {
            position: static;
            max-height: none;
        }

        .map-container {
            height: 750px;
        }
    }

    @media (max-width: 768px) {
        .mapas-container {
            padding: 1rem;
        }

        .map-container {
            height: 700px;
        }

        .legend-items {
            grid-template-columns: repeat(2, 1fr);
        }

        .filter-toggle {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 480px) {
        .mapas-container {
            padding: 0.8rem;
        }

        .sidebar-mapas {
            padding: 1rem;
        }

        .map-container {
            height: 550px;
            padding: 1rem;
        }
    }

    /* ===== ANIMACIONES ===== */
    .map-container {
        animation: fadeInUp 0.6s ease-out;
    }

    .map-container:nth-child(1) {
        animation-delay: 0.1s;
    }

    .map-container:nth-child(2) {
        animation-delay: 0.2s;
    }

    .map-container:nth-child(3) {
        animation-delay: 0.3s;
    }

    .map-container:nth-child(4) {
        animation-delay: 0.4s;
    }

    .map-container:nth-child(5) {
        animation-delay: 0.5s;
    }

    .map-container:nth-child(6) {
        animation-delay: 0.6s;
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

    /* ===== ESTILOS PARA MENSAJES DE ERROR ===== */
    .filter-error-message {
        display: none;
        color: var(--accent-red);
        font-size: 0.65rem;
        margin-top: 0.3rem;
        font-weight: 600;
        animation: fadeIn 0.3s ease;
    }

    .filter-error-message.visible {
        display: block;
    }

    .control-input.error-required {
        border-color: var(--accent-red) !important;
        background: rgba(255, 7, 58, 0.1) !important;
        animation: shake 0.3s ease;
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-5px);
        }

        75% {
            transform: translateX(5px);
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    /* ===== ESTILOS PARA BOTÓN RESET ===== */
    .btn-reset {
        background: var(--bg-card);
        color: var(--text-secondary);
        border: 1px solid var(--border-secondary);
        font-size: 0.75rem;
        /* ✅ AGREGAR esta línea */
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-reset:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--primary-cyan);
        transform: translateY(-1px);
    }

    /* ===== LOADING STATE ===== */
    .control-input.loading {
        background-image: linear-gradient(90deg,
                var(--bg-card) 0%,
                rgba(0, 255, 255, 0.1) 50%,
                var(--bg-card) 100%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }

    /* ===== ESTILOS PARA LEAFLET ===== */
    .map-viewer {
        position: relative;
        width: 95%;
        margin: 0 auto;
        height: calc(100% - 180px);
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid var(--border-secondary);
        background: var(--bg-secondary);
    }

    /* Contenedor Leaflet */
    .leaflet-container {
        width: 100%;
        height: 100%;
        background: rgba(18, 24, 43, 0.95) !important;
        position: relative;
        /* ← AGREGAR */
        z-index: 10;
        /* ← AGREGAR: Mayor que map-background */
    }

    .leaflet-marker-pane {
        z-index: 600 !important;
    }

    .leaflet-tooltip-pane {
        z-index: 650 !important;
    }


    /* Ocultar controles de zoom de Leaflet */
    .leaflet-control-zoom {
        display: none !important;
    }

    /* Ocultar atribución de Leaflet */
    .leaflet-control-attribution {
        display: none !important;
    }

    /* Personalizar tiles (semi-transparentes) */
    .leaflet-tile-pane {
        opacity: 0.6;
    }

    /* Círculos meteorológicos */
    .marker-circulo {
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {

        0%,
        100% {
            box-shadow: 0 0 15px rgba(0, 255, 255, 0.5);
            transform: scale(1);
        }

        50% {
            box-shadow: 0 0 25px rgba(0, 255, 255, 0.8);
            transform: scale(1.05);
        }
    }

    /* Tooltip personalizado */
    .leaflet-tooltip {
        background: var(--bg-primary) !important;
        border: 1px solid var(--primary-cyan) !important;
        color: var(--text-primary) !important;
        font-size: 0.7rem !important;
        padding: 0.5rem !important;
        border-radius: 6px !important;
        box-shadow: var(--shadow-glow) !important;
    }

    .leaflet-tooltip-top:before,
    .leaflet-tooltip-bottom:before,
    .leaflet-tooltip-left:before,
    .leaflet-tooltip-right:before {
        border-top-color: var(--primary-cyan) !important;
    }

    /* ===== ICONOS TEMÁTICOS PARA MAPAS ===== */

    /* Contenedor de grupo de iconos */
    .icon-group-container {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 8px;
        width: 120px;
        height: 120px;
    }

    /* Contenedor circular invisible */
    .circle-container {
        position: relative;
        border-radius: 50%;
        border: 2px solid transparent;
        /* Borde transparente */
        background: transparent;
        /* Fondo transparente */
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        transform-origin: center center;
    }

    /* Efecto hover: mostrar borde sutil */
    .circle-container:hover {
        border-color: rgba(255, 255, 255, 0.15);
        background: rgba(255, 255, 255, 0.03);
    }

    /* Iconos individuales */
    .weather-icon {
        position: absolute;
        left: 50%;
        top: 50%;
        opacity: 0.85;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.4));
        animation: floatIcon 3s ease-in-out infinite;
        transition: all 0.3s ease;
        pointer-events: none;
        z-index: 1;

    }

    .weather-icon:nth-child(odd) {
        z-index: 2;
    }

    .weather-icon:nth-child(even) {
        z-index: 1;
    }

    .weather-icon:hover {
        opacity: 1;
        transform: scale(1.15) !important;
    }

    /* Tamaños de iconos */
    .icon-small {
        font-size: 12px;
    }

    .icon-medium {
        font-size: 18px;
    }

    .icon-large {
        font-size: 24px;
    }

    .icon-xlarge {
        font-size: 30px;
    }

    /* Animación flotante para iconos */
    @keyframes floatIcon {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-4px);
        }
    }

    /* Estilos específicos por tipo de icono */
    .icon-temperature {
        animation-delay: 0s;
    }

    .icon-humidity {
        animation-delay: 0.3s;
    }

    .icon-pressure {
        animation-delay: 0.6s;
    }

    .icon-radiation {
        animation-delay: 0.2s;
    }

    .icon-wind {
        animation-delay: 0.4s;
        animation: rotateWind 4s linear infinite;
    }


    /* Tooltip mejorado con fondo según variable */
    .leaflet-tooltip.tooltip-temperatura {
        background: linear-gradient(135deg, rgba(255, 107, 107, 0.95), rgba(255, 159, 64, 0.95)) !important;
        border-color: #ff6b6b !important;
    }

    .leaflet-tooltip.tooltip-humedad {
        background: linear-gradient(135deg, rgba(34, 211, 238, 0.95), rgba(74, 222, 128, 0.95)) !important;
        border-color: #22d3ee !important;
    }

    .leaflet-tooltip.tooltip-presion {
        background: linear-gradient(135deg, rgba(147, 51, 234, 0.95), rgba(236, 72, 153, 0.95)) !important;
        border-color: #9333ea !important;
    }

    .leaflet-tooltip.tooltip-radiacion {
        background: linear-gradient(135deg, rgba(251, 191, 36, 0.95), rgba(251, 146, 60, 0.95)) !important;
        border-color: #fbbf24 !important;
    }

    .leaflet-tooltip.tooltip-viento {
        background: linear-gradient(135deg, rgba(96, 165, 250, 0.95), rgba(147, 197, 253, 0.95)) !important;
        border-color: #60a5fa !important;
    }

    .custom-weather-marker {
        background: transparent !important;
        border: none !important;
    }
</style>

<!-- Fondo fijo del Chimborazo (IMAGEN MANTENIDA) -->
<div class="mapas-fixed-background"></div>

<div class="mapas-page-wrapper">
    <div class="mapas-container">

        <aside class="sidebar-mapas">
            <div class="sidebar-header">
                <i class="fas fa-filter sidebar-icon"></i>
                <h2 class="sidebar-title">Filtros Meteorológicos</h2>
            </div>

            <div class="filter-controls">

                <!-- ✅ FILTRO: ESTACIÓN METEOROLÓGICA -->
                <div class="control-section">
                    <div class="control-section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Estación Meteorológica
                    </div>
                    <div class="control-group">
                        <label class="control-label">Seleccionar Estación</label>
                        <select class="control-input" id="filterEstacion" onchange="onEstacionChange()">
                            <option value="">Cargando estaciones...</option>
                        </select>
                        <span class="filter-error-message" id="errorEstacion">
                            ⚠️ Debe seleccionar una estación
                        </span>
                    </div>
                </div>

                <!-- ✅ FILTRO: RANGO TEMPORAL -->
                <div class="control-section">
                    <div class="control-section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Rango Temporal
                    </div>

                    <!-- AÑO -->
                    <div class="control-group">
                        <label class="control-label">Año</label>
                        <select class="control-input" id="filterAnio" onchange="onAnioChange()" disabled>
                            <option value="">Primero seleccione estación</option>
                        </select>
                        <span class="filter-error-message" id="errorAnio">
                            ⚠️ Debe seleccionar un año
                        </span>
                    </div>

                    <!-- MES -->
                    <div class="control-group">
                        <label class="control-label">Mes</label>
                        <select class="control-input" id="filterMes" onchange="onMesChange()" disabled>
                            <option value="">Primero seleccione año</option>
                        </select>
                        <span class="filter-error-message" id="errorMes">
                            ⚠️ Debe seleccionar un mes
                        </span>
                    </div>
                </div>

                <!-- ✅ NUEVO FILTRO: TIPO DE DATO -->
                <div class="control-section">
                    <div class="control-section-title">
                        <i class="fas fa-chart-line"></i>
                        Tipo de Dato
                    </div>
                    <div class="control-group">
                        <label class="control-label">Estadística</label>
                        <select class="control-input" id="filterTipoDato" onchange="onTipoDatoChange()" disabled>
                            <option value="">Primero seleccione mes</option>
                        </select>
                        <span class="filter-error-message" id="errorTipoDato">
                            ⚠️ Debe seleccionar un tipo de dato
                        </span>
                    </div>
                </div>

                <!-- ✅ BOTÓN DE ACCIÓN -->
                <div class="control-section">
                    <button class="btn-filter btn-reset" onclick="resetFilters()" style="width: 100%; padding: 0.8rem;">
                        <i class="fas fa-undo"></i>
                        Restablecer
                    </button>
                </div>
            </div>
        </aside>

        <!-- Contenido Principal -->
        <main class="main-map-content">

            <!-- Grid de Mapas -->
            <div class="mapas-grid">

                <!-- Mapa de Temperatura -->
                <div class="map-container" id="mapTemperatura">
                    <div class="map-header">
                        <i class="fas fa-thermometer-half map-icon"></i>
                        <div>
                            <h3 class="map-title">Temperatura del Aire</h3>
                        </div>
                    </div>
                    <div class="map-viewer" id="leaflet-temperatura"></div>
                    <div class="map-legend">
                        <h4 class="legend-title">🌡️ Escala de Temperatura</h4>
                        <div class="legend-items">
                            <div class="legend-item">
                                <div class="legend-color hot"></div>
                                <span>Alta (&gt;25°C) 🔥</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color warm"></div>
                                <span>Moderada (18-25°C) 🌡️</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color cool"></div>
                                <span>Baja (&lt;18°C) ❄️</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mapa de Humedad -->
                <div class="map-container" id="mapHumedad">
                    <div class="map-header">
                        <i class="fas fa-tint map-icon"></i>
                        <div>
                            <h3 class="map-title">Humedad Relativa</h3>
                        </div>
                    </div>
                    <div class="map-viewer" id="leaflet-humedad"></div>
                    <div class="map-legend">
                        <h4 class="legend-title">💧 Niveles de Humedad</h4>
                        <div class="legend-items">
                            <div class="legend-item">
                                <div class="legend-color humid"></div>
                                <span>Alta (&gt;70%) 💧</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color cool"></div>
                                <span>Moderada (50-70%) 💦</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color dry"></div>
                                <span>Baja (&lt;50%) 🌵</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mapa de Presión Atmosférica -->
                <div class="map-container" id="mapPresion">
                    <div class="map-header">
                        <i class="fas fa-weight-hanging map-icon"></i>
                        <div>
                            <h3 class="map-title">Presión Atmosférica</h3>
                        </div>
                    </div>
                    <div class="map-viewer" id="leaflet-presion"></div>
                    <div class="map-legend">
                        <h4 class="legend-title">⚖️ Categorías de Presión</h4>
                        <div class="legend-items">
                            <div class="legend-item">
                                <div class="legend-color hot"></div>
                                <span>Alta (&gt;730 hPa) 🔴</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color cool"></div>
                                <span>Normal (720-730 hPa) 🟡</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color cold"></div>
                                <span>Baja (&lt;720 hPa) 🔵</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mapa de Radiación Solar -->
                <div class="map-container" id="mapRadiacion">
                    <div class="map-header">
                        <i class="fas fa-sun map-icon"></i>
                        <div>
                            <h3 class="map-title">Radiación Global</h3>
                        </div>
                    </div>
                    <div class="map-viewer" id="leaflet-radiacion"></div>
                    <div class="map-legend">
                        <h4 class="legend-title">☀️ Niveles de Radiación</h4>
                        <div class="legend-items">
                            <div class="legend-item">
                                <div class="legend-color hot"></div>
                                <span>Alta (&gt;400 W/m²) ☀️</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color warm"></div>
                                <span>Moderada (200-400 W/m²) 🌤️</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color cool"></div>
                                <span>Baja (&lt;200 W/m²) ☁️</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mapa de Velocidad del Viento -->
                <div class="map-container" id="mapViento">
                    <div class="map-header">
                        <i class="fas fa-wind map-icon"></i>
                        <div>
                            <h3 class="map-title">Velocidad del Viento</h3>
                            <p class="map-subtitle">Datos L0/L1 - Todos los Niveles</p>
                        </div>
                    </div>
                    <div class="map-viewer" id="leaflet-viento"></div>
                    <div class="map-legend">
                        <h4 class="legend-title">💨 Escala de Velocidad</h4>
                        <div class="legend-items">
                            <div class="legend-item">
                                <div class="legend-color hot"></div>
                                <span>Fuerte (&gt;5 m/s) 🌪️</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color warm"></div>
                                <span>Moderada (2-5 m/s) 💨</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color cool"></div>
                                <span>Ligera (&lt;2 m/s) 🍃</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

<script>
    // ===== VARIABLES GLOBALES LEAFLET =====
    let mapInstances = {
        temperatura: null,
        humedad: null,
        presion: null,
        radiacion: null,
        viento: null
    };

    let currentMarkers = {
        temperatura: null,
        humedad: null,
        presion: null,
        radiacion: null,
        viento: null
    };

    // Coordenadas del centro de Chimborazo
    const CHIMBORAZO_CENTER = [-1.885, -78.775];
    const CHIMBORAZO_ZOOM = 10;

    // ===== VARIABLES GLOBALES =====
    let selectedStation = '';
    let selectedYear = '';
    let selectedMonth = '';
    let selectedTipoDato = '';
    let cachedStationsData = null;
    let cachedYearsData = {};
    let cachedMonthsData = {};

    /**
 * 🗺️ INICIALIZAR MAPAS LEAFLET (SE EJECUTA UNA SOLA VEZ)
 */
    function inicializarMapasLeaflet() {
        console.log('🗺️ Inicializando mapas Leaflet...');

        const mapaIds = ['temperatura', 'humedad', 'presion', 'radiacion', 'viento'];

        mapaIds.forEach(tipo => {
            try {
                // Crear instancia de mapa
                const mapa = L.map(`leaflet-${tipo}`, {
                    center: CHIMBORAZO_CENTER,
                    zoom: CHIMBORAZO_ZOOM,
                    zoomControl: false,
                    dragging: false,
                    touchZoom: false,
                    scrollWheelZoom: false,
                    doubleClickZoom: false,
                    boxZoom: false,
                    keyboard: false,
                    attributionControl: false
                });

                // Agregar capa de tiles (mapa base)
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    minZoom: 9,
                    maxZoom: 11,
                    opacity: 0.7
                }).addTo(mapa);

                // Limitar vista a Chimborazo
                const bounds = L.latLngBounds(
                    [-2.45, -79.15], // Suroeste (límite sur Cumandá)
                    [-1.32, -78.40]  // Noreste (límite norte Chocavi)
                );
                mapa.setMaxBounds(bounds);
                mapa.fitBounds(bounds);

                // Guardar instancia
                mapInstances[tipo] = mapa;

                console.log(`✅ Mapa ${tipo} inicializado`);

            } catch (error) {
                console.error(`❌ Error al inicializar mapa ${tipo}:`, error);
            }
        });

        console.log('✅ Todos los mapas Leaflet inicializados');
    }
    /**
 * 📏 CALCULAR TAMAÑO DEL CÍRCULO SEGÚN VALOR Y VARIABLE
 */
    function calcularRadioCirculo(valor, variable) {
        if (valor === null || valor === undefined) return 40;

        const escalas = {
            'temperatura_aire': { min: -10, max: 40, radioMin: 50, radioMax: 120 },
            'humedad_relativa': { min: 0, max: 100, radioMin: 50, radioMax: 120 },
            'presion_atmosferica': { min: 700, max: 750, radioMin: 50, radioMax: 110 },
            'radiacion_global': { min: 0, max: 800, radioMin: 50, radioMax: 130 },
            'velocidad_viento': { min: 0, max: 15, radioMin: 50, radioMax: 120 }
        };

        const escala = escalas[variable];
        if (!escala) return 60;

        // Normalizar valor entre 0 y 1
        const porcentaje = Math.max(0, Math.min(1,
            (valor - escala.min) / (escala.max - escala.min)
        ));

        // Calcular radio proporcional
        return Math.round(escala.radioMin + (porcentaje * (escala.radioMax - escala.radioMin)));
    }
    /**
     * 🎲 GENERAR ICONOS DISPERSOS DENTRO DEL CÍRCULO
     */
    function generarIconosDispersos(variable, valor, color, radio) {
        if (valor === null || valor === undefined) {
            return '<span class="weather-icon icon-medium" style="color: #666;">❓</span>';
        }

        // Configuración de iconos por variable
        const configuraciones = {
            'temperatura_aire': {
                iconos: {
                    high: '🔥',      // >25°C
                    medium: '🌡️',    // 18-25°C
                    low: '❄️'        // <18°C
                },
                umbralHigh: 25,
                umbralMedium: 18
            },
            'humedad_relativa': {
                iconos: {
                    high: '💧',      // >70%
                    medium: '💦',    // 50-70%
                    low: '🌵'        // <50%
                },
                umbralHigh: 70,
                umbralMedium: 50
            },
            'presion_atmosferica': {
                iconos: {
                    high: '🔴',      // >730 hPa - Círculo rojo
                    medium: '🟡',    // 720-730 hPa - Círculo amarillo
                    low: '🔵'        // <720 hPa - Círculo azul
                },
                umbralHigh: 730,
                umbralMedium: 720
            },
            'radiacion_global': {
                iconos: {
                    high: '☀️',      // >400 W/m²
                    medium: '🌤️',    // 200-400 W/m²
                    low: '☁️'        // <200 W/m²
                },
                umbralHigh: 400,
                umbralMedium: 200
            },
            'velocidad_viento': {
                iconos: {
                    high: '🌪️',      // >5 m/s
                    medium: '💨',    // 2-5 m/s
                    low: '🍃'        // <2 m/s
                },
                umbralHigh: 5,
                umbralMedium: 2
            }
        };

        const config = configuraciones[variable];
        if (!config) return '<span class="weather-icon">❓</span>';

        // Seleccionar emoji según valor
        let emoji = config.iconos.low;
        if (valor >= config.umbralHigh) {
            emoji = config.iconos.high;
        } else if (valor >= config.umbralMedium) {
            emoji = config.iconos.medium;
        }

        // Calcular cantidad de iconos (proporcional al radio)
        const cantidadIconos = Math.max(5, Math.min(15, Math.round(radio / 8)));

        // Definir tamaños posibles
        const tamaños = ['icon-small', 'icon-small', 'icon-medium', 'icon-medium', 'icon-large', 'icon-xlarge'];

        // ✅ NUEVO: Array para almacenar posiciones y evitar solapamiento
        const posicionesUsadas = [];
        const distanciaMinima = 20; // Distancia mínima entre iconos en píxeles

        let htmlIconos = '';

        for (let i = 0; i < cantidadIconos; i++) {
            let x, y, intentos = 0;
            let posicionValida = false;

            // Intentar encontrar una posición que no se solape
            while (!posicionValida && intentos < 50) {
                // Generar posición aleatoria dentro del círculo
                const angulo = Math.random() * 2 * Math.PI;
                const distancia = Math.sqrt(Math.random()) * (radio / 2 - 20); // Margen interno

                x = Math.cos(angulo) * distancia;
                y = Math.sin(angulo) * distancia;

                // Verificar si está muy cerca de otros iconos
                posicionValida = true;
                for (const pos of posicionesUsadas) {
                    const dist = Math.sqrt(Math.pow(x - pos.x, 2) + Math.pow(y - pos.y, 2));
                    if (dist < distanciaMinima) {
                        posicionValida = false;
                        break;
                    }
                }

                intentos++;
            }

            // Si encontró posición válida, guardarla
            if (posicionValida) {
                posicionesUsadas.push({ x, y });

                const tamañoClase = tamaños[Math.floor(Math.random() * tamaños.length)];
                const delay = (Math.random() * 2).toFixed(2);

                htmlIconos += `
                <span class="weather-icon ${tamañoClase}" 
                      style="color: ${color}; 
                             position: absolute;
                             left: 50%;
                             top: 50%;
                             margin-left: ${x}px;
                             margin-top: ${y}px;
                             transform: translate(-50%, -50%);
                             animation-delay: ${delay}s;">
                    ${emoji}
                </span>
            `;
            }
        }

        return htmlIconos;
    }

    /**
 * 🎨 OBTENER ICONOS TEMÁTICOS SEGÚN VARIABLE Y VALOR
 */
    function getIconosPorVariable(variable, valor) {
        if (valor === null || valor === undefined) {
            return { iconos: ['❓'], cantidad: 1 };
        }

        const configuraciones = {
            'temperatura_aire': {
                iconos: {
                    high: { emoji: '🔥', min: 25 },      // >25°C
                    medium: { emoji: '🌡️', min: 18 },    // 18-25°C
                    low: { emoji: '❄️', min: 0 }         // <18°C
                },
                cantidadBase: 5
            },
            'humedad_relativa': {
                iconos: {
                    high: { emoji: '💧', min: 70 },      // >70%
                    medium: { emoji: '💦', min: 50 },    // 50-70%
                    low: { emoji: '🌵', min: 0 }         // <50%
                },
                cantidadBase: 6
            },
            'presion_atmosferica': {
                iconos: {
                    high: { emoji: '⬆️', min: 730 },     // >730 hPa
                    medium: { emoji: '➡️', min: 720 },   // 720-730 hPa
                    low: { emoji: '⬇️', min: 0 }         // <720 hPa
                },
                cantidadBase: 4
            },
            'radiacion_global': {
                iconos: {
                    high: { emoji: '☀️', min: 400 },     // >400 W/m²
                    medium: { emoji: '🌤️', min: 200 },   // 200-400 W/m²
                    low: { emoji: '☁️', min: 0 }         // <200 W/m²
                },
                cantidadBase: 7
            },
            'velocidad_viento': {
                iconos: {
                    high: { emoji: '🌪️', min: 5 },      // >5 m/s
                    medium: { emoji: '💨', min: 2 },     // 2-5 m/s
                    low: { emoji: '🍃', min: 0 }         // <2 m/s
                },
                cantidadBase: 5
            }
        };

        const config = configuraciones[variable];
        if (!config) return { iconos: ['❓'], cantidad: 1 };

        // Determinar qué emoji usar según el valor
        let emojiSeleccionado = config.iconos.low.emoji;

        if (valor >= config.iconos.high.min) {
            emojiSeleccionado = config.iconos.high.emoji;
        } else if (valor >= config.iconos.medium.min) {
            emojiSeleccionado = config.iconos.medium.emoji;
        }

        // Calcular cantidad de iconos según intensidad del valor
        const cantidadIconos = Math.min(
            Math.max(3, Math.round(config.cantidadBase * (valor / 100))),
            9
        );

        return {
            iconos: Array(cantidadIconos).fill(emojiSeleccionado),
            cantidad: cantidadIconos
        };
    }
    function dibujarLimitesChimborazo() {
        const limites = [
            [-2.45, -79.15], // SO
            [-2.45, -78.40], // SE
            [-1.32, -78.40], // NE
            [-1.32, -79.15], // NO
            [-2.45, -79.15]  // Cierre
        ];

        Object.values(mapInstances).forEach(mapa => {
            if (mapa) {
                L.polyline(limites, {
                    color: '#00ffff',
                    weight: 2,
                    opacity: 0.5,
                    dashArray: '5, 10'
                }).addTo(mapa);
            }
        });

        console.log('📐 Límites de Chimborazo dibujados');
    }
    /**
     * 🎨 OBTENER COLOR SEGÚN VALOR Y VARIABLE
     */
    function getColorByValue(valor, variable) {
        if (valor === null || valor === undefined) return '#666666';

        const escalas = {
            'temperatura_aire': [
                { max: 10, color: '#3b82f6' },      // Azul frío
                { max: 18, color: '#22d3ee' },      // Cyan fresco
                { max: 25, color: '#fbbf24' },      // Amarillo cálido (MODERADO)
                { max: Infinity, color: '#ef4444' } // Rojo caliente (ALTA)
            ],
            'humedad_relativa': [
                { max: 50, color: '#9d4edd' },      // Morado seco (BAJA)
                { max: 70, color: '#fbbf24' },      // Amarillo normal (MODERADA)
                { max: Infinity, color: '#22d3ee' } // Cyan húmedo (ALTA)
            ],
            'presion_atmosferica': [
                { max: 720, color: '#3b82f6' },     // Azul baja
                { max: 730, color: '#fbbf24' },     // Amarillo normal
                { max: Infinity, color: '#ef4444' } // Rojo alta
            ],
            'radiacion_global': [
                { max: 200, color: '#60a5fa' },     // Azul baja
                { max: 400, color: '#fbbf24' },     // Amarillo media
                { max: Infinity, color: '#ef4444' } // Rojo alta
            ],
            'velocidad_viento': [
                { max: 2, color: '#60a5fa' },       // Azul ligera
                { max: 5, color: '#fbbf24' },       // Amarillo moderado
                { max: Infinity, color: '#ef4444' } // Rojo fuerte
            ]
        };

        const escala = escalas[variable];
        if (!escala) return '#cccccc';

        for (let rango of escala) {
            if (valor < rango.max) {
                return rango.color;
            }
        }

        return '#cccccc';
    }

    /**
     * 📏 CALCULAR RADIO DEL CÍRCULO SEGÚN VALOR
     */
    function getRadiusByValue(valor, variable) {
        if (valor === null || valor === undefined) return 15;

        const escalas = {
            'temperatura_aire': { min: -10, max: 30, radioMin: 12, radioMax: 28 },
            'humedad_relativa': { min: 0, max: 100, radioMin: 12, radioMax: 28 },
            'presion_atmosferica': { min: 700, max: 750, radioMin: 12, radioMax: 28 },
            'radiacion_global': { min: 0, max: 1500, radioMin: 12, radioMax: 28 },
            'velocidad_viento': { min: 0, max: 10, radioMin: 12, radioMax: 28 }
        };

        const escala = escalas[variable];
        if (!escala) return 15;

        const porcentaje = Math.max(0, Math.min(1,
            (valor - escala.min) / (escala.max - escala.min)
        ));

        return escala.radioMin + (porcentaje * (escala.radioMax - escala.radioMin));
    }

    /**
     * 📊 OBTENER UNIDAD DE MEDIDA
     */
    function getUnidadMedida(variable) {
        const unidades = {
            'temperatura_aire': '°C',
            'humedad_relativa': '%',
            'presion_atmosferica': 'hPa',
            'radiacion_global': 'W/m²',
            'velocidad_viento': 'm/s'
        };
        return unidades[variable] || '';
    }
    /**
     * 🎯 PINTAR CÍRCULO EN MAPA LEAFLET
     */
    function pintarCirculoEnMapa(tipoMapa, estacion, valor, variable) {
        const mapa = mapInstances[tipoMapa];
        if (!mapa) {
            console.error(`❌ Mapa ${tipoMapa} no existe`);
            return;
        }

        // ✅ MODIFICADO: Ya NO limpiar marcador previo si estamos en modo TODAS
        // Solo limpiar si es estación individual
        if (selectedStation !== 'TODAS' && currentMarkers[tipoMapa]) {
            mapa.removeLayer(currentMarkers[tipoMapa]);
        }

        // Si no hay valor, no pintar
        if (valor === null || valor === undefined) {
            console.warn(`⚠️ Sin valor para ${variable} en ${tipoMapa}`);
            return;
        }

        const color = getColorByValue(valor, variable);
        const unidad = getUnidadMedida(variable);
        const radio = calcularRadioCirculo(valor, variable);

        // Generar HTML con iconos dispersos
        const iconosHTML = generarIconosDispersos(variable, valor, color, radio);

        const containerHTML = `
        <div class="circle-container" style="width: ${radio}px; height: ${radio}px;">
            ${iconosHTML}
        </div>
    `;

        // Crear icono personalizado de Leaflet
        const customIcon = L.divIcon({
            html: containerHTML,
            className: 'custom-weather-marker',
            iconSize: [radio, radio],
            iconAnchor: [radio / 2, radio / 2]
        });

        // Crear marcador
        const marcador = L.marker(
            [estacion.latitud, estacion.longitud],
            { icon: customIcon }
        );

        // Tooltip interactivo
        marcador.bindTooltip(
            `<div class="tooltip-content">
            <strong style="font-size: 1rem; display: block; margin-bottom: 0.3rem;">
                ${estacion.nombre}
            </strong>
            <div style="font-size: 0.85rem; color: rgba(255,255,255,0.8); margin-bottom: 0.4rem;">
                ${variable.replace(/_/g, ' ').toUpperCase()}
            </div>
            <div style="font-size: 1.3rem; font-weight: 700; color: ${color};">
                ${valor.toFixed(2)} ${unidad}
            </div>
        </div>`,
            {
                permanent: false,
                direction: 'top',
                offset: [0, -(radio / 2 + 10)],
                className: `tooltip-${tipoMapa}`
            }
        );

        // Agregar al mapa
        marcador.addTo(mapa);

        // ✅ MODIFICADO: Guardar referencia como array si es modo TODAS
        if (selectedStation === 'TODAS') {
            if (!Array.isArray(currentMarkers[tipoMapa])) {
                currentMarkers[tipoMapa] = [];
            }
            currentMarkers[tipoMapa].push(marcador);
        } else {
            currentMarkers[tipoMapa] = marcador;
        }

        console.log(`✅ Círculo pintado en ${tipoMapa}: ${estacion.nombre} - ${valor} ${unidad}`);
    }

    // ===== CARGA INICIAL: OBTENER ESTACIONES =====
    async function loadInitialStations() {
        try {
            console.log('🔄 Cargando estaciones desde la base de datos...');

            const response = await fetch('../controller/CMapas.php?action=get_estaciones');
            const result = await response.json();

            if (!result.success) {
                throw new Error(result.error || 'Error al cargar estaciones');
            }

            cachedStationsData = result.data;
            populateStationSelect(result.data);

            console.log(`✅ ${result.total} estaciones cargadas`);

        } catch (error) {
            console.error('❌ Error al cargar estaciones:', error);

            const select = document.getElementById('filterEstacion');
            select.innerHTML = '<option value="">Error al cargar estaciones</option>';
            select.disabled = true;
        }
    }

    // ===== POBLAR SELECT DE ESTACIONES =====
    function populateStationSelect(estaciones) {
        const select = document.getElementById('filterEstacion');

        // ✅ MODIFICADO: Agregar opción "TODAS LAS ESTACIONES"
        select.innerHTML = `
        <option value="">Seleccione una estación...</option>
        <option value="TODAS" style="font-weight: 700; color: var(--accent-green);">🌍 TODAS LAS ESTACIONES</option>
    `;

        if (!estaciones || estaciones.length === 0) {
            select.innerHTML = '<option value="">No hay estaciones disponibles</option>';
            select.disabled = true;
            return;
        }

        // Agregar estaciones individuales
        estaciones.forEach(estacion => {
            const option = document.createElement('option');
            option.value = estacion.id;
            option.textContent = `${estacion.nombre} (${estacion.codigo})`;
            select.appendChild(option);
        });

        select.disabled = false;
    }

    // ===== EVENTO: CAMBIO DE ESTACIÓN =====
    async function onEstacionChange() {
        const estacionId = document.getElementById('filterEstacion').value;

        clearFieldError('filterEstacion');
        resetYearMonthTipoSelects();
        limpiarTodosLosMapas();

        selectedStation = estacionId;
        selectedYear = '';
        selectedMonth = '';
        selectedTipoDato = '';

        if (!estacionId) {
            console.log('⚠️ Estación deseleccionada');
            return;
        }

        // ✅ NUEVO: Detectar si seleccionó "TODAS"
        if (estacionId === 'TODAS') {
            console.log('🌍 Modo TODAS LAS ESTACIONES activado');
            await loadGlobalYearRange();
            return;
        }

        // Flujo normal para estación individual
        try {
            const years = await loadYearsForStation(estacionId);

            if (years.length === 0) {
                return;
            }

            const selectYear = document.getElementById('filterAnio');
            selectYear.disabled = false;
            selectYear.innerHTML = '<option value="">Seleccione un año...</option>';

            years.forEach(year => {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                selectYear.appendChild(option);
            });

            console.log(`✅ Estación ${estacionId} seleccionada: ${years.length} años`);

        } catch (error) {
            console.error('❌ Error al cargar años:', error);
        }
    }

    // ===== CARGAR RANGO GLOBAL DE AÑOS =====
    async function loadGlobalYearRange() {
        try {
            console.log('📅 Cargando rango global de años...');

            const response = await fetch('../controller/CMapas.php?action=get_rango_anios_global');
            const result = await response.json();

            if (!result.success) {
                throw new Error(result.error);
            }

            const { anio_minimo, anio_maximo } = result.data;

            if (!anio_minimo || !anio_maximo) {
                throw new Error('No hay datos disponibles en el sistema');
            }

            // Poblar select de años con rango completo
            const selectYear = document.getElementById('filterAnio');
            selectYear.disabled = false;
            selectYear.innerHTML = '<option value="">Seleccione un año...</option>';

            for (let year = anio_maximo; year >= anio_minimo; year--) {
                const option = document.createElement('option');
                option.value = year;
                option.textContent = year;
                selectYear.appendChild(option);
            }

            console.log(`✅ Rango global cargado: ${anio_minimo} - ${anio_maximo}`);

        } catch (error) {
            console.error('❌ Error al cargar rango global:', error);

            const selectYear = document.getElementById('filterAnio');
            selectYear.innerHTML = '<option value="">Error al cargar años</option>';
            selectYear.disabled = true;
        }
    }

    // ===== CARGAR AÑOS DESDE BD =====
    async function loadYearsForStation(estacionId) {
        if (cachedYearsData[estacionId]) {
            console.log(`✅ Usando años desde caché para estación ${estacionId}`);
            return cachedYearsData[estacionId];
        }

        try {
            const response = await fetch(`../controller/CMapas.php?action=get_anios&id_estacion=${estacionId}`);
            const result = await response.json();

            if (!result.success) {
                throw new Error(result.error);
            }

            cachedYearsData[estacionId] = result.data;
            return result.data;

        } catch (error) {
            console.error('❌ Error en loadYearsForStation:', error);
            throw error;
        }
    }

    // ===== EVENTO: CAMBIO DE AÑO =====
    async function onAnioChange() {
        const year = document.getElementById('filterAnio').value;

        clearFieldError('filterAnio');
        resetMonthTipoSelects();
        limpiarTodosLosMapas();

        selectedYear = year;
        selectedMonth = '';
        selectedTipoDato = '';

        if (!year) {
            console.log('⚠️ Año deseleccionado');
            return;
        }

        // ✅ NUEVO: Detectar si está en modo "TODAS"
        if (selectedStation === 'TODAS') {
            await loadGlobalMonths(year);
            return;
        }

        // Flujo normal para estación individual
        try {
            const months = await loadMonthsForStationYear(selectedStation, year);

            if (months.length === 0) {
                return;
            }

            populateMonthSelect(months);

            console.log(`✅ Año ${year} seleccionado: ${months.length} opciones de mes`);

        } catch (error) {
            console.error('❌ Error al cargar meses:', error);
        }
    }
    // ===== CARGAR MESES GLOBALES =====
    async function loadGlobalMonths(year) {
        try {
            console.log(`📆 Cargando meses globales para año ${year}...`);

            const response = await fetch(`../controller/CMapas.php?action=get_meses_global&anio=${year}`);
            const result = await response.json();

            if (!result.success) {
                throw new Error(result.error);
            }

            populateMonthSelect(result.data);

            console.log(`✅ Meses globales cargados: ${result.total} meses`);

        } catch (error) {
            console.error('❌ Error al cargar meses globales:', error);

            const selectMonth = document.getElementById('filterMes');
            selectMonth.innerHTML = '<option value="">Error al cargar meses</option>';
            selectMonth.disabled = true;
        }
    }
    // ===== FUNCIÓN AUXILIAR: POBLAR SELECT DE MESES =====
    function populateMonthSelect(months) {
        const selectMonth = document.getElementById('filterMes');
        selectMonth.disabled = false;
        selectMonth.innerHTML = '<option value="">Seleccione un mes...</option>';

        const tieneMes13 = months.some(m => m.numero === 13);

        if (tieneMes13) {
            const optionTodos = document.createElement('option');
            optionTodos.value = '13';
            optionTodos.textContent = '📊 Todos (Promedio Anual)';
            optionTodos.style.fontWeight = '700';
            optionTodos.style.color = 'var(--accent-green)';
            selectMonth.appendChild(optionTodos);
        }

        months
            .filter(m => m.numero >= 1 && m.numero <= 12)
            .forEach(month => {
                const option = document.createElement('option');
                option.value = month.numero;
                option.textContent = `${month.numero.toString().padStart(2, '0')} - ${month.nombre}`;
                selectMonth.appendChild(option);
            });
    }



    // ===== CARGAR MESES DESDE BD =====
    async function loadMonthsForStationYear(estacionId, year) {
        const cacheKey = `${estacionId}_${year}`;

        if (cachedMonthsData[cacheKey]) {
            console.log(`✅ Usando meses desde caché para ${cacheKey}`);
            return cachedMonthsData[cacheKey];
        }

        try {
            const response = await fetch(`../controller/CMapas.php?action=get_meses&id_estacion=${estacionId}&anio=${year}`);
            const result = await response.json();

            if (!result.success) {
                throw new Error(result.error);
            }

            cachedMonthsData[cacheKey] = result.data;
            return result.data;

        } catch (error) {
            console.error('❌ Error en loadMonthsForStationYear:', error);
            throw error;
        }
    }

    // ===== EVENTO: CAMBIO DE MES =====
    function onMesChange() {
        const month = document.getElementById('filterMes').value;

        clearFieldError('filterMes');
        resetTipoDatoSelect();

        // ✅ LIMPIAR MAPAS AL CAMBIAR MES
        limpiarTodosLosMapas();

        selectedMonth = month;
        selectedTipoDato = '';

        if (!month) {
            console.log('⚠️ Mes deseleccionado');
            return;
        }

        const selectTipoDato = document.getElementById('filterTipoDato');
        selectTipoDato.disabled = false;
        selectTipoDato.innerHTML = '<option value="">Seleccione tipo de dato...</option>';

        const opciones = [
            { value: 'MAX', label: '📈 Máximo', icon: '🔴' },
            { value: 'AVG', label: '📊 Promedio', icon: '🟡' },
            { value: 'MIN', label: '📉 Mínimo', icon: '🔵' }
        ];

        opciones.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = `${opt.icon} ${opt.label}`;
            selectTipoDato.appendChild(option);
        });

        if (month === '13') {
            console.log(`✅ Seleccionado: TODOS (mes 13) - Esperando tipo de dato`);
        } else {
            const nombres_meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            console.log(`✅ Seleccionado: ${nombres_meses[parseInt(month)]} (${month}) - Esperando tipo de dato`);
        }
    }

    // ===== NUEVO: EVENTO CAMBIO DE TIPO DE DATO =====
    function onTipoDatoChange() {
        const tipoDato = document.getElementById('filterTipoDato').value;

        clearFieldError('filterTipoDato');
        selectedTipoDato = tipoDato;

        if (!tipoDato) {
            console.log('⚠️ Tipo de dato deseleccionado');
            return;
        }

        // Descripción del tipo seleccionado
        const descripciones = {
            'MAX': 'Valores máximos registrados',
            'AVG': 'Promedios del período',
            'MIN': 'Valores mínimos registrados'
        };

        console.log(`✅ Tipo seleccionado: ${tipoDato} (${descripciones[tipoDato]})`);

        // ✅ CARGAR MAPAS AUTOMÁTICAMENTE
        actualizarMapas();
    }

    // ===== FUNCIÓN: ACTUALIZAR MAPAS =====
    function actualizarMapas() {
        const estacion = selectedStation;
        const anio = selectedYear;
        const mes = selectedMonth;
        const tipoDato = selectedTipoDato;

        clearAllFieldErrors();

        // Validación completa
        let hayErrores = false;

        if (!estacion) {
            showFieldError('filterEstacion');
            hayErrores = true;
        }

        if (!anio) {
            showFieldError('filterAnio');
            hayErrores = true;
        }

        if (!mes) {
            showFieldError('filterMes');
            hayErrores = true;
        }

        if (!tipoDato) {
            showFieldError('filterTipoDato');
            hayErrores = true;
        }

        if (hayErrores) {
            setTimeout(clearAllFieldErrors, 3000);
            return;
        }

        // ✅ MODIFICADO: Preparar datos según modo
        const filterData = {
            id_estacion: estacion,  // Puede ser ID numérico o "TODAS"
            anio: anio,
            mes: mes,
            tipo_dato: tipoDato,
            es_promedio_anual: (mes === '13')
        };

        console.log('🗺️ Actualizando mapas con filtros:', filterData);

        loadMapDataFromDatabase(filterData);
    }

    // ===== CARGAR DATOS DE MAPAS =====
    async function loadMapDataFromDatabase(filterData) {
        console.log('📊 Cargando datos de mapas...');

        try {
            // ✅ NUEVO: Detectar si es modo "TODAS"
            if (filterData.id_estacion === 'TODAS') {
                // Llamar a endpoint diferente para todas las estaciones
                const response = await fetch('../controller/CMapas.php?action=get_datos_todas_estaciones', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        anio: filterData.anio,
                        mes: filterData.mes,
                        tipo_dato: filterData.tipo_dato
                    })
                });

                const result = await response.json();

                if (!result.success) {
                    throw new Error(result.error);
                }

                console.log(`✅ Datos recibidos: ${result.total_estaciones} estaciones`);
                updateAllMapsMultipleStations(result.data);
                return;
            }

            // Flujo normal para estación individual
            const response = await fetch('../controller/CMapas.php?action=get_datos_mapas', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(filterData)
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.error);
            }

            console.log('✅ Datos recibidos:', result.data);
            updateAllMaps(result.data);

        } catch (error) {
            console.error('❌ Error:', error);
        }
    }
    // ===== ACTUALIZAR TODOS LOS MAPAS CON MÚLTIPLES ESTACIONES =====
    function updateAllMapsMultipleStations(estaciones) {
        console.log(`🗺️ Actualizando mapas con ${estaciones.length} estaciones`);

        if (!estaciones || estaciones.length === 0) {
            console.error('❌ No hay datos de estaciones');
            mostrarMensajeError('No se encontraron datos para este período');
            return;
        }

        // Limpiar marcadores previos
        limpiarTodosLosMapas();

        // Configuración de mapas
        const mapasConfig = [
            { tipo: 'temperatura', variable: 'temperatura_aire' },
            { tipo: 'humedad', variable: 'humedad_relativa' },
            { tipo: 'presion', variable: 'presion_atmosferica' },
            { tipo: 'radiacion', variable: 'radiacion_global' },
            { tipo: 'viento', variable: 'velocidad_viento' }
        ];

        // Iterar sobre cada estación
        estaciones.forEach(estacionData => {
            const { estacion, valores } = estacionData;

            // Validar coordenadas
            if (!estacion.latitud || !estacion.longitud) {
                console.warn(`⚠️ Estación ${estacion.nombre} sin coordenadas, omitida`);
                return;
            }

            // Pintar círculo en cada mapa
            mapasConfig.forEach(config => {
                try {
                    const valor = valores[config.variable];

                    // Solo pintar si hay valor
                    if (valor !== null && valor !== undefined) {
                        pintarCirculoEnMapa(config.tipo, estacion, valor, config.variable);
                    }
                } catch (error) {
                    console.error(`❌ Error al pintar ${config.tipo} para ${estacion.nombre}:`, error);
                }
            });
        });

        console.log(`✅ Mapas actualizados con ${estaciones.length} estaciones`);
    }

    // ===== ACTUALIZAR TODOS LOS MAPAS (POR IMPLEMENTAR) =====
    function updateAllMaps(data) {
        console.log('🗺️ Actualizando mapas con datos:', data);

        if (!data || !data.estacion || !data.valores) {
            console.error('❌ Estructura de datos inválida');
            mostrarMensajeError('Error en estructura de datos');
            return;
        }

        const { estacion, valores } = data;

        // Validar coordenadas
        if (!estacion.latitud || !estacion.longitud) {
            console.error('❌ Estación sin coordenadas');
            mostrarMensajeError('Estación sin coordenadas configuradas');
            return;
        }

        console.log(`📍 ${estacion.nombre} [${estacion.latitud}, ${estacion.longitud}]`);

        // Configuración de mapas
        const mapasConfig = [
            { tipo: 'temperatura', variable: 'temperatura_aire', valor: valores.temperatura_aire },
            { tipo: 'humedad', variable: 'humedad_relativa', valor: valores.humedad_relativa },
            { tipo: 'presion', variable: 'presion_atmosferica', valor: valores.presion_atmosferica },
            { tipo: 'radiacion', variable: 'radiacion_global', valor: valores.radiacion_global },
            { tipo: 'viento', variable: 'velocidad_viento', valor: valores.velocidad_viento }
        ];

        // Pintar cada mapa
        mapasConfig.forEach(config => {
            try {
                pintarCirculoEnMapa(config.tipo, estacion, config.valor, config.variable);
            } catch (error) {
                console.error(`❌ Error al pintar ${config.tipo}:`, error);
            }
        });

        console.log('✅ Todos los mapas actualizados');
    }
    function mostrarMensajeError(mensaje) {
        console.error('🚨', mensaje);
        // TODO: Implementar notificación visual si quieres
    }

    // ===== FUNCIÓN: RESETEAR FILTROS =====
    function resetFilters() {
        document.getElementById('filterEstacion').selectedIndex = 0;
        resetYearMonthTipoSelects();

        // ✅ LIMPIAR MAPAS AL RESETEAR
        limpiarTodosLosMapas();

        selectedStation = '';
        selectedYear = '';
        selectedMonth = '';
        selectedTipoDato = '';

        clearAllFieldErrors();

        console.log('🔄 Filtros restablecidos y mapas limpiados');
    }

    /**
     * 🧹 LIMPIAR TODOS LOS MARCADORES DE LOS MAPAS
     */
    function limpiarTodosLosMapas() {
        console.log('🧹 Limpiando todos los marcadores de los mapas...');

        const tiposMapas = ['temperatura', 'humedad', 'presion', 'radiacion', 'viento'];

        tiposMapas.forEach(tipo => {
            const mapa = mapInstances[tipo];
            const marcadores = currentMarkers[tipo];

            if (mapa && marcadores) {
                try {
                    // ✅ NUEVO: Detectar si es array (modo TODAS) o marcador único
                    if (Array.isArray(marcadores)) {
                        // Modo TODAS: limpiar array de marcadores
                        marcadores.forEach(marcador => {
                            mapa.removeLayer(marcador);
                        });
                        console.log(`✅ ${marcadores.length} marcadores de ${tipo} eliminados`);
                    } else {
                        // Modo estación individual: limpiar marcador único
                        mapa.removeLayer(marcadores);
                        console.log(`✅ Marcador de ${tipo} eliminado`);
                    }

                    // Resetear a null
                    currentMarkers[tipo] = null;

                } catch (error) {
                    console.warn(`⚠️ Error al limpiar marcador de ${tipo}:`, error);
                }
            }
        });

        console.log('✅ Todos los mapas limpiados');
    }

    // ===== FUNCIONES DE LIMPIEZA =====
    function resetYearMonthTipoSelects() {
        const selectYear = document.getElementById('filterAnio');
        selectYear.innerHTML = '<option value="">Primero seleccione estación</option>';
        selectYear.disabled = true;

        resetMonthTipoSelects();
    }

    function resetMonthTipoSelects() {
        const selectMonth = document.getElementById('filterMes');
        selectMonth.innerHTML = '<option value="">Primero seleccione año</option>';
        selectMonth.disabled = true;

        resetTipoDatoSelect();
    }

    function resetTipoDatoSelect() {
        const selectTipoDato = document.getElementById('filterTipoDato');
        selectTipoDato.innerHTML = '<option value="">Primero seleccione mes</option>';
        selectTipoDato.disabled = true;
    }

    // ===== FUNCIONES DE MANEJO DE ERRORES =====
    function showFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        const errorMsg = document.getElementById(`error${fieldId.replace('filter', '')}`);

        if (field) field.classList.add('error-required');
        if (errorMsg) errorMsg.classList.add('visible');
    }

    function clearFieldError(fieldId) {
        const field = document.getElementById(fieldId);
        const errorMsg = document.getElementById(`error${fieldId.replace('filter', '')}`);

        if (field) field.classList.remove('error-required');
        if (errorMsg) errorMsg.classList.remove('visible');
    }

    function clearAllFieldErrors() {
        document.querySelectorAll('.filter-error-message').forEach(el => el.classList.remove('visible'));
        document.querySelectorAll('.control-input').forEach(el => el.classList.remove('error-required'));
    }


    // ===== INICIALIZACIÓN =====
    document.addEventListener('DOMContentLoaded', async function () {
        console.log('🚀 Inicializando Sistema de Mapas Meteorológicos...');

        try {
            // 1️⃣ Inicializar mapas Leaflet PRIMERO
            inicializarMapasLeaflet();

            // 3️⃣ Cargar estaciones de la BD
            await loadInitialStations();

            console.log('✅ Sistema completamente inicializado');

        } catch (error) {
            console.error('❌ Error en inicialización:', error);
        }
    });

</script>

<?php include('includes/footer.php'); ?>